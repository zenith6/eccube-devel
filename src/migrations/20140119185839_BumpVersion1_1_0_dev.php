<?php

class plg_Devel_Migrations_BumpVersion1_1_0_dev extends Zenith_Eccube_Migration {
    public function up() {
        $plugin_code = $this->plugin_info['plugin_code'];
        
        if (version_compare(ECCUBE_VERSION, '2.13') >= 0) {
            return;
        }

        $values = $where_values = array();
        
        $values['plugin_version'] = '1.1.0-dev';
        
        $where_values[] = $plugin_code;
        
        $result = $this->query->update('dtb_plugin', $values, 'plugin_code = ?', $where_values);
        if (PEAR::isError($result)) {
            throw new RuntimeException($result->toString());
        }
    }
}
