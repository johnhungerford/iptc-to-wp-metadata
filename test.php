<?php
$filename = './fail3.jpg';
$size = getimagesize($filename, $info);
print_r($info);
print_r(exif_read_data($filename));

if(isset($info['APP13']))
{
    $iptc = iptcparse($info['APP13']);
    fwrite(STDOUT, var_dump($iptc));
} else {
    fwrite(STDOUT, "no iptc info found...");
    $content = file_get_contents($filename);
    $xmp_data_start = strpos($content, '<dc:subject>') + 12;
    $xmp_data_end   = strpos($content, '</dc:subject>');
    $xmp_data_length     = $xmp_data_end - $xmp_data_start;
    $xmp_data       = substr($content, $xmp_data_start, $xmp_data_length);
    $key_data_start = strpos($xmp_data, '<rdf:Seq>') + 9;
    $key_data_end   = strpos($xmp_data, '</rdf:Seq>');
    $key_data_length     = $key_data_end - $key_data_start;
    $key_data       = substr($xmp_data, $key_data_start, $key_data_length);

    $ctr = strpos($key_data, '<rdf:li>');

    $keys = Array();

    while($ctr != FALSE && $ctr < $key_data_length) {
        $ctr += 8;
        $endpos = strpos($key_data, '</rdf:li>', $ctr);
        if ($endpos == FALSE) break;

        array_push($keys, substr($key_data, $ctr, $endpos - $ctr));

        $ctr = $endpos + 10;
    }

    print_r($keys);
}
?>