<?php

/**
 * マイグレーションを行います。
 * 
 * @author Seiji Nitta
 */
abstract class Zenith_Eccube_Migration {
    /**
     * @var array
     */
    protected $plugin_info;
    
    /**
     * @var SC_Query_Ex
     */
    protected $query;

    /**
     * @param SC_Query_Ex $query
     */
    public function __construct(array $plugin_info, SC_Query_Ex $query) {
        $this->plugin_info = $plugin_info;
        $this->query = $query;
    }

    /**
     * マイグレーションを適用します。
     */
    public function up() {
    }

    /**
     * マイグレーションを適用を取り下げます。
     */
    public function down() {
    }
}
