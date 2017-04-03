<?php

/**
 * Author: Roger Creasy
 * Email: roger@rogercreasy.com
 * Date: 1/25/17
 * Time: 2:46 PM
 */


class TabletoEntity
{
    protected $sqlinput;
    protected $fieldLine;
    protected $phpFile;
    protected $conversions;
    protected $template;
    protected $entityName;
    protected $tableName;
    protected $tableSchema;

    public function __construct($sqlinput, $verboseMode)
    {
        $this->sqlinput = $sqlinput;
        $this->conversions = include 'conversionArray.php';
        $this->template = 'entityTemplate';
        $this->entityName = '';
        $this->verboseMode = $verboseMode;

    }

    public function generateEntity()
    {
        $fp = fopen($this->sqlinput, 'r') or die("Unable to open file!");
        $file = fread($fp, filesize($this->sqlinput));

        //$file = $this->doctrineTypeConversion($file);

        $this->checkForMultipleTables($file);
        fclose($fp);
        return $this->phpFile;
    }

    private function checkForMultipleTables($file)
    {
        $x = 0;
        preg_match_all("/(.*)\(((?>[^()]+)|(?R))*\)/", $file, $matches);
        if ($this->verboseMode)
        {
            echo "Found ".count($matches[0]) . " tables to process\n";
        }

        foreach ($matches[0] as $k => $table)
        {   
            $x++;
            if ($this->verboseMode)
            {
                echo "Processing Table #".$x.":\n";
            }
            preg_match_all("/(.*)^[^\(]*/",$table,$name_matches);
            $the_name = strtoupper($name_matches[0][0]);
            $the_name = str_replace('CREATE TABLE','',$the_name);
            $the_name = trim($the_name);
            $pieces = explode('.',$the_name);
            if (count($pieces) == 2)
            {
                $this->tableName = $pieces[1];
                $this->tableSchema = $pieces[0];
            }
            else
            {
                $this->tableName = $pieces[0];
            }

            $this->entityName = str_replace(array('_',' '), '', ucwords(strtolower($this->tableName), '_'));
            if ($this->verboseMode)
            {
                echo "\ttableName: " . $this->tableName . "\n";
                echo "\ttableSchema: " . $this->tableSchema . "\n";
                echo "\tentityName: " . $this->entityName . "\n";
            }
            $this->fieldLine = array();
            $this->arrayFromInput($table);
        }
    }

    private function arrayFromInput($file)
    {
        //$file = str_replace("\n\r", "\n", $file);
        //$dataArray = explode(",\n", $file);
        // Remove line endings
        $file = preg_replace("/\n|\r\n?/", "", $file);
        // Remove Create table portion - up to first (
        $file = preg_replace("/^[^\(]+/", "", $file);
        // Remove first and last parenthesis
        $file = trim($file, "()");

        if ($this->verboseMode)
        {
            echo "\t\tFull table sql: " . $file . "\n\n";
        }
        $dataArray = preg_split("/,(?=[^)]*(?:[(]|$))/", $file);

        foreach ($dataArray as $line) {
            $line = trim($line);
            if ($this->verboseMode)
            {
                echo "\t\tProcessing Line: " . $line . "\n";
            }

            //remove SQL comments
            $line = preg_replace("/(--.*)/", "", $line);

            // move each line into an associative array
            // ignore "for column" and the word that follows
            preg_match('/(for column \w+\s+)/is', $line, $saved);
            if (isset($saved[0]))
            {
                $line = str_replace($saved[0], '', $line);
            }

            $line_good = false;
            $line_array = explode(' ',trim($line));

            $column_name = trim($line_array[0],'"');


            foreach ($this->conversions as $real_type => $new_type)
            {
                $column_type_loc = stripos($line_array[1], $real_type);
                if ($column_type_loc !== false)
                {
                    $line_good = true;
                    $column_type = $new_type;
                    // find column length
                    preg_match("/\(([A-Za-z0-9 ,]+?)\)/", $line_array[1], $column_l);
                    if (isset($column_l[1]))
                    {
                        $column_length = $column_l[1];
                    }
                    else
                    {
                        $column_length = '';
                    }
                    break;
                }
            }

            if ($line_good)
            {
                $this->fieldLine[] = array('name'=>$column_name, 'length'=>$column_length, 'type'=>$column_type);
            }
        }

        $this->generatePHP();
        $this->writeEntityFile();
    }

    private function generatePHP()
    {
        $this->phpFile = '/**'."\n".'* @ORM\Entity'."\n".'* @ORM\Table(name="'.$this->tableSchema . '.' . $this->tableName.'")'."\n";
        $this->phpFile .= 'class '.$this->entityName."\n".'{';

        foreach ($this->fieldLine as $col_no => $column){
            $this->phpFile .= "\n".'    /**'."\n".'    * @ORM\Column(name="'.$column['name'].'", type="'.$column['type'].'"';
            if(!empty($column['length']))
            {
                $this->phpFile .= ', length='.$column['length'];
            }
            $this->phpFile .= ')'."\n";
            $this->phpFile .= '    */'."\n".'    protected $'. $column['name'].";\n";
        }

        $fp = fopen($this->template, 'r') or die("Unable to open file!");
        $templateFile = fread($fp, filesize($this->template));
        $this->phpFile = str_replace('{{ types }}',$this->phpFile, $templateFile);

    }

    public function writeEntityFile()
    {
        $path = 'generatedEntities/';
        $entityName = $path.$this->entityName.'.php';
        $entityFile = fopen($entityName, "w") or die("Unable to open file!");
        fwrite($entityFile, $this->phpFile);
        fclose($entityFile);
        echo "\tWriting entity file: " . $entityName . "\n";
    }

    // Convert SQL type names to Doctrine types
    // Conversion translation matrix is in conversionArray.php
    private function doctrineTypeConversion($file)
    {
        $output = str_replace(array_keys($this->conversions), $this->conversions, $file);
        return $output;
    }
}
