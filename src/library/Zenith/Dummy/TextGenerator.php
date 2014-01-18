<?php

class Zenith_Dummy_TextGenerator extends Zenith_Dummy_Generator {
    public function getDefaultOptions() {
        return array(
            'min_length' => 100,
            'max_length' => 100,
            'min_word_length' => 1,
            'max_word_length' => 12,
            'min_statement_length' => 10,
            'max_statement_length' => 120,
            'characters' => Zenith_Dummy_StringGenerator::ALPHABET_LOWERCASE,
            'period' => '.',
            'comma' => ',',
            'comma_rate' => 0.03,
            'word_delimiter' => ' ',
            'statement_delimiter' => ' ',
            'capitalize' => true,
            'multiple' => true,
        );
    }
    
    /**
     * @var Zenith_Dummy_StringGenerator
     */
    private $wordGenerator;
    
    protected function resetCache() {
        $options = array(
            'min_length' => $this->options['min_word_length'],
            'max_length' => $this->options['max_word_length'],
            'characters' => $this->options['characters'],
        );
        $this->wordGenerator = new Zenith_Dummy_StringGenerator($options);
    }
    
    /**
     * @see Zenith_Dummy_Generator::generate()
     * @todo もっと lorem ipsum ぽくしたい
     */
    public function generate(array $options = array()) {
        $options = $options + $this->options;
        
        $para = '';
        $para_len = mt_rand($options['min_length'], $options['max_length']);
        $stmt_len = mt_rand($options['min_statement_length'], $options['max_statement_length']);
        $last_stmt_pos = 0; $first_word = true;
        while (($cur_para_len = mb_strlen($para, 'UTF-8')) < $para_len) {
            $word = $this->wordGenerator->generate(array('capitalize' => $options['capitalize'] && $first_word));
            $first_word = false;
            
            $para .= $word;
            
            if ($options['multiple']) {
                if ($cur_para_len - $last_stmt_pos > $stmt_len) {
                    $para .= $options['period'] . $options['statement_delimiter'];
                    $last_stmt_pos = mb_strlen($para, 'UTF-8');
                    $first_word = true;
                    continue;
                }
                
                if (mt_rand() / mt_getrandmax() < $options['comma_rate']) {
                    $para .= $options['comma'];
                }
            }
            
            $para .= $options['word_delimiter'];
        }
        
        $para = mb_substr($para, 0, $para_len, 'UTF-8');
        return $para;
    }
}
