<?php
class DbSchema {
    public static function Update($table,$definition) {
        //_gc($table, 'Schema Update');
        $definition = static::flattenDefinition($definition);
        if (!self::Table_exists($table)) {
            self::Create_Table($table,$definition);
        } else {
            $currentQuery = 'SHOW FULL COLUMNS FROM `'.$table.'`';
            $cols = DB::get($currentQuery);
            //_d($cols,'columns from DB');
            $currCols = [];
            foreach ($cols as $col) {
                $fieldObj = new DbSchemaField();
                $fieldObj->setFromQuery($col);
                $currCols[$col->Field] = $fieldObj;
            }

            //_d($currCols,'current columns');

            $defCols = [];
            foreach ($definition as $fieldName=>$def){
                $fieldObj = new DbSchemaField;
                $fieldObj->setFromDefinition($fieldName, $def);
                $defCols[$fieldName] = $fieldObj;
            }

            //_d($defCols,'definition columns');


            $operations = [];
            //_g('checking fields');
            foreach ($defCols as $fieldName=>$def) {
                //_gc('defcol');
                    //_d($defCols);
                    //_d($fieldName);
                    //_d($def);
                //_u();
                if (!array_key_exists($fieldName, $currCols)){
                    //_d($fieldName, 'does not exist in currcols');
                    $op = $def->getAddField();
                    //_d($op);
                    $operations[] = $op;
                } else {
                    //_d($fieldName, 'exists in currcols');
                    if ($def->differsFrom($currCols[$fieldName])){
                        //_d($fieldName,'DIFFERS');
                        $operations[] = $def->getAlterField($currCols[$fieldName]);
                    } else {
                        //_d($fieldName,'does not differ');
                    }

                }
            }
            //_u();

            //if (Config::get('db.schema.update.delete')) {
                foreach ($currCols as $fieldName=>$def){
                    if (!array_key_exists($fieldName,$defCols)) {
                        $operations[] = $currCols[$fieldName]->getDrop();
                    }
                }
            //}

            //_d($operations,"operations");

            if (count($operations) > 0) {
                $sql = 'ALTER TABLE `'.$table.'` '.implode(', ',$operations);
                //_d($sql);
                DB::query($sql);
            }

        }
        //_u();
    }

    private static function Table_exists($tableName) {
        $query = 'SHOW TABLES LIKE "' . $tableName . '"';
        $result = DB::get($query);
        return (bool)$result;
    }

    private static function Create_Table($tableName,$definition){

        $fields = [];
        $definition = static::flattenDefinition($definition);
        foreach ($definition as $fieldName => $def) {
            $field = new DbSchemaField();
            $field->setFromDefinition($fieldName,$def);
            $fields[] = $field;
        }

        $fieldDefs = [];
        foreach ($fields as $field){
            $fieldDefs[] = $field->getCreateField();
        }

        $query = 'CREATE TABLE `' . $tableName . '` (' . implode(' , ', $fieldDefs) . ')';

        DB::query($query);
    }

    private static function flattenDefinition($definition){
        //_g('flattenDefinition');
        //_d($definition);
        $result = array_merge($definition);
        foreach ($definition as $fieldName=>$def){

            if (substr($def,0,1) == '@') {

                $className = substr($def,1).'Repository';
                $vars = get_class_vars($className);
                $fdef = $vars['Fields'][$vars['PrimaryKey']];
                $fdefParts = explode(',',$fdef);
                $result[$fieldName] = $fdefParts[0];
            }
        }
        //_d($result);
        //_u();
        return $result;
    }

}

