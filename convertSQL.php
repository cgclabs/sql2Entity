#!/usr/bin/php
<?php
/**
 * Author: Roger Creasy
 * Email: roger@rogercreasy.com
 * Date: 1/25/17
 * Time: 2:54 PM
 */

if (in_array('-h', $argv) || in_array('--help', $argv))
{
    ?>

    This is a command line PHP script that will create doctrine entity file(s) based on SQL.

      Usage:
      <?php echo $argv[0]; ?> <sql file> <output folder (optional)> <options>

      <options> can be -v for verbose mode. With the --help or -h options, you will get this help.

    <?php
}
else
{
    $verboseeMode = 0;
    $output = 'generatedEntities/'; 

    if (isset($argv[1]))
    {
        $file = $argv[1];
        if (isset($argv[2]) && $argv[2] != '-v')
        {
            $output = $argv[2];
        }

        if (in_array('-v', $argv))
        {
            $verboseMode = 1; 
        }

        include 'TabletoEntity.php';
        $converter = new TabletoEntity($file,$verboseMode,$output);
        $converter->generateEntity();
    }
    else
    {
        echo "\nNo SQL file specified\n";
    }
}
