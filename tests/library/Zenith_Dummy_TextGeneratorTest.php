<?php

class Zenith_Dummy_TextGeneratorTest extends PHPUnit_Framework_TestCase {
    public function testGenerate() {
        $options = array(
            'min_length'      => 1,
            'max_length'      => 1,
            'min_word_length' => 1,
            'max_word_length' => 1,
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[A-Za-z,.]\\z/u', $generator->generate());

        $options = array(
            'min_length'      => 1,
            'max_length'      => 1,
            'min_word_length' => 1,
            'max_word_length' => 1,
            'characters'      => 'ABCDE',
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[A-E,.]\\z/u', $generator->generate());
        
        $options = array(
            'min_length' => 1,
            'max_length' => 10,
            'characters' => implode('', array_merge(range('A', 'Z'), range('a', 'z'))),
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[A-Za-z  ,.]{1,10}\\z/u', $generator->generate());

        $options = array(
            'min_length' => 10,
            'max_length' => 10,
            'characters' => implode('', array_merge(range('A', 'Z'), range('a', 'z'))),
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[A-Za-z ,.]{10}\\z/u', $generator->generate());

        $options = array(
            'min_length' => 1,
            'max_length' => 1,
            'characters' => 'あいうえお',
            'word_delimiter' => '',
            'statement_delimiter' => '',
            'comma' => '、',
            'period' => '。',
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[あ-お、。]\\z/u', $generator->generate());

        $options = array(
            'min_length' => 100,
            'max_length' => 100,
            'characters' => 'あいうえお',
            'word_delimiter' => '',
            'statement_delimiter' => '',
            'comma' => '、',
            'period' => '。',
        );
        $generator = new Zenith_Dummy_TextGenerator($options);
        $this->assertRegExp('/\\A[あ-お、。]{100}\\z/u', $generator->generate());
    }
}
