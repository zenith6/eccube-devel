<?php

class Zenith_Eccube_Utils {
    /**
     * @param SC_Query_Ex $query
     * @return MDB2
     */
    public static function getMDB2(SC_Query_Ex $query) {
        return $query->conn;
    }
    
    public static function buildDatabaseSchema(SC_Query_Ex $query, array $tables = array(), $sequences = array()) {
        $query = SC_Query_Ex::getSingletonInstance();
        $mdb2 = $query->conn;
        $mdb2->loadModule('Manager');
        $mdb2->loadModule('Reverse');
        
        if (!$tables) {
            $table_names = $mdb2->listTables();
            if (PEAR::isError($tables)) {
                throw new RuntimeException($tables->toString());
            }
            
            foreach ($table_names as $table_name) {
                $tables[$table_name] = array();
            }
        }
        
        $def = array();
        
        foreach ($tables as $table_name => $table_def) {
            $table_def += array(
                'fields' => array(),
                'constraints' => array(),
                'indexes' => array(),
            );
            $def['tables'][$table_name] = self::buildTableSchema($query, $table_name, $table_def['fields'], $table_def['constraints'], $table_def['indexes']);
        }
        
        foreach ($sequences as $seq_name => $init) {
            $def['sequences'][$seq_name] = $init;
        }
        
        return $def;
    }
    
    public static function buildTableSchema(SC_Query_Ex $query, $table_name, array $field_names = array(), array $const_names = array(), array $indexes_names = array()) {
        $query = SC_Query_Ex::getSingletonInstance();
        $mdb2 = $query->conn;
        $mdb2->loadModule('Manager');
        $mdb2->loadModule('Reverse');
        
        $options = array(
            'decimal_places' => 0,
            'idxname_format' => '%s',
        );
        $org_options = array();
        foreach ($options as $key => $value) {
            $org_options[$value] = $mdb2->getOption($key);
            $mdb2->setOption($key, $value);
        }
        
        $def = array(
            'fields' => array(),
        );
        
        if ($field_names || $const_names || $indexes_names) {
            $def['partial'] = true;
        }

        if (!$field_names) {
            $field_names = $mdb2->listTableFields($table_name);
            if (PEAR::isError($field_names)) {
                throw new RuntimeException($field_names->toString());
            }
        }
        
        $field_defs = array();
        $needs = array_flip(array(
            'length',
            'default',
            'notnull',
            'unsigned',
        ));
        foreach ($field_names as $field_name) {
            $info = $mdb2->getTableFieldDefinition($table_name, $field_name);
            if (PEAR::isError($info)) {
                throw new RuntimeException($info->toString());
            }
            $info = array_shift($info);

            $field_def = array(
                'type' => $info['type'],
            ) + array_intersect_key($info, $needs);
            
            // MySQL は Boolean 型ないんで…MDB2は悪くないんで…
            if ($field_def['type'] == 'boolean') {
                $field_def['type'] = 'integer';
                $field_def['length'] = 1;
            }
            
            // そのまま MDB2#createTable に渡すとコケるので（激おこ）
            if ($field_def['type'] == 'decimal' && isset($field_def['length'])) {
                list($scale, $precision) = (array)explode(',', $field_def['length']);
                $field_def['length'] = $scale;
                $field_def['scale'] = $precision;
            }

            // そのまま MDB2#createTable に渡すとコケるので（激おこ）
            if ($field_def['type'] == 'timestamp' && isset($field_def['default'])) {
                $default = preg_replace('/\\r\\n/', ' ', $field_def['default']);
                
                if ($default == '0000-00-00 00:00:00') {
                    $default = null;
                }
                    
                $field_def['default'] = $default;
            }
            
            if (!empty($field_def['notnull']) && !isset($field_def['default'])) {
                unset($field_def['default']);
            }
            
            if (isset($field_def['unsigned']) && !$field_def['unsigned']) {
                unset($field_def['unsigned']);
            }
            
            $field_defs[$field_name] = $field_def;
        }
        
        if ($field_defs) {
            $def['fields'] = $field_defs;
        }


        if (!$const_names) {
            $const_names = $mdb2->listTableConstraints($table_name);
            if (PEAR::isError($const_names)) {
                throw new RuntimeException($const_names->toString());
            }
        }

        $const_defs = array();
        foreach ($const_names as $const_name) {
            $const_def = $mdb2->getTableConstraintDefinition($table_name, $const_name);
            if (PEAR::isError($const_def)) {
                throw new RuntimeException($const_def->toString());
            }
            $const_full_name = $table_name . '_' . $const_name;
            $const_defs[$const_full_name] = $const_def;
        }
        
        if ($const_defs) {
            $def['constraints'] = $const_defs;
        }


        if (!$index_names) {
            $index_names = $mdb2->listTableIndexes($table_name);
            if (PEAR::isError($index_names)) {
                throw new RuntimeException($index_names->toString());
            }
        }

        $index_defs = array();
        foreach ($index_names as $index_name) {
            $index_def = $mdb2->getTableIndexDefinition($table_name, $index_name);
            if (PEAR::isError($index_def)) {
                throw new RuntimeException($index_def->toString());
            }
            $index_full_name = $table_name . '_' . $index_name;
            $index_defs[$index_full_name] = $index_def;
        }
        
        if ($index_defs) {
            $def['indexes'] = $index_defs;
        }
        
        foreach ($options as $key => $value) {
            $mdb2->setOption($key, $org_options[$key]);
        }
        
        return $def;
    }
    
