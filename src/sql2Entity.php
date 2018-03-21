<?php
namespace CGCLabs\sql2Entity;

class sql2Entity
{
    protected $sqlinput;
    protected $phpFile;
    protected $template = 'entityTemplate';
    protected $entityName = '';
    protected $tableName;
    protected $tableSchema;
    protected $path;
    protected $fieldLine = array();
    protected $primaryKeys = array();


    protected $conversions = array(
        'varchar' => 'string',
        'char' => 'string',
        'date' => 'datetime',
        'numeric' => 'integer',
        'integer' => 'integer',
        'timestamp' => 'datetime',
        'decimal' => 'decimal',
        'text' => 'text'
    );

    public function __construct($sqlinput, $verboseMode, $path)
    {
        $this->sqlinput = $sqlinput;
        $this->verboseMode = $verboseMode;
        $this->path = $path;
    }

    public function generateEntity()
    {
        $fp = fopen($this->sqlinput, 'r') or die("Unable to open file!");
        $file = fread($fp, filesize($this->sqlinput));

        $this->checkForMultipleTables($file);
        fclose($fp);
        return $this->phpFile;
    }

    private function checkForMultipleTables($file)
    {
        $x = 0;
        preg_match_all("/(.*)\(((?>[^()]+)|(?R))*\)/", $file, $matches);
        if ($this->verboseMode) {
            echo "Found ".count($matches[0]) . " tables to process\n";
        }

        foreach ($matches[0] as $k => $table) {
            $x++;
            if ($this->verboseMode) {
                echo "Processing Table #".$x.":\n";
            }

            $this->findTableInfo($table);

            if ($this->verboseMode) {
                echo "\ttableName: " . $this->tableName . "\n";
                echo "\ttableSchema: " . $this->tableSchema . "\n";
                echo "\tentityName: " . $this->entityName . "\n";
            }
            $this->arrayFromInput($table);
        }
    }

    private function findTableInfo($table_sql)
    {
        preg_match_all("/(.*)^[^\(]*/", $table_sql, $name_matches);
        $the_name = strtoupper($name_matches[0][0]);
        $the_name = str_replace('CREATE TABLE', '', $the_name);
        $the_name = trim($the_name);
        $pieces = explode('.', $the_name);
        if (count($pieces) == 2) {
            $this->tableName = $pieces[1];
            $this->tableSchema = $pieces[0];
        } else {
            $this->tableName = $pieces[0];
        }

        $this->entityName = str_replace(array('_',' '), '', ucwords(strtolower($this->tableName), '_'));
    }

    private function cleanTable($table_sql)
    {
        // Remove line endings
        $table_sql = preg_replace("/\n|\r\n?/", "", $table_sql);
        // Remove Create table portion - up to first (
        $table_sql = preg_replace("/^[^\(]+/", "", $table_sql);
        // Remove first and last parenthesis
        $table_sql = trim($table_sql, "()");
        return $table_sql;
    }

    private function arrayFromInput($file)
    {
        $file = $this->cleanTable($file);

        if ($this->verboseMode) {
            echo "\t\tFull table sql: " . $file . "\n\n";
        }

        // split up columns
        $dataArray = preg_split("/,(?=[^)]*(?:[(]|$))/", $file);

        foreach ($dataArray as $line) {
            $this->processColumn($line);
        }

        $this->generatePHP();
        $this->writeEntityFile();
    }

    private function processColumn($line)
    {
        $line = trim($line);
        if ($this->verboseMode) {
            echo "\t\tProcessing Line: " . $line . "\n";
        }

        //remove SQL comments
        $line = preg_replace("/(--.*)/", "", $line);

        // move each line into an associative array
        // ignore "for column" and the word that follows
        preg_match('/(for column \w+\s+)/is', $line, $saved);
        if (isset($saved[0])) {
            $line = str_replace($saved[0], '', $line);
        }

        $line_good = false;
        $line_array = explode(' ', trim($line));

        $column_name = trim($line_array[0], '"');
        if (stripos($column_name, 'primary') !== false) {
            $second_word = trim($line_array[1], '"');
            if (stripos($second_word, 'key') !== false) {
                preg_match('/\((.*?)\)/', $line, $p_keys);
                $this->primaryKeys = explode(',', str_replace(' ', '', $p_keys[1]));
            }
        }

        foreach ($this->conversions as $real_type => $new_type) {
            $column_type_loc = stripos($line_array[1], $real_type);
            if ($column_type_loc !== false) {
                $line_good = true;
                $column_type = $new_type;
                // find column length
                preg_match("/\(([A-Za-z0-9 ,]+?)\)/", $line_array[1], $column_l);
                if (isset($column_l[1])) {
                    $column_length = $column_l[1];
                } else {
                    $column_length = '';
                }
                break;
            }
        }

        if ($line_good) {
            $this->fieldLine[] = array('name'=>$column_name, 'length'=>$column_length, 'type'=>$column_type);
        }
    }

    private function generatePHP()
    {
        $this->phpFile = '/**'."\n".'* @ORM\Entity'."\n".'* @ORM\Table(name="'.$this->tableSchema . '.' . $this->tableName.'")'."\n" . '*/' . "\n";
        $this->phpFile .= 'class '.$this->entityName."\n".'{';

        foreach ($this->fieldLine as $col_no => $column) {
            $this->phpFile .= "\n".'    /**'."\n";
            if (in_array($column['name'], $this->primaryKeys)) {
                $this->phpFile .= '    * @ORM\Id' . "\n";
            }
            $this->phpFile .= '    * @ORM\Column(name="'.$column['name'].'", type="'.$column['type'].'"';
            if (!empty($column['length'])) {
                $this->phpFile .= ', length='.$column['length'];
            }
            $this->phpFile .= ')'."\n";
            $this->phpFile .= '    */'."\n".'    private $'. str_replace('#', '', $column['name']) .";\n";
        }

        // Clear table
        $this->fieldLine = array();

        $fp = fopen($this->template, 'r') or die("Unable to open file!");
        $templateFile = fread($fp, filesize($this->template));
        $this->phpFile = str_replace('{{ types }}', $this->phpFile, $templateFile);
    }

    public function writeEntityFile()
    {
        $entityName = $this->path.$this->entityName.'.php';
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
