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
     * プラグイン設定
     * @var array
     */
    private static $settings;
    
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
        
        // 設定を保存。
        $settings = self::getDefaultSettings();
        self::saveSettings($settings);
    }
    
    /**
     * @return array
     */
    public static function getDefaultSettings() {
        return array(
            'use_holderjs' => true,
        );
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
                if (Zenith_Eccube_Utils::isStringEndWith($filename, 'main_frame.tpl')) {
                    $tpl_path = "plg_Devel_main_frame_header.tpl";
                    $tpl = "<!--{include file='{$tpl_path}'}-->";
                    $transformer->select('head')->appendChild($tpl);
                    break;
                }
                
                if (Zenith_Eccube_Utils::isStringEndWith($filename, 'system/subnavi.tpl')) {
                    $tpl_path = "system/plg_Devel_subnavi_item.tpl";
                    $tpl = "<!--{include file='{$tpl_path}'}-->";
                    $transformer->select('ul')->appendChild($tpl);
                    break;
                }
                
                break;

            case DEVICE_TYPE_PC:
                if (Zenith_Eccube_Utils::isStringEndWith($filename, 'site_frame.tpl')) {
                    $tpl_path = "plg_Devel_site_frame_header.tpl";
                    $tpl = "<!--{include file='{$tpl_path}'}-->";
                    $transformer->select('head')->appendChild($tpl);
                    break;
                }

                if (Zenith_Eccube_Utils::isStringEndWith($filename, 'popup_frame.tpl')) {
                    $tpl_path = "plg_Devel_popup_frame_header.tpl";
                    $tpl = "<!--{include file='{$tpl_path}'}-->";
                    $transformer->select('head')->appendChild($tpl);
                    break;
                }
                
                break;
        }
        
        $source = $transformer->getHTML();
    }
    
    /**
     * 設定を読み込みます。
     * 
     * @param bool $reload
     * @return array
     */
    public static function loadSettings($reload = false) {
        if ($reload || self::$settings === null) {
            $query = SC_Query_Ex::getSingletonInstance();
            
            $free_field2 = $query->get('free_field2', 'dtb_plugin', 'plugin_code = ?', array('Devel'));
            if (PEAR::isError($free_field2)) {
                throw new RuntimeException($free_field2->toString());
            }

            self::$settings = Zenith_Eccube_Utils::decodeJson($free_field2, true);
        }
        
        return self::$settings;
    }
    
    /**
     * 設定を保存します。
     * 
     * @param array $settings
     * @return array
     */
    public static function saveSettings(array $settings) {
        $query = SC_Query_Ex::getSingletonInstance();
        
        $values = array();

        $free_field2 = Zenith_Eccube_Utils::encodeJson($settings);
        $values['free_field2'] = $free_field2;

        $query->update('dtb_plugin', $values, 'plugin_code = ?', array('Devel'));
        if (PEAR::isError($free_field2)) {
            throw new RuntimeException($free_field2->toString());
        }
    }
    
    /**
     * 指定した設定を取得します。
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null) {
        $settings = self::loadSettings();
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }
    
    /**
     * プラグインの情報を
     * 
     * @param LC_Page_Ex $page
     */
    public function preProcess(LC_Page_Ex $page) {
        $settings = self::loadSettings();
        $page->plg_Devel_settings = $settings;
    }
}
