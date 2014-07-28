<?php
class Query {
    protected $_operation;
    protected $_table;
    protected $_fields = array();
    protected $_fieldMap = array();
    protected $_values = array();
    protected $_wheres = array();
    protected $_orders = array();
    protected $_limit = null;
    protected $_offset = null;
    protected $_joins = array();
    protected $_built = '';

    private static function Create ($operation, $table = null){
        $q = new static;
        $q->operation($operation);
        if (!is_null($table)){
            $q->table($table);
        }
        return $q;
    }


    /**
     * Create an Insert Query
     *
     * @param string $table Table to insert to
     * @return Query
     */
    public static function Insert ($table=null){
        return static::Create('INSERT',$table);
    }

    /**
     * Create a Select Query
     *
     * @param string $table Table to select from
     * @return Query
     */
    public static function Select ($table=null){
        return static::Create('SELECT',$table);
    }

    /**
     * Create an Update Query
     *
     * @param string $table Table to update
     * @return Query
     */
    public static function Update ($table=null){
        return static::Create('UPDATE',$table);
    }

    /**
     * Create Delete Query
     *
     * @param string $table Table to delete from
     * @return Query
     */
    public static function Delete ($table=null){
        return static::Create('DELETE',$table);
    }

    /**
     * Sets Table for Query
     *
     * @param string $table
     * @return Query
     */
    public function table ($table=null){
        if (is_null($table)){
            return $this->_table;
        } else {
            $this->_table = $table;
            return $this;
        }
    }


    /**
     * Sets Operation for Query
     *
     * @param string $operation - INSERT, SELECT, UPDATE or DELETE
     * @return Query
     */
    public function operation ($operation=null){
        if (is_null($operation)){
            return $this->_operation;
        } else {
            $this->_operation = strtoupper($operation);
            return $this;
        }
    }

//--------------------Helper methods-------------------------//

    private static function tick($string){
        //we escape field and table names (the ones which get 'ticked') because parts of those names might come as user input
        //In any case, proper table and field names do not contain escapable characters, so this is just to be safe.
        $result = '`'.DB::escape($string).'`';
        return $result;
    }

    private function invalidate(){
        $this->_built='';
    }

    public function __toString(){
        return $this->build();
    }

//----------------Field=>Value methods-----------------------//

    private function add_field ($field){
        $this->invalidate();
        $this->_fields[]=$field;
    }

    private function add_value ($value){
        $this->invalidate();
        $this->_values[]=$value;
    }

    /**
     * Add one or more Fields
     *
     * @param array $fields - Array with field names as strings
     * @param string $fields,... - String(s) with field name(s)
     * @param NULL $fields - Retuns list of fields
     * @return Query
     */
    public function fields ($fields=null){
        if (is_null($fields)){
            return $this->_fields;
        } else {
            if (is_array($fields)){
                foreach($fields as $field){
                    $this->add_field($field);
                }
            } else {
                if (func_num_args() == 1){
                    if (is_string($fields)){
                        $this->add_field($fields);
                    }
                } else {
                    foreach (func_get_args() as $field){
                        $this->add_field($field);
                    }
                }
            }
            return $this;
        }
    }


    /**
     * Adds a field Map to use in "field as alias" clause, Only use when building Select Queries
     *
     * @param array $fieldMap - Array with fieldname=>fieldAlias mapping
     * @return Query
     */
    public function fieldMap($fieldMap){
        $this->_fieldMap = $fieldMap;
        return $this;
    }

    /**
     * Add one or more Values
     *
     * @param array $values - Array with field values
     * @param mixed $values,... - field value(s)
     * @param NULL $values - Retuns list of values
     * @return Query
     */
    public function values ($values=null){
        if (is_null($values)){
            return $this->_values;
        } else {
            if (is_array($values)){
                foreach($values as $value){
                    $this->add_value($value);
                }
            } else {
                if (func_num_args() == 1){
                    if (is_string($values)){
                        $this->add_value($values);
                    }
                } else {
                    foreach (func_get_args() as $value){
                        $this->add_value($value);
                    }
                }
            }
            return $this;
        }
    }

