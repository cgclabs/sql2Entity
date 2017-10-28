<?php

use CGCLabs\sql2Entity\Sql2Entity;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class Sql2EntityTest extends PHPUnit_Framework_TestCase
{
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

        $root = vfsStream::setup('root', null, $structure);
        $this->assertTrue($root->hasChild('to_read/input.sql'));
        
        $sql2Entity = new Sql2Entity($root->url() . '/to_read/input.sql', true, $root->url() . '/to_output/');
        $sql2Entity->generateEntity();

        $this->assertTrue($root->hasChild('to_output/TestTable123.php'));
        $this->assertEquals($root->getChild('to_output/TestTable123.php')->getContent(), $output);
        $this->expectOutputRegex('/Found 1 tables to process/m');
        $this->expectOutputRegex('/tableSchema: CGCLABS/m');
        $this->expectOutputRegex('/entityName: TestTable123/m');
        $this->expectOutputRegex('/tableName: TEST_TABLE_123/m');
    }

    public static function providerTestCompleteSQL()
    {
        return array(
            array("test1_input.txt","test1_output.txt"),
        );
    }

    public function testHelp()
    {
        $output = `./convertSQL.php --help 2>&1`;
        $this->assertRegExp('/This is a command line PHP script that will create doctrine entity file/m', $output, 'no help message?');
        $this->assertRegExp('/Usage:/m', $output, 'no help message?');
    }
}
