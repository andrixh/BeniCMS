<?php
class DbSchemaField {
    private static $keyTypes = [
        'PRIMARY' => 'PRI',
        'UNIQUE' => 'UNI',
        'INDEX' => 'MUL'
    ];

    public $field = '';
    public $type = '';
    public $null = true;
    public $default = null;
    public $auto_increment = false;
    public $key = '';
    public $comment = '';

    public function setFromQuery ($rowData) {
        $this->field = $rowData->Field;
        $this->type = strtoupper($rowData->Type);
        $this->null = ($rowData->Null == 'YES');
        $this->auto_increment =  (strpos($rowData->Extra,'auto_increment')!==false);
        foreach (static::$keyTypes as $key=>$value){
            if ($rowData->Key == $value){
                $this->key = $key;
            }
        }
        $this->default = ($rowData->Default=='NULL') ? null: $rowData->Default;
        $this->comment = $rowData->Comment;
    }

    public function setFromDefinition ($field, $def) {
        $this->field = $field;

        $defParts = explode(',',$def);

        $this->type = trim(array_shift($defParts));
        foreach ($defParts as $defPart){
            $def = trim($defPart);
            if (strpos($def,'PRIMARY') !== false) {
                $this->key = 'PRIMARY';
            } else if (strpos($def,'INDEX') !== false) {
                $this->key =  'INDEX';
            } else if (strpos($def,'UNIQUE') !== false) {
                $this->key =  'UNIQUE';
            }

            if (strpos($def,'AUTO_INCREMENT') !== false) {
                $this->auto_increment = true;
            }

            if ($def == 'NOT NULL') {
                $this->null = false;
            }

            if (substr($def,0,1) == '=') {
                $this->default = substr($def,1);
            }

            if (substr($def,0,2) == '//') {
                $this->comment = substr($def,2);
            }
        }
    }

    public function differsFrom($compare) {
        //Debug::groupCollapsed('checking differences');
        $result = false;
        if ($this->type != $compare->type){
            //Debug::log('type differs');
            $result =  true;
        } else if ($this->null != $compare->null){
            //Debug::log('null differs');
            $result =  true;
        } else if ($this->auto_increment != $compare->auto_increment){
            //Debug::log('auto_increment differs');
            $result =  true;
        } else if ($this->key != $compare->key){
            //Debug::log('key differs');
            $result =  true;
        } else if ($this->default != $compare->default){
            //Debug::log('default differs');
            $result =  true;
        } else if ($this->comment != $compare->comment){
            //Debug::log('comment differs');
            $result =  true;
        } else {
            $result = false;
        }
        //Debug::groupClose();
        return $result;
    }

    public function getCreateField(){
        $result = [];
        $keyResult = '';
        $result[] = '`'.$this->field.'`';
        $result[] = $this->type;
        if (!$this->null) {
            $result[] = 'NOT NULL';
        }
        if ($this->auto_increment) {
            $result[] = 'AUTO_INCREMENT';
        }
        if ($this->default!== null) {
            if (is_numeric($this->default) || $this->default == 'NULL' || $this->default == 'CURRENT_TIMESTAMP') {
                $result[] = "DEFAULT ".$this->default;
            } else {
                $result[] = "DEFAULT '".$this->default."'";
            }
        }
        if ($this->comment != '') {
            $result[] = "COMMENT '".$this->comment."'";
        }
        if ($this->key == 'PRIMARY'){
            $keyResult = ' , PRIMARY KEY (`'.$this->field.'`)';
        } else if ($this->key == 'UNIQUE'){
            $keyResult = ' , UNIQUE (`'.$this->field.'`)';
        } else if ($this->key == 'INDEX'){
            $keyResult = ' , INDEX (`'.$this->field.'`)';
        }
        return implode(' ',$result).$keyResult;
    }

    public function getAlterField($compare){
        $field = '';
        if ($this->differentDefinition($compare)){
            $field = [];
            $field[] = 'MODIFY `'.$this->field.'` ';
            $field[] = $this->type;
            if (!$this->null) {
                $field[] = 'NOT NULL';
            }
            if ($this->auto_increment) {
                $field[] = 'AUTO_INCREMENT';
            }
            if ($this->default!==null) {
                if (is_numeric($this->default) || $this->default == 'NULL' || $this->default == 'CURRENT_TIMESTAMP') {
                    $field[] = "DEFAULT ".$this->default;
                } else {
                    $field[] = "DEFAULT '".$this->default."'";
                }
            }
            if ($this->comment != '') {
                $field[] = "COMMENT '".$this->comment."'";
            }
            $field = implode (' ',$field);
        }


        $keys = $this->getAlterKeys($compare);

        if ($field && $keys) {
            return $field.', '.$keys;
        } else if ($field || $keys) {
            return $field.$keys;
        } else {
            return false;
        }

    }

    public function getAddField(){
        $result = [];
        $keyResult = '';
        $result[] = 'ADD `'.$this->field.'`';
        $result[] = $this->type;
        if (!$this->null) {
            $result[] = 'NOT NULL';
        }
        if ($this->auto_increment) {
            $result[] = 'AUTO_INCREMENT';
        }
        if ($this->default !== null) {
            if (is_numeric($this->default) || $this->default == 'NULL' || $this->default == 'CURRENT_TIMESTAMP') {
                $result[] = "DEFAULT ".$this->default;
            } else {
                $result[] = "DEFAULT '".$this->default."'";
            }
        }
        if ($this->comment != '') {
            $result[] = "COMMENT '".$this->comment."'";
        }
        if ($this->key == 'PRIMARY'){
            $keyResult = ' , ADD PRIMARY KEY (`'.$this->field.'`)';
        } else if ($this->key == 'UNIQUE'){
            $keyResult = ' , ADD UNIQUE (`'.$this->field.'`)';
        } else if ($this->key == 'INDEX'){
            $keyResult = ' , ADD INDEX (`'.$this->field.'`)';
        }
        return implode(' ',$result).$keyResult;

    }

    private function differentDefinition($compare) {
        if ($this->type != $compare->type){
            return true;
        }
        if ($this->null != $compare->null){
            return true;
        }
        if ($this->auto_increment != $compare->auto_increment){
            return true;
        }
        if ($this->default != $compare->default){
            return true;
        }
        if ($this->comment != $compare->comment){
            return true;
        }
        return false;
    }

    private function getAlterKeys($compare) {
        $result = '';

        if ($this->key != $compare->key) {
            $result = [];
            if ($compare->key != '') {
                if ($compare->key == 'PRIMARY') {
                    $result[] = 'DROP PRIMARY KEY';
                } else {
                    $result[] = 'DROP INDEX `'.$compare->field.'` ';
                }
            }
            if ($this->key!= ''){
                if ($this->key == 'PRIMARY') {
                    $result[] = 'ADD PRIMARY KEY (`'.$this->field.'`)';
                } else {
                    $result[] = 'ADD '.$this->key.' (`'.$this->field.'`) ';
                }
            }
            $result = implode (', ', $result);
        }

        return $result;
    }

    public function getDrop(){
        $result = '';
        if ($this->key !== ''){
            if ($this->key == 'PRIMARY') {
                $result.='DROP PRIMARY KEY, ';
            } else {
                $result.='DROP INDEX `'.$this->field.'`, ';
            }
        }
        $result .= 'DROP `'.$this->field.'` ';
        return $result;
    }

}