    /**
     * Add one or more Field=>Value Pairs
     *
     * @param array $pairs - Array with field=>value pairs
     * @param array $values - field value(s)
     * @param mixed $values,... - take all argument pairs as $arg0=>$arg1, $arg2=>$arg3....
     * @return Query
     */
    public function pairs ($pairs=null,$values=null){
        if (is_null($values)){ // single field=>value array
            foreach ($pairs as $field=>$value){
                $this->add_field($field);
                $this->add_value($value);
            }
        } else if (func_num_args() == 2){
            if (is_array($pairs) && is_array($values)){ // two arrays
                $min = min(array(count($pairs),count($values)));
                for ($i=0; $i<$min; $i++){
                    $this->add_field($pairs[$i]);
                    $this->add_value($values[$i]);
                }
            } else { //a string and a value
                $this->add_field($pairs);
                $this->add_value($values);
            }
        } else if (func_num_args() > 2){ //take all argument pairs as $arg0=>$arg1, $arg2=>$arg3....
            $num = floor(func_num_args()/2);
            for ($i=0; $i<$num; $i++){
                $this->add_field(func_get_arg($i*2));
                $this->add_value(func_get_arg(($i*2)+1));
            }
        }
        return $this;
    }

//----------------WHERE methods-----------------------//

    /**
     * Add an array to the $this->_where
     *
     * @param string $op - Operation:  =   <   <=   >   >=   <>   IS NULL   IS NOT NULL   IN    BETWEEN    LIKE    AND   OR   (   )
     * @param string $field - Field to compare in
     * @param mixed $value,... - Value(s) to compare
     */
    private function add_where ($op,$field=null,$value=null){
        $this->invalidate();
        $num = func_num_args();
        if ($num == 1){
            $this->_wheres[] = array($op);
        } else if ($num == 2){
            $this->_wheres[] = array($op,$field);
        } else if ($num == 3){
            $this->_wheres[] = array($op,$field,$value);
        } else if ($num > 3){
            $newWhere = array();
            for ($i = 0; $i<$num; $i++){
                $newWhere[] = func_get_args($i);
            }
            $this->_wheres[] = $newWhere;
        }
    }

    /**
     * Adds an arbitrary Where Clause -- Not escaped
     *
     * @param mixed $where - Arbitrary where clause - not escaped
     *
     * @return Query
     */
    public function where ($where){
        $this->addWhere ('WHERE',$where);
        return $this;
    }

    /**
     * Adds WHERE ID=$value
     *
     * @param int $value - ID of record
     */
    public function id ($value){
        $this->add_where('=','ID',$value);
        return $this;
    }

    /**
     * Equals - Adds WHERE $field=$value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function eq ($field,$value){
        $this->add_where('=',$field,$value);
        return $this;
    }

    /**
     * Inequal - Adds WHERE $field <> $value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function ieq ($field,$value){
        $this->add_where('<>',$field,$value);
        return $this;
    }

    /**
     * Less than - Adds WHERE $field < $value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function lt ($field,$value){
        $this->add_where('<',$field,$value);
        return $this;
    }

    /**
     * Less than or welcome - Adds WHERE $field <= $value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function lte ($field,$value){
        $this->add_where('<=',$field,$value);
        return $this;
    }

    /**
     * Greater than - Adds WHERE $field > $value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function gt ($field,$value){
        $this->add_where('>',$field,$value);
        return $this;
    }

    /**
     * Greater than or equal - Adds WHERE $field >= $value
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Query
     */
    public function gte ($field,$value){
        $this->add_where('>=',$field,$value);
        return $this;
    }

    /**
     * Is null - Adds WHERE $field IS NULL
     *
     * @param string $field
     *
     * @return Query
     */
    public function isnull ($field){
        $this->add_where('IS NULL',$field);
        return $this;
    }

    /**
     * Is not null - Adds WHERE $field IS NULL
     *
     * @param string $field
     *
     * @return Query
     */
    public function notnull ($field){
        $this->add_where('IS NOT NULL',$field);
        return $this;
    }

    /**
     * In - Adds WHERE $field IN $value(s)
     *
     * @param string $field
     * @param mixed $values,...
     *
     * @return Query
     */
    public function in ($field){
        $ins = array();
        $num = func_num_args();
        if ($num == 2 && is_array(func_get_arg(1))){
            foreach(func_get_arg(1) as $arg){
                $ins[] = $arg;
            }
        } else {
            for ($i=1; $i<$num; $i++){
                $ins[] = func_get_arg($i);
            }
        }
        $this->add_where('IN',$field,$ins);
        return $this;
    }

