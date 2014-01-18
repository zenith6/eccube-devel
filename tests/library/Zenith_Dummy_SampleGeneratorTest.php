<?php

class Zenith_Dummy_SampleGeneratorTest extends PHPUnit_Framework_TestCase {
    public function testEmptySamples() {
        $samples = array();
        $generator = new Zenith_Dummy_SampleGenerator($samples);
        $this->assertEquals($samples, $generator->generate());
    }
    
    public function testGenerateArray() {
        $samples = array(1);
        $generator = new Zenith_Dummy_SampleGenerator($samples);
        $this->assertEquals($samples, $generator->generate());

        $samples = range(1, 5);
        $generator = new Zenith_Dummy_SampleGenerator($samples);
        $this->assertNotEmpty(array_intersect($samples, $generator->generate()));
    }
    
    public function testGenerateScalar() {
        $samples = array(1);
        $options = array('scalar' => true);
        $generator = new Zenith_Dummy_SampleGenerator($samples, $options);
        $this->assertInternalType('integer', $generator->generate());
        
        $samples = array(1);
        $options = array('scalar' => true);
        $generator = new Zenith_Dummy_SampleGenerator($samples, $options);
        $this->assertContains($generator->generate(), $samples);
        
        $samples = range(1, 5);
        $options = array('scalar' => true);
        $generator = new Zenith_Dummy_SampleGenerator($samples, $options);
        $this->assertInternalType('integer', $generator->generate());
        
        $samples = range(1, 5);
        $options = array('scalar' => true);
        $generator = new Zenith_Dummy_SampleGenerator($samples, $options);
        $this->assertContains($generator->generate(), $samples);
    }
}
