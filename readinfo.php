<?php

/**
 *  This is a test script to run on the command to explore the metadata content of
 *  image files.
 */

$filename = './tiftest.tif';
$size = getimagesize($filename, $info);

// Output exif OR xmp
if(isset($info['APP1'])) print_r($info['APP1']);

// Output iptc
if(isset($info['APP13'])) print_r(iptcparse($info['APP13']));

// Search for xmp
$content = file_get_contents($filename);
$xmp_data_start = strpos($content, '<dc:subject>') + 12;
fwrite(STDOUT, '\nFIRST KEYWORD XMP TAG\n');
print_r($xmp_data_start - 12);
$xmp_data_end   = strpos($content, '</dc:subject>');

$xmp_data_length     = $xmp_data_end - $xmp_data_start;
$xmp_data       = substr($content, $xmp_data_start, $xmp_data_length);

print_r($xmp_data);

$key_data_start = strpos($xmp_data, '<rdf:Seq>') + 9;
$key_data_end   = strpos($xmp_data, '</rdf:Seq>');
$key_data_length     = $key_data_end - $key_data_start;
$key_data       = substr($xmp_data, $key_data_start, $key_data_length);

print_r($key_data);

$last_tag = strrpos($content, '<dc:subject>');
fwrite(STDOUT, '\nLAST KEYWORD XMP TAG\n');
print_r($last_tag);

$ctr = strpos($key_data, '<rdf:li>');

$keys = Array();

while($ctr != FALSE && $ctr < $key_data_length) {
    $ctr += 8;
    $endpos = strpos($key_data, '</rdf:li>', $ctr);
    if ($endpos == FALSE) break;

    print_r(substr($key_data, $ctr, $endpos - $ctr));

    array_push($keys, substr($key_data, $ctr, $endpos - $ctr));

    $ctr = strpos($key_data, '<rdf:li>', $endpos);
}

print_r($keys);

?>