    /**
     * Between - Adds WHERE $field BETWEEN $value1 and $value2
     *
     * @param string $field
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return Query
     */
    public function between ($field,$value1,$value2){
        $this->add_where('BETWEEN',$field,$value1,$value2);
        return $this;
    }

    /**
     * Contains value - Adds WHERE $field LIKE %$value%
     *
     * @param string $field
     * @param string $value
     *
     * @return Query
     */
    public function contains ($field,$value){
        $this->add_where('CONTAINS',$field,$value);
        return $this;
    }

    /**
     * Begins with value - Adds WHERE $field LIKE $value%
     *
     * @param string $field
     * @param string $value
     *
     * @return Query
     */
    public function begins($field,$value){
        $this->add_where('BEGINS',$field,$value);
        return $this;
    }

    /**
     * Ends with value - Adds WHERE $field LIKE %$value
     *
     * @param string $field
     * @param string $value
     *
     * @return Query
     */
    public function ends($field,$value){
        $this->add_where('ENDS',$field,$value);
        return $this;
    }

    /**
     * And - Adds AND to WHERE
     *
     * @return Query
     */
    public function w_and (){
        $this->add_where('AND');
        return $this;
    }

    /**
     * Or - Adds OR to WHERE
     *
     * @return Query
     */
    public function w_or (){
        $this->add_where('OR');
        return $this;
    }

    /**
     * Group - Adds ( to WHERE
     *
     * @return Query
     */
    public function g (){
        $this->add_where('(');
        return $this;
    }

    /**
     * Group Close - Adds ) to WHERE
     *
     * @return Query
     */
    public function gc (){
        $this->add_where(')');
        return $this;
    }


//----------------- Order----------------------//

    /**
     * Ascending Order - Adds ORDER BY $field ASC
     *
     * @param string $field;
     *
     * @return Query
     */
    public function asc ($field){
        $this->invalidate();
        $this->_orders[]=array('ASC',$field);
        return $this;
    }

    /**
     * Descending Order - Adds ORDER BY $field DESC
     *
     * @param string $field;
     *
     * @return Query
     */
    public function desc ($field){
        $this->invalidate();
        $this->_orders[]=array('DESC',$field);
        return $this;
    }


//----------------- Limits ----------------------//

    /**
     * Limit - add LIMIT
     *
     * @param int $limit
     *
     * @return Query
     */

