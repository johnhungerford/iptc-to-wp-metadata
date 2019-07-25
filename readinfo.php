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
    // Skip past the tag to get the keyword itself
    $key_begin = $ctr + 8;

    // Keyword ends where closing tag begins
    $key_end = strpos($key_data, '</rdf:li>', $key_begin);

    print_r(substr($key_data, $key_begin, $key_end - $key_begin));

    // Make sure keyword has a closing tag
    if ($key_end == FALSE) break;
    
    // Make sure keyword is not too long (not sure what WP can handle)
    $key_length = $key_end - $key_begin;
    $key_length = (100 < $key_length ? 100 : $key_length);

    // Add keyword to keyword array
    array_push($keys, substr($key_data, $key_begin, $key_length));

    // Find next keyword open tag
    $ctr = strpos($key_data, '<rdf:li>', $key_end);
}

print_r($keys);

// If above code generated an array of keywords ($keys), enter these into wordpress meta
if (isset($keys) && sizeof($keys) > 0) {
    $keywords_string = implode(', ', $keys);

    // Let's make sure title string is 150 characters or less, and ends with a complete keyword
    $title_string = (strlen($keywords_string) < 150 ? $keywords_string : substr($keywords_string, 0, strrpos(substr($keywords_string, 0, 150), ', ')));

    $new_image_meta = array(
        'ID'		    => $attachment_id,		// Specify the image (ID) to be updated
        'post_title'	=> $title_string,	    // Set image Title to string of keywords
        'post_content'	=> $keywords_string,	// Set image Description (Content) to string of keywords
    );

    print_r( $new_image_meta );
} 

?>