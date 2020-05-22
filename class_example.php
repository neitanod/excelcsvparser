<?php
include("ExcelCSVParser.php");

$csv = new ExcelCSVParser("class_example.csv");

$csv->setSeparator(',');

while(!$csv->eof()){
    $line = $csv->parseLine();
    if( $line !== false ) {
        $values[] = $line;
    }
}

echo "<pre>";
print_r($values);
echo "</pre>";
?>