    public function limit($limit){
        $this->invalidate();
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Offset - add Offset to LIMIT
     *
     * @param int $offset
     *
     * @return Query
     */

    public function offset($offset){
        $this->invalidate();
        $this->_offset = $offset;
        return $this;
    }
// -----------------Joins------------------------//
    /**
     * Adds a JOIN Directive, to be used with SELECT queries
     *
     * @param string $sourceField - field from source table to connect to
     * @param string $otherTable - table with key
     * @param string $otherField - field from other table to get
     * @param string $cmpOtherField - field from other table to comapre with $sourceField
     * @param string $joinType - Join Type: "LEFT/RIGHT INNER/OUTER JOIN"
     */
    protected function addJoin($sourceField, $otherTable, $otherField, $cmpOtherField, $joinType){
        $this->invalidate();
        $joinData = new stdClass;
        $joinData->type = $joinType;
        $joinData->table = $otherTable;
        $joinData->field = $otherField;
        $joinData->cmpField = $cmpOtherField;
        $this->_joins[$sourceField]=$joinData;
    }

    /**
     * Adds a LEFT OUTER JOIN Directive, to be used with SELECT queries
     *
     * @param string $sourceField - field from source table to connect to
     * @param string $otherTable - table with key
     * @param string $otherField - field from other table to get
     * @param string $cmpOtherField - field from other table to comapre with $sourceField
     */
    public function left_join($sourceField, $otherTable, $otherField, $cmpOtherField){
        $this->addJoin($sourceField, $otherTable, $otherField, $cmpOtherField, 'LEFT OUTER JOIN');
        return $this;
    }

    /**
     * Adds a RIGHT OUTER JOIN Directive, to be used with SELECT queries
     *
     * @param string $sourceField - field from source table to connect to
     * @param string $otherTable - table with key
     * @param string $otherField - field from other table to get
     * @param string $cmpOtherField - field from other table to comapre with $sourceField
     */
    public function right_join($sourceField, $otherTable, $otherField, $cmpOtherField){
        $this->addJoin($sourceField, $otherTable, $otherField, $cmpOtherField, 'RIGHT OUTER JOIN');
        return $this;
    }

    /**
     * Default Join Adds a LEFT OUTER JOIN Directive, to be used with SELECT queries
     *
     * @param string $sourceField - field from source table to connect to
     * @param string $otherTable - table with key
     * @param string $otherField - field from other table to get
     * @param string $cmpOtherField - field from other table to comapre with $sourceField
     */
    public function join($sourceField, $otherTable, $otherField, $cmpOtherField){
        $this->addJoin($sourceField, $otherTable, $otherField, $cmpOtherField, 'INNER JOIN');
        return $this;
    }

    //-----------------------builders------------------------------//

    /**
     * Builds Query
     *
     * @return string
     */
    public function build(){

        if ($this->_built == ''){
            if ($this->_operation == 'SELECT'){
                $this->_built = $this->build_select();
            } else if ($this->_operation == 'INSERT'){
                $this->_built = $this->build_insert();
            } else if ($this->_operation == 'UPDATE'){
                $this->_built = $this->build_update();
            } else if ($this->_operation == 'DELETE'){
                $this->_built = $this->build_delete();
            }
        }
        return $this->_built;
    }

    //-------------------Query Builders-----------------------------//
    private function build_insert(){
        $table = static::tick($this->_table);
        $fields = '('.$this->build_fields().')';
        $values = '('.$this->build_values().')';

        $result = 'INSERT INTO '.$table.' '.$fields.' VALUES '.$values;
        return $result;
    }

    private function build_select(){
        $table = static::tick($this->_table);
        $joins = $this->build_joins();
        $fields = $this->build_fields();
        $where = $this->build_where();
        $order = $this->build_order();
        $limit = $this->build_limit();

        $result = 'SELECT '.$fields.' FROM '.$table.' '.$joins.' '.$where.' '.$order.' '.$limit;
        return $result;
    }

    private function build_update(){
        $table = static::tick($this->_table);
        $pairs = $this->build_pairs();
        $where = $this->build_where();
        $order = $this->build_order();
        $limit = $this->build_limit();

        $result = 'UPDATE '.$table.' SET '.$pairs.' '.$where.' '.$order.' '.$limit;
        return $result;
    }

    private function build_delete(){
        $table = static::tick($this->_table);
        $where = $this->build_where();
        $order = $this->build_order();
        $limit = $this->build_limit();

        $result = 'DELETE FROM '.$table.' '.$where.' '.$order.' '.$limit;
        return $result;
    }


    //-----------------Clause Builders--------------------------------//
    private function build_fields(){
        $result = '*';
        if (count($this->_fields)>0){
            $fields = array();
            foreach ($this->_fields as $field){
                $newField=static::tick($this->_table).'.'.static::tick($field);
                if (array_key_exists($field, $this->_fieldMap)){
                    $newField.= ' as '.static::tick($this->_fieldMap[$field]);
                }
                $fields[] = $newField;
                if (array_key_exists($field, $this->_joins)){
                    $join = $this->_joins[$field];
                    $fields[]= static::tick($field.'__table').'.'.static::tick($join->field).' AS '.static::tick($field.'__value');
                    $fields[]=static::tick($field.'__table').'.`ID` AS '.static::tick($field.'__value_ID');
                }
            }
            $result = implode(', ', $fields);
        }
        return $result;
    }

    private function build_joins(){
        $result = '';
        $joins = array();

        foreach ($this->_joins as $field=>$join){
            $newJoin = $join->type.' ';
            $newJoin.= '`'.$join->table.'` AS `'.$field.'__table` ON ';
            $newJoin.= '`'.$this->_table.'`.`'.$field.'` = `'.$field.'__table`.`'.$join->cmpField.'`';
            $joins[] = $newJoin;
        }
        $result = implode(' ', $joins);
        return $result;
    }

    private function build_values(){
        $result = '';
        if (count($this->_values)>0){
            $values = array();
            foreach ($this->_values as $value){
                $values[]='"'.DB::escape($value).'"';
            }
            $result = implode(', ', $values);
        }
        return $result;
    }

    private function build_pairs(){
        $result = '';
        $pairs = array();
        for ($i = 0; $i<count($this->_fields); $i++){
            $pairs[] = static::tick($this->_table).'.'.static::tick($this->_fields[$i]).'="'.DB::escape($this->_values[$i]).'"';
        }
        $result = implode(', ',$pairs);
        return $result;
    }

    private function build_where(){
        $result = '';
        $tab = static::tick($this->_table);
        $glue = '#####AND#####';
        $depth = 0;
        foreach ($this->_wheres as $where){
            $clause = '';
            if (in_array($where[0],array('=','<>','<','<=','>','>='))){
                $clause = $tab.'.'.static::tick($where[1]).$where[0].'"'.DB::escape($where[2]).'"';
            } else if (in_array($where[0],array('IS NULL','IS NOT NULL'))){
                $clause = $where[0].' '.$tab.'.'.static::tick($where[1]);
            } else if ($where[0]=='IN'){
                $escaped = array();
                foreach ($where[2] as $in){
                    $escaped[]='"'.DB::escape($in).'"';
                }
                $clause = $tab.'.'.static::tick($where[1]).' IN ('.implode(',', $escaped).')';
            } else if ($where[0] == 'BETWEEN'){
                $clause = $tab.'.'.static::tick($where[1]).' BETWEEN "'.DB::escape($where[2]).'" AND "'.DB::escape($where[3]).'"';
            } else if (in_array($where[0],array('CONTAINS','BEGINS','ENDS'))){
                $val = DB::escape($where[2]);
                if ($where[0] == 'CONTAINS'){
                    $val = '%'.$val.'%';
                } else if ($where[0] == 'BEGINS'){
                    $val = $val.'%';
                }  else if ($where[0] == 'ENDS'){
                    $val = '%'.$val;
                }
                $clause = $tab.'.'.static::tick($where[1]).' LIKE "'.$val.'"';
            } else if (in_array($where[0],array('AND','OR'))) {
                $glue = '#####'.$where[0].'#####';
            } else if ($where[0] == '('){
                $depth++;
                $clause = '#####(#####';
            } else if ($where[0] == ')'){
                $depth--;
                if( $depth >= 0){
                    $clause = '#####)#####';
                } else {
                    trigger_error('Unexpected Closing Parentheses in Query - not used.',E_USER_WARNING);
                }
            } else { //Explicit Where Clause
                $clause = $where[0];
            }

            if ($result != '' && $clause!=''){
                $result.=' '.$glue.' ';
            }
            $result.=$clause;
        }

        if ($depth > 0){
            trigger_error('Missing '.$depth.' Closing Parentheses in Query - appending.',E_USER_NOTICE);
            for ($i = 0; $i<$depth; $i++){
                $result.=' #####)#####';
            }
        }

        if ($result != ''){
            $result = 'WHERE '.$result;
        }

        $result=  str_replace('#####(##### #####OR#####', '#####(#####', $result);
        $result=  str_replace('#####(##### #####AND#####', '#####(#####', $result);
        $result=  str_replace('#####OR##### #####)#####', '#####)#####', $result);
        $result=  str_replace('#####AND##### #####)#####', '#####)#####', $result);

        $result=  str_replace('#####)#####', ')', $result);
        $result=  str_replace('#####(#####', '(', $result);
        $result=  str_replace('#####AND#####', 'AND', $result);
        $result=  str_replace('#####OR#####', 'OR', $result);

        return $result;
    }

    private function build_limit(){
        $result = '';
        if (!is_null($this->_limit) && !is_null($this->_offset)){
            $result = 'LIMIT '.$this->_offset.', '.$this->_limit;
        } else if (is_null($this->_limit) && !is_null($this->_offset)) {
            $result = 'LIMIT '.$this->_offset.', 99999999999';
        } else if (!is_null($this->_limit) && is_null($this->_offset)) {
            $result = 'LIMIT '.$this->_limit;
        }
        return $result;
    }

    private function build_order(){
        $result = '';
        if (count ($this->_orders)>0){
            $orders = array();
            foreach ($this->_orders as $order){
                $orders[] =static::tick($this->_table).'.'.static::tick($order[1]).' '.$order[0];
            }
            $result = 'ORDER BY '.implode(', ',$orders);
        }
        return $result;
    }


}