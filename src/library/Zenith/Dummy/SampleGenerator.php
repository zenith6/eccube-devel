<?php

class Zenith_Dummy_SampleGenerator extends Zenith_Dummy_Generator {
    public $samples;
    
    public function __construct(array $samples = array(), array $options = array()) {
        parent::__construct($options);
        
        $this->samples = $samples;
        $this->resetCache();
    }
    
    public function getDefaultOptions() {
        return array(
            'max_number' => 1,
            'min_number' => 1,
            'scalar' => false,
            'duplication' => false,
        );
    }
    
    public function generate(array $options = array()) {
        $options += $this->options;
        $samples = array();
        $data = $this->samples;
        $num = mt_rand($options['min_number'], $options['max_number']);
        if ($this->samples) {
            for ($i = 0; $i < $num; $i++) {
                $index = mt_rand(0, count($data) - 1);
                $samples[] = $data[$index];
                if (!$options['duplication']) {
                    array_splice($data, $index, 1);
                }
            }
        }
        
        return $options['scalar'] ? ($samples ? $samples[0] : null) : $samples;
    }
}
