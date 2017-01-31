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

print "Entity name:";
$entityName = trim(fgets(STDIN));

$converter = new TabletoEntity($file,$entityName);

$output = $converter->generateEntity();

if($converter->writeEntityFile($output,$entityName))
{
    echo "SUCCESS!";
}
else echo "File Write Failed";
