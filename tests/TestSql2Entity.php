<?php

use CGCLabs\sql2Entity\sql2Entity,
    org\bovigo\vfs\vfsStream,
    org\bovigo\vfs\vfsStreamDirectory;

class sql2EntityTest extends PHPUnit_Framework_TestCase {
    /**
     ** @dataProvider providerTestCompleteSQL
     **/

    public function testCompleteSQL($input_file, $output_file)
    {
        $input = file_get_contents(realpath(dirname(__FILE__)) . '/' . $input_file);
        $output = file_get_contents(realpath(dirname(__FILE__)) . '/' . $output_file);
        $structure = [
            'to_read' => [
                'input.sql' => $input],
            'to_output' => []
        ];

        $root = vfsStream::setup('root',null,$structure);
        $this->assertTrue($root->hasChild('to_read/input.sql'));
        $sql2Entity = new sql2Entity($root->url() . '/to_read/input.sql',false,$root->url() . '/to_output/');
        $sql2Entity->generateEntity();

        $this->assertTrue($root->hasChild('to_output/TestTable123.php'));
        $this->assertEquals($root->getChild('to_output/TestTable123.php')->getContent(),$output);
    }

    public function providerTestCompleteSQL()
    {
        return array(
            array("test1_input.txt","test1_output.txt"),
        );
    }
    
    public function testHelp()
    {
        $output = `./convertSQL.php --help 2>&1`;
        $this->assertRegExp('/This is a command line PHP script that will create doctrine entity file/m', $output, 'no help message?' );
        $this->assertRegExp('/Usage:/m', $output, 'no help message?' );
    }
}
