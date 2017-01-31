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

    public function __construct($sqlinput, $entityName)
    {
        $this->sqlinput = $sqlinput;
        $this->conversions = include 'conversionArray.php';
        $this->template = 'entityTemplate';
        $this->entityName = $entityName;
    }

    public function generateEntity()
    {
        $fp = fopen($this->sqlinput, 'r') or die("Unable to open file!");
        $file = fread($fp, filesize($this->sqlinput));

        $file = $this->doctrineTypeConversion($file);

        $this->arrayFromInput($file);
        fclose($fp);
        return $this->phpFile;
    }

    private function arrayFromInput($file)
    {
        $dataArray = explode(",\n", $file);

        foreach ($dataArray as $line) {

            //remove SQL comments
            $line = preg_replace("/(--.*)/", "", $line);

            // move each line into an associative array
            // ignore "for column" and the word that follows
            preg_match('/(\w+)\s+(for column \w+\s+)?([a-zA-Z]+)(\((.*?)\))?.*/is', $line, $saved);
            $this->fieldLine[]=$saved;

        }

        $this->generatePHP();
    }

    private function generatePHP()
    {
        $this->phpFile = 'class '.$this->entityName."\n".'{';
        foreach ($this->fieldLine as $line){
            $this->phpFile .= "\n".'    /**'."\n".'    * @ORM\Column(name="'.$line[1].'", type="'.$line[3].'"';
            if(array_key_exists(5, $line))
            {
                $line[5] = preg_replace("/(,.*)/", "", $line[5]);
                $this->phpFile .= ', length='.$line[5];
            }
            $this->phpFile .= ')'."\n";
            $this->phpFile .= '    */'."\n".'    protected $'. $line[1].";\n";
        }

        $fp = fopen($this->template, 'r') or die("Unable to open file!");
        $templateFile = fread($fp, filesize($this->template));
        $this->phpFile = str_replace('{{ types }}',$this->phpFile, $templateFile);
    }

    public function writeEntityFile($file, $entityName)
    {
        $path = 'generatedEntities/';
        $entityName = $path.$entityName.'.php';
        $entityFile = fopen($entityName, "w") or die("Unable to open file!");
        fwrite($entityFile, $file);
        fclose($entityFile);
        return true;
    }

    // Convert SQL type names to Doctrine types
    // Conversion translation matrix is in conversionArray.php
    private function doctrineTypeConversion($file)
    {
        $output = str_replace(array_keys($this->conversions), $this->conversions, $file);
        return $output;
    }
}
