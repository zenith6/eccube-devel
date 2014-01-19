<?php

class plg_Devel_Migrations_AddDummyGenerator extends Zenith_Eccube_Migration {
    public function up() {
        $plugin_code = $this->plugin_info['plugin_code'];

        // プラグイン本体を更新
        $src_dir = DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR;
        $dest_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/";
        SC_Utils::copyDirectory($src_dir, $dest_dir);
        
        // テンプレートを更新。
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/templates/";
        $dest_dir = SMARTY_TEMPLATES_REALDIR;
        SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);

        if ($this->plugin_info['enable']) {
            // プラグイン用の公開ファイルを更新。
            $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/plugin/";
            $dest_dir = PLUGIN_HTML_REALDIR . "{$plugin_code}/";
            SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);
            
            // 管理者用の公開ファイルを更新。
            $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/admin/";
            $dest_dir = HTML_REALDIR . ADMIN_DIR;
            SC_Utils_Ex::copyDirectory($src_dir, $dest_dir);
            
            // 顧客用の公開ファイルを更新。
            $src_dir = PLUGIN_UPLOAD_REALDIR . "{$plugin_code}/public/customer/";
            $dest_dir = HTML_REALDIR;
            SC_Utils::copyDirectory($src_dir, $dest_dir);
        }
        
        // 設定を更新。
        $values = array();
        
        $settings = array(
            'use_holderjs' => true,
        );
        $values['free_field2'] = Zenith_Eccube_Utils::encodeJson($settings);
        
        $result = $this->query->update('dtb_plugin', $values, 'plugin_code = ?', array($plugin_code));
        if (PEAR::isError($result)) {
            throw new RuntimeException($result->toString());
        }
    }
}
