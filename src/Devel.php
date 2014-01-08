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

require_once PLUGIN_UPLOAD_REALDIR . 'Devel/plugin_bootstrap.php';

/**
 * 開発支援プラグイン
 *
 * @package Devel
 * @author Seiji Nitta
 */
class Devel extends SC_Plugin_Base {
    /**
     * コンストラクタ
     */
    public function __construct(array $info) {
        parent::__construct($info);
    }
    
    /**
     * プラグインをインストールします。
     *
     * @param array $info プラグイン情報(dtb_plugin)
     */
    public function install($info) {
        $plugin_code = $info['plugin_code'];

        // ロゴを配置。
        $src = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/logo.png";
        $dest = PLUGIN_HTML_REALDIR . "{$plugin_code}/logo.png";
        copy($src, $dest);

        // テンプレートを配置。
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/templates/";
        $dest_dir = SMARTY_TEMPLATES_REALDIR;
        SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);
    }
    
    /**
     * プラグインをアンインストールします。
     *
     * @param array $info プラグイン情報
     */
    public function uninstall($info) {
        $plugin_code = $info['plugin_code'];

        // ロゴを削除。
        $path = PLUGIN_HTML_REALDIR . "{$plugin_code}/logo.png";
        unlink($path);
        
        // テンプレートを削除。 
        $target_dir = SMARTY_TEMPLATES_REALDIR;
        $source_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/templates/";
        Zenith_Eccube_Utils::deleteFileByMirror($target_dir, $source_dir);
    }
    
    /**
     * プラグインを有効化します。
     *
     * @param array $info プラグイン情報
     */
    public function enable($info) {
        $plugin_code = $info['plugin_code'];
        
        // プラグイン用の公開ファイルを配置。 
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/plugin/";
        $dest_dir = PLUGIN_HTML_REALDIR . "{$plugin_code}/";
        SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);
        
        // 管理者用の公開ファイルを配置。
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/admin/";
        $dest_dir = HTML_REALDIR . ADMIN_DIR;
        SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);

        // 顧客用の公開ファイルを配置。
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/customer/";
        $dest_dir = HTML_REALDIR;
        SC_Utils::copyDirectory($src_dir, $dest_dir);
    }
    
    /**
     * プラグインを無効化します。
     *
     * @param array $info プラグイン情報
     */
    public function disable($info) {
        $plugin_code = $info['plugin_code'];
        
        // プラグイン用の公開ファイルを削除。 
        $target_dir = PLUGIN_HTML_REALDIR . "{$plugin_code}/";
        $source_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/plugin/";
        Zenith_Eccube_Utils::deleteFileByMirror($target_dir, $source_dir);
        
        // 管理者用の公開ファイルを削除。 
        $target_dir = HTML_REALDIR . ADMIN_DIR;
        $source_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/admin/";
        Zenith_Eccube_Utils::deleteFileByMirror($target_dir, $source_dir);

        // 顧客用の公開ファイルを削除。
        $target_dir = HTML_REALDIR;
        $source_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/customer/";
        Zenith_Eccube_Utils::deleteFileByMirror($target_dir, $source_dir);
    }
    
    /**
     * フックを登録します。
     *
     * @param SC_Helper_Plugin $plugin_helper
     * @param int $priority
     */
    public function register(SC_Helper_Plugin $plugin_helper, $priority) {
        parent::register($plugin_helper, $priority);

        // プラグイン関連の画面を挿入する。
        $plugin_helper->addAction('prefilterTransform', array($this, 'hook_prefilterTransform'));
    }
    
    /**
     * prefilterTransform フックアクション。
     * 
     * @param string $source
     * @param LC_Page_Ex $page
     * @param string $filename
     */
    public function hook_prefilterTransform(&$source, LC_Page_Ex $page, $filename) {
        $transformer = new SC_Helper_Transform($source);

        $device_type_id = GC_Utils_Ex::isAdminFunction()
            ? DEVICE_TYPE_ADMIN
            : (isset($page->arrPageLayout['device_type_id']) ? $page->arrPageLayout['device_type_id'] : SC_Display_Ex::detectDevice());
        
        switch ($device_type_id) {
            case DEVICE_TYPE_ADMIN:
                if (DEBUG_MODE && Zenith_Eccube_Utils::isStringEndWith($filename, 'system/subnavi.tpl')) {
                    $tpl_path = "system/plg_Devel_subnavi_item.tpl";
                    $tpl = "<!--{include file='{$tpl_path}'}-->";
                    $transformer->select('ul')->appendChild($tpl);
                    break;
                }
                
                break;
        }
        
        $source = $transformer->getHTML();
    }
}
