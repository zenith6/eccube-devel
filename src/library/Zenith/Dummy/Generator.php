<?php

abstract class Zenith_Dummy_Generator {
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @param array $options
     * @param SC_Query_Ex $query
     */
    public function __construct($options = array()) {
        $this->options = $options + $this->getDefaultOptions();
        
        $this->resetCache();
    }
    
    /**
     * @return array()
     */
    public function getDefaultOptions() {
        return array();
    }
    
    protected function resetCache() {
    }
    
    /**
     * @return mixed
     */
    abstract public function generate();
}
