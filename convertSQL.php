<?php
/**
 * Author: Roger Creasy
 * Email: roger@rogercreasy.com
 * Date: 1/25/17
 * Time: 2:54 PM
 */

include 'TabletoEntity.php';
print "File to convert:";
$file = trim(fgets(STDIN));

print "Verbose Mode (0 or 1 for now):";
$verboseMode = trim(fgets(STDIN));

$converter = new TabletoEntity($file,$verboseMode);

$converter->generateEntity();

//if($converter->writeEntityFile($output,$entityName))
//{
//    echo "SUCCESS!";
//}
//else echo "File Write Failed";
