#!/usr/bin/php
<?php
require __DIR__ . '/vendor/autoload.php';
use CGCLabs\sql2Entity\sql2Entity;

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

        $converter = new sql2Entity($file,$verboseMode,$output);
        $converter->generateEntity();
    }
    else
    {
        echo "\nNo SQL file specified\n";
    }
}
