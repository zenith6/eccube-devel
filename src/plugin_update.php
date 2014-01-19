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

require_once 'plugin_bootstrap.php';

/**
 * プラグインアップデート
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plugin_update {
    const MIGRATION_VERSION_DELIMITER = '/';
    
    /**
     * プラグインをアップデートします。
     *
     * @param array $info プラグイン情報。
     * @return void
     */
    public function update($info) {
        $migration_dir = dirname(__FILE__) . '/migrations';
        self::execute($info, 'up', $migration_dir);
    }
    
    /**
     * マイグレーションを実行します。
     * 
     * @param array $info プラグイン情報。
     * @param string $dir 実行方向。
     * @param string $container_dir マイグレーションコンテナのディレクトリ。
     */
    public static function execute($info, $direction, $container_dir) {
        // コンテナを全て取得
        $containers = self::findContainers($container_dir);
        
        // 適用済みのバージョンを取得
        // eg. array('version1' = null, 'version2' = null ...)
        // 
        // @todo free_field1 の格納上限に引っかからないうちに正規化する
        $applieds = array_flip((array)explode(self::MIGRATION_VERSION_DELIMITER, $info['free_field1']));

        // 実行待ちのコンテナを列挙
        // アップデート時: 未適用のコンテナ
        // ダウングレード時: 適用済みのコンテナ
        switch ($direction) {
            case 'up':
                $method = 'up';
                $pendings = array_diff_key($containers, $applieds);
                usort($pendings, array('plugin_update', 'sortMigrationUp'));
                break;
                
            case 'down':
                $method = 'down';
                $pendings = array_intersect_key($containers, $applieds);
                usort($pendings, array('plugin_update', 'sortMigrationDown'));
                $applieds = array();
                break;
                
            default:
                $message = sprintf('$direction is must "up" or "down". "%s" was given.', $direction);
                throw new InvalidArgumentException($message);
        }
        
        /*
         *  マイグレーションの実行
         */
        $query = SC_Query_Ex::getSingletonInstance();
        foreach ($pendings as $container) {
            try {
                // トランザクションはコンテナ単位
                $query->begin();
                
                // コンテナに含まれるマイグレーションを全て実行
                $classes = self::loadMigrationClass($container['path']);
                foreach ($classes as $class) {
                    $message = 'マイグレーションを実行中しました: plugin=%s version="%s", file="%s", class=%s#%s';
                    $log = sprintf($message, $info['plugin_code'], $container['version'], $container['path'], $class, $method);
                    GC_Utils_Ex::gfPrintLog($log);
                    
                    $migration = new $class($info, $query);
                    $migration->$method();
                }
                
                // 適用済みのマイグレーションを更新
                if ($direction == 'up') {
                    $applieds[$container['version']] = true;
                } else {
                    unset($applieds[$container['version']]);
                }
    
                // 実行済みのマイグレーションを記録
                $free_field1 = implode(self::MIGRATION_VERSION_DELIMITER, array_keys($applieds));
                $values = array('free_field1' => $free_field1);
                $query->update('dtb_plugin', $values, 'plugin_code = ?', array($info['plugin_code']));

                $query->commit();
            } catch (Exception $e) {
                $query->rollback();
                
                $message = 'マイグレーションを実行中に問題が発生しました: plugin=%s version="%s", file="%s", class="%s#%s", message="%s"';
                $log = sprintf($message, $info['plugin_code'], $container['version'], $container['path'], $class, $method, $e->__toString());
                GC_Utils_Ex::gfPrintLog($log);
                echo $log, PHP_EOL;
                
                throw $e;
            }
        }
    }
    
    private static function sortMigrationUp($a, $b) {
        return strnatcasecmp($a['version'], $b['version']);
    }
    
    private static function sortMigrationDown($a, $b) {
        return strnatcasecmp($b['version'], $a['version']);
    }
    
    /**
     * @param string $file
     * @return array
     */
    private static function loadMigrationClass($file) {
        $exists = get_declared_classes();
        require_once $file;
        $classes = array_diff(get_declared_classes(), $exists);
        
        $migrations = array();
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isSubclassOf('Zenith_Eccube_Migration')) {
                $migrations[] = $class;
            }
        }
    }
    
    private static function sortMigrationUp($a, $b) {
        return strnatcasecmp($a['version'], $b['version']);
    }
    
    private static function sortMigrationDown($a, $b) {
        return strnatcasecmp($b['version'], $a['version']);
    }
    
    /**
     * @param string $file
     * @return array
     */
    private static function loadMigrationClass($file) {
        $exists = get_declared_classes();
        require_once $file;
        $classes = array_diff(get_declared_classes(), $exists);
        
        $migrations = array();
        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isSubclassOf('Zenith_Eccube_Migration')) {
                $migrations[] = $class;
            }
        }
        
        return $migrations;
    }
    
    /**
     * @param string $path
     * @return array
     */
    public static function findContainers($path) {
        $containers = array();
        $files = new DirectoryIterator($path);
        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() != 'php') {
                continue;
            }
            
            $version = $file->getBasename('.php');
            $containers[$version] = array(
                'path' => $file->getPathname(),
                'version' => $version,
            );
        }
        return $containers;
    }
}