    /**
     * @param MDB2_Driver_Datatype_Common $db
     * @param string $caller
     * @param array $parameter
     */
    public static function callbackTypeTimestamp(MDB2_Driver_Common $db, $method, $parameter) {
        switch ($method) {
            case 'getDeclaration':
                extract($parameter, EXTR_OVERWRITE);
                $name = $db->quoteIdentifier($name, true);
                $declaration_options = $db->datatype->_getDeclarationOptions($field);
                if (PEAR::isError($declaration_options)) {
                    return $declaration_options;
                }
                return $name . ' TIMESTAMP ' . $declaration_options;
                
            case 'quote':
                extract($parameter, EXTR_OVERWRITE);
                return $db->_quoteTimestamp($value, $quote, $escape_wildcards);
        }
        
        throw new RuntimeException('Not supported callback ' . $method);
    }
    
    public static function createTable(SC_Query_Ex $query, $table_name, $table_def) {
        $mdb2 = self::getMDB2($query);
        $mdb2->loadModule('Manager');

        $options = array(
            'decimal_places' => 0,
            'idxname_format' => '%s',
            'datatype_map' => array(
                'timestamp' => 'timestamp',
            ),
            'datatype_map_callback' => array(
                'timestamp' => array(__CLASS__, 'callbackTypeTimestamp'),
            ),
        );
        $org_options = array();
        foreach ($options as $key => $value) {
            $org_options[$key] = $mdb2->getOption($key);
            $mdb2->setOption($key, $value);
        }
        
        if (empty($table_def->partial)) {
            $result = $mdb2->createTable($table_name, $table_def['fields']);
            if (PEAR::isError($result)) {
                throw new RuntimeException($result->toString());
            }
        } else {
            if (!empty($table_def['fields'])) {
                $result = $mdb2->alterTable($table_name, array('add' => $table_def['fields']));
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }

        if (isset($table_def['constraints'])) {
            foreach ($table_def['constraints'] as $const_name => $const_def) {
                $result = $mdb2->createConstraint($table_name, $const_name, $const_def);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }

        if (isset($table_def['indexes'])) {
            foreach ($table_def['indexes'] as $index_name => $index_def) {
                $result = $mdb2->createIndex($table_name, $index_name, $index_def);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }

        foreach ($options as $key => $value) {
            $mdb2->setOption($key, $org_options[$key]);
        }
    }
    
    public static function deleteTable(SC_Query_Ex $query, $table_name, $table_def) {
        $mdb2 = self::getMDB2($query);
        $mdb2->loadModule('Manager');

        $options = array(
            'decimal_places' => 0,
            'idxname_format' => '%s',
        );
        $org_options = array();
        foreach ($options as $key => $value) {
            $org_options[$value] = $mdb2->getOption($key);
            $mdb2->setOption($key, $value);
        }

        if (isset($table_def['indexes'])) {
            foreach ($table_def['indexes'] as $index_name => $index_def) {
                $result = $mdb2->dropIndex($table_name, $index_name);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }

        if (isset($table_def['constraints'])) {
            foreach ($table_def['constraints'] as $const_name => $const_def) {
                if (DB_TYPE == 'mysql' && !empty($const_def['primary'])) {
                    continue;
                }
                
                $result = $mdb2->dropConstraint($table_name, $const_name);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }
        
        if (empty($table_def['partial'])) {
            $result = $mdb2->dropTable($table_name);
        } else {
            $result = $mdb2->alterTable($table_name, array('remove' => $table_def['fields']));
        }

        foreach ($options as $key => $value) {
            $mdb2->setOption($key, $org_options[$key]);
        }
    }
    
    public static function createDatabase(SC_Query_Ex $query, $schema) {
        $mdb2 = self::getMDB2($query);
        $mdb2->loadModule('Manager');

        if (isset($schema['tables'])) {
            foreach ($schema['tables'] as $name => $def) {
                self::createTable($query, $name, $def);
            }
        }
        
        if (isset($schema['sequences'])) {
            foreach ($schema['sequences'] as $seq_name => $init) {
                $result = $mdb2->createSequence($seq_name, $init);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }
    }
    
    public static function deleteDatabase(SC_Query_Ex $query, $schema) {
        $mdb2 = self::getMDB2($query);
        $mdb2->loadModule('Manager');

        if (isset($schema['sequences'])) {
            foreach ($schema['sequences'] as $seq_name => $init) {
                $result = $mdb2->dropSequence($seq_name);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }
        
        if (isset($schema['tables'])) {
            foreach ($schema['tables'] as $table_name => $table_def) {
                self::deleteTable($query, $table_name, $table_def);
            }
        }
    }
    
    public static function insertBulk(SC_Query_Ex $query, $data) {
        foreach ($data as $table => $rows) {
            foreach ($rows as $row) {
                $result = $query->insert($table, $row);
                if (PEAR::isError($result)) {
                    throw new RuntimeException($result->toString());
                }
            }
        }
    }
    
    public static function encodeJson($data) {
        if (function_exists('json_decode')) {
            $json = json_encode($data);
            $error = json_last_error();
            if ($error !== JSON_ERROR_NONE) {
                $message = json_last_error_msg();
                throw new RuntimeException($message, $code);
            }
            return $json;
        }
        
        $encoder = new Services_JSON();
        $json = $encoder->decode($data);
        if (Services_JSON::isError($json)) {
            throw new RuntimeException($json->toString(), $json->getCode());
        }
        
        return $json;
    }
    
    public static function decodeJson($json, $return_assoc = false) {
        if (false && function_exists('json_decode')) {
            $data = json_decode($json, $return_assoc);
            $error = json_last_error();
            if ($error !== JSON_ERROR_NONE) {
                $message = json_last_error_msg();
                throw new RuntimeException($message, $error);
            }
            
            return $data;
        }
        
        // オートローダーを働かせて SERVICES_JSON_LOOSE_TYPE を定義させるため。
        class_exists('Services_JSON');
        
        $options = $return_assoc ? SERVICES_JSON_LOOSE_TYPE : 0;
        $decoder = new Services_JSON($options);
        $data = $decoder->decode($json);
        if (Services_JSON::isError($json)) {
            throw new RuntimeException($data->toString(), $data->getCode());
        }
        
        return $data;
    }
    
    /**
     * 削除対象のディレクトリから比較対象のディレクトリにある同名のファイルを削除します。
     * 要は SC_Utils::copyDirectory() の逆。
     * 
     * @param string $target_dir 削除対象のディレクトリ
     * @param string $source_dir 比較対象のディレクトリ
     */
    public static function deleteFileByMirror($target_dir, $source_dir) {
        $dir = opendir($source_dir);
        while ($name = readdir($dir)) {
            if ($name == '.' || $name == '..') {
                continue;
            }

            $target_path = $target_dir . '/' . $name;
            $source_path = $source_dir . '/' . $name;
            
            if (is_file($source_path)) {
                if (is_file($target_path)) {
                    unlink($target_path);
                    GC_Utils::gfPrintLog("$target_path を削除しました。");
                }
            } elseif (is_dir($source_path)) {
                if (is_dir($target_path)) {
                    self::deleteFileByMirror($target_path, $source_path);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * 文字列に対して別の文字列が後方一致しているかどうかを取得します。
     * 
     * @param string $target
     * @param string $tail
     * @return bool
     */
    public static function isStringEndWith($target, $tail) {
        return !substr_compare($target, $tail, -strlen($tail));
    }
}
