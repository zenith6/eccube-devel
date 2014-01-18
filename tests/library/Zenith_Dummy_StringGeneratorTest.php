<?php

class Zenith_Dummy_StringGeneratorTest extends PHPUnit_Framework_TestCase {
    public function testGenerate() {
        $options = array(
            'min_length' => 1,
            'max_length' => 1,
            'characters' => 'A',
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertEquals('A', $generator->generate());

        $options = array(
            'min_length' => 1,
            'max_length' => 1,
            'characters' => 'ABCDE',
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[A-E]\\z/u', $generator->generate());

        $options = array(
            'min_length' => 1,
            'max_length' => 1,
            'characters' => array(array(0x41, 0x45)), // 'A' to 'E'
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[A-E]\\z/u', $generator->generate());
        
        $options = array(
            'min_length' => 1,
            'max_length' => 10,
            'characters' => implode('', array_merge(range('0', '9'), range('A', 'Z'), range('a', 'z'))),
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[0-9A-Za-z]{1,10}\\z/u', $generator->generate());

        $options = array(
            'min_length' => 10,
            'max_length' => 10,
            'characters' => implode('', array_merge(range('0', '9'), range('A', 'Z'), range('a', 'z'))),
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[0-9A-Za-z]{10}\\z/u', $generator->generate());

        $options = array(
            'min_length' => 1,
            'max_length' => 1,
            'characters' => 'あいうえお',
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[あ-お]\\z/u', $generator->generate());

        $options = array(
            'min_length' => 10,
            'max_length' => 10,
            'characters' => 'あいうえお',
        );
        $generator = new Zenith_Dummy_StringGenerator($options);
        $this->assertRegExp('/\\A[あ-お]{10}\\z/u', $generator->generate());
    }
}
