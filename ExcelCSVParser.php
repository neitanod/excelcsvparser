<?php
/*
Example:

$csvfile = new ExcelCSVParser("file.csv");
while(!$csvfile->eof){
    $line = $csvfile->parseLine();
    $values[] = $line;
}
print_r($values);

   * May return:
   *
   * Array
   * (
   *     [0] => Array
   *         (
   *             [0] => ID
   *             [1] => Category
   *             [2] => Name
   *         )
   *     [1] => Array
   *         (
   *             [0] => 1300
   *             [1] => Consumer Electronics; Gadgets
   *             [2] => Portable "MP3" Player
   *         )
   * )

*/

class ExcelCSVParser {

    // defaults
    var $separator = ';';
    var $delimiter = '"';
    var $escape = '"';

    // auxiliar values
    protected $file;
    protected $enclosed;
    protected $eof = false;

    public function __construct($file){
        $this->open($file);
    }

    public function open($file){
        if(is_readable($file) && is_file($file)){
            $this->file = fopen($file, "r");
            $this->eof = feof($this->file);
        }
        return $this;
    }

    public function eof(){
        return $this->eof;
    }

    public function setSeparator($separator){
        $this->separator = $separator;
        return $this;
    }

    public function setDelimiter($delimiter){
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setEscape($escape){
        $this->escape = $escape;
        return $this;
    }

    public function parseLine() {
        if(empty($this->file)){ return false; }
        if(feof($this->file)){ $this->eof=true; return false; }
        $parts = [];
        $line = fgets($this->file);
        $res["remaining"] = $line;
        while(!empty($res["remaining"])){
            $res = $this->get_csv_value($res["remaining"]);
            $parts[] = $res["value"];
        }
        if(feof($this->file)){ $this->eof=true; }
        if(empty($parts)){ return false; }
        return $parts;
    }

    function get_csv_value($line, $already_enclosed = false){
        // auxiliar function for internal use
        // returns an array consisting of:
        //        "value" => <the_first_value_of_the_line>
        //        "remaining" => <the_rest_of_the_line>
        $enclosed = false;
        if($already_enclosed){
            $enclosed = true;
        } elseif(substr($line,0,1) == $this->delimiter){
            $line = substr($line,1);
            $enclosed = true;
        }
        $ready = false;
        $output = $this->str_ibefore($line, $this->separator);
        $line = substr($line, strlen($output));
        if(substr($line,0,1) == $this->separator){
            $line = substr($line,1);
        }
        if($enclosed){
            $output = str_replace($this->escape.$this->delimiter, "{[_delimiter_character_in_contents_]}", $output);
            $output = str_replace($this->escape.$this->escape, "{[_escape_character_in_contents_]}", $output);
            if(substr($output, -1) == $this->delimiter){
                $output = substr($output, 0, -1);
            } elseif(!empty($line)) {
                $res = $this->get_csv_value($line, true);
                $output .= $this->separator . $res["value"];
                $line = $res["remaining"];
            } else {
                // end of line reached, didn't find delimiter character.
                // must add next line to this one.
                if(!feof($this->file)){
                    $line = fgets($this->file);
                    $res = $this->get_csv_value($line, true);
                    $output .= $res["value"];
                    $line = $res["remaining"];
                } else {
                    $this->eof = true;
                }
            }
            $output = str_replace("{[_delimiter_character_in_contents_]}", $this->delimiter, $output);
            $output = str_replace("{[_escape_character_in_contents_]}", $this->escape, $output);
        }
        return array("value" => $output, "remaining" => $line);
    }

    function str_ibefore($haystack, $limit)
    {
        // auxiliar function for internal use
        // returns string from the begining of haystack
        // to the limit (not including it) or end
        return ($_pos = strpos(strtoupper($haystack),strtoupper($limit)))===false?
            $haystack:substr($haystack,0,$_pos);
    }
}  //end class
