<?php

use CGCLabs\sql2Entity\sql2Entity,
    org\bovigo\vfs\vfsStream,
    org\bovigo\vfs\vfsStreamDirectory;

class sql2EntityTest extends PHPUnit_Framework_TestCase {
    
    public function testBlah()
    {
        $this->assertTrue(True);
    }

    public function testAll()
    {
        $structure = [
            'to_read' => [
                'input.sql' => "Extra SQLcode: GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE
                        ON Test OPTION ;
                        
                        CREATE TABLE CGCLABS.TEST_TABLE_123 (
                            GRP_ID FOR COLUMN UGGRPID    NUMERIC(5, 0) GENERATED ALWAYS AS IDENTITY (
                            START WITH 1 INCREMENT BY 1
                            NO MINVALUE NO MAXVALUE
                            NO CYCLE NO ORDER
                            CACHE 20 )
                                ,
                            GROUP_NAME FOR COLUMN UGGRPNAME  CHAR(75) CCSID 37 NOT NULL DEFAULT '' ,
                            GROUP_DESCRIPTION FOR COLUMN UGGRPDESC  VARCHAR(255) CCSID 37 NOT NULL DEFAULT '' )

                        RCDFMT F123_U00001 ;

                        GRANT ALTER , DELETE , INDEX , INSERT , REFERENCES , SELECT , UPDATE
                        ON teststststtsts WITH GRANT OPTION ;  "],
            'to_output' => []
        ];

        $root = vfsStream::setup('root',null,$structure);
        $this->assertTrue($root->hasChild('to_read/input.sql'));
        $sql2Entity = new sql2Entity($root->url() . '/to_read/input.sql',false,$root->url() . '/to_output/');
        $sql2Entity->generateEntity();

        $this->assertTrue($root->hasChild('to_output/TestTable123.php'));
    }
}
