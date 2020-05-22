<?php
include("excel_csv_parser.php");

$csvfile = fopen("example.csv", "r");

while(!feof($csvfile)){
    $csvline = fgets($csvfile);
    $csv[] = parse_csv_line($csvline);
}

echo "<pre>";
print_r($csv);
echo "</pre>";
?>
