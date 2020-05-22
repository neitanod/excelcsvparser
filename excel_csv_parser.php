<?php

function parse_csv_line($line, $separator = ';', $delimiter = '"', $escape = '"'){
  /*************************
   * Parses a line from a CSV file and returns an array of it's fields.
   *
   * Example:
   * 
   * $lines = file("products.csv");
   * foreach($lines as $line){
   *   $values[] = parse_csv_line($line);
   * }
   * print_r($values);
   *
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
   * 
   *************************/ 
  $res["remaining"] = $line;
  while(!empty($res["remaining"])){
    $res = get_csv_value($res["remaining"],";",'"','"');
    $parts[] = $res["value"];
  }
  return $parts;
}

function get_csv_value($line, $separator, $delimiter, $escape, $already_enclosed = false){
  // auxiliar function for internal use
  // returns an array consisting of:
  // array(
  //        "value" => <the_first_value_of_the_line>
  //        "remaining" => <the_rest_of_the_line>
  // )
  if($already_enclosed){
    $enclosed = true;
  } elseif(substr($line,0,1) == $delimiter){
    $line = substr($line,1);
    $enclosed = true;  
  }
  $ready = false;
  $output = svn_str_ibefore($line, $separator);
  $line = substr($line, strlen($output));
  if(substr($line,0,1) == $separator){
    $line = substr($line,1);
  }
  if($enclosed){
    $output = str_replace($escape.$delimiter, "_delimiter_character_in_contents_", $output);
    if(substr($output, -1) == $delimiter){
      $output = substr($output, 0, -1);
    } elseif(!empty($line)) {
      $res = get_csv_value($line, $separator, $delimiter, $escape, true);
      $output .= $separator . $res["value"];
      $line = $res["remaining"];
    }
    $output = str_replace("_delimiter_character_in_contents_", $delimiter, $output);
  }
  return array("value" => $output, "remaining" => $line);
}

function svn_str_ibefore($haystack, $limit) 
{
  // auxiliar function for internal use
  // returns string from the begining of haystack 
  // to the limit (not including it) or end
  return ($_pos = strpos(strtoupper($haystack),strtoupper($limit)))===false?
          $haystack:substr($haystack,0,$_pos);
}
?>