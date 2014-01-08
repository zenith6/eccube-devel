<?php
/*
 * 開発支援プラグイン
 * Copyright (C) 2014 Seiji Nitta All Rights Reserved.
 * http://zenith6.github.io/
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * プラグインアップデート
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plugin_update {
    /**
     * プラグインをアップデートします。
     *
     * @param array $info プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function update($info) {
        self::invokeUpTo($info);
    }
    
    /**
     * 未実行のマイグレーションを実行します。
     * 
     * @param array $info プラグイン情報の連想配列(dtb_plugin)
     * @return array アップデート後のプラグイン情報の連想配列(dtb_plugin)
     */
    protected static function invokeUpTo($info) {
        $points = self::getMigrationPoints();
        foreach ($points as $point) {
            if (strcmp($info['free_field1'], $point['version']) > 0) {
                $log = sprintf('アップデートを実行: %s => %s', $info['free_field'], $point['version']);
                GC_Utils_Ex::gfPrintLog($log);
                call_user_func($point['up_to'], $info);
                $info['free_field1'] = $point['version'];
                $this->savePluginInfo($info);
            }
        }
        
        return $info;
    }
    
    private static function loadMigrations() {
        $points = array();
        $sortkey = array();
        $class = new ReflectionClass(__CLASS__);
        $methods = $class->getMethods();
        $prefix_len = strlen('upTo_');
        foreach ($methods as $method) {
            if (strncmp($method->name, 'upTo_', $prefix_len) == 0) {
                $version = substr($method->name, $prefix_len);
                $version = strtr($version, array('_' => '.'));
                $points[$version] = array(
                    'version' => $version,
                    'up_to' => array($method->class, $method->name),
                );
                $sortkey[] = $version;
            }
        }
        
        natsort($sortkey);
        $sorted = array();
        foreach ($sortkey as $key) {
            $sorted[$key] = $points[$key];
        }
        return $sorted;
    }
    
    /**
     * プラグイン情報を保存します。
     * 
     * @param array $new_info 新しいプラグイン情報の連想配列(dtb_plugin)
     * @param array $old_info 以前のプラグイン情報の連想配列(dtb_plugin)
     */
    protected static function savePluginInfo($new_info, $old_info) {
        $query = SC_Query_Ex::getSingletonInstance();
        $fields = array(
            'plugin_name',
            'plugin_code',
            'class_name',
            'author',
            'author_site_url',
            'plugin_site_url',
            'plugin_version',
            'compliant_version',
            'plugin_description',
            'free_field1',
            'free_field2',
            'free_field3',
            'free_field4',
        );
        $values = array();
        foreach ($fields as $field) {
            $values[$field] = $new_info[$field];
        }
        $query->update('dtb_plugin', $values, 'plugin_code = ?', array($old_info['plugin_code']));
    }
}
