<?php
/*
Copyright (c) 2006 Sebastián Grignoli
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. Neither the name of copyright holders nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * @author   "Sebastián Grignoli" <grignoli@gmail.com>
 * @package  ExcelCSVParser
 * @version  1.0
 * @link     https://github.com/neitanod/excelcsvparser
 * @license  Revised BSD
  */

/*
Example:

$csvfile = new \Neitanod\ExcelCSVParser\ExcelCSVParser("file.csv");
while(!$csvfile->eof()){
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

namespace Neitanod\ExcelCSVParser;

class ExcelCSVParser {

    // defaults
    var $separator = ';';
    var $delimiter = '"';
    var $escape = '"';

    // auxiliar values
    protected $file = null;
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

    public function reset() {
        if ( !is_null($this->file) ) {
            fseek($this->file, 0);
        }
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

    protected function get_csv_value($line, $already_enclosed = false){
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
            if(substr($output, -1) == $this->delimiter ||
               substr($output, -2) == $this->delimiter."\n"){
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

    protected function str_ibefore($haystack, $limit)
    {
        // auxiliar function for internal use
        // returns string from the begining of haystack
        // to the limit (not including it) or end
        return ($_pos = strpos(strtoupper($haystack),strtoupper($limit)))===false?
            $haystack:substr($haystack,0,$_pos);
    }
}  //end class
