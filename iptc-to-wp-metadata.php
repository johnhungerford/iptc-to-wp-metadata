<?php
/**
 * Plugin Name: IPTC to Wordpress Metadata
 * Plugin URI: https://github.com/johnhungerford/iptc-to-wp-metadata
 * Description: A Wordpress plugin to autopopulate the "Description" and "Title" fields for image media with the "Keywords" field from IPTC metadata
 * Version: 1.0
 * Author: John Hungerford
 * Author URI: https://github.com/johnhungerford
 */

// Bind plugin callback (below) to WP's 'add_attachment' hook, which fires when image is uploaded
add_action( 'add_attachment', 'populate_img_desc_iptc_keywords');

// This callback is the only action of this plugin
function populate_img_desc_iptc_keywords($attachment_id) {
    // Get IPTC data from the attachment file
    $image = getimagesize( get_attached_file( $attachment_id ), $info );
    $iptc = iptcparse( $info['APP13'] );
    
    // Use iptcparse data only if 'Keywords' IPTC key ('2#025') is present
    if( isset( $iptc['2#025'] ) && is_array( $iptc['2#025'] ) ) {
        // Generate string from array of keywords, space-separated
        $keys = $iptc['2#025'];

    // In case there is no appropriate data in 'APP13' slot of jpg embedded data
    } else {
        $content = file_get_contents(get_attached_file($attachment_id));

        // Look for xmp data: xml tag "dc:subject" is where keywords are stored
        $xmp_data_start = strpos($content, '<dc:subject>') + 12;

        // Only proceed if able to find dc:subject tag
        if ($xmp_data_start != FALSE) {
            $xmp_data_end   = strpos($content, '</dc:subject>');
            $xmp_data_length     = $xmp_data_end - $xmp_data_start;
            $xmp_data       = substr($content, $xmp_data_start, $xmp_data_length);

            // Look for tag "rdf:Seq" where individual keywords are listed
            $key_data_start = strpos($xmp_data, '<rdf:Seq>') + 9;

            // Only proceed if able to find rdf:Seq tag
            if ($key_data_start != FALSE) {
                $key_data_end   = strpos($xmp_data, '</rdf:Seq>');
                $key_data_length     = $key_data_end - $key_data_start;
                $key_data       = substr($xmp_data, $key_data_start, $key_data_length);

                // Find first keyword xml tag "rdf:li"
                $ctr = strpos($key_data, '<rdf:li>');

                // Initialize empty array to store keywords
                $keys = Array();

                // While loop stores each keyword and searches for next xml keyword tag
                while($ctr != FALSE && $ctr < $key_data_length) {
                    // Skip past the tag to get the keyword itself
                    $key_begin = $ctr + 8;

                    // Keyword ends where closing tag begins
                    $key_end = strpos($key_data, '</rdf:li>', $key_begin);

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
            }
        } 
    }

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

        wp_update_post($new_image_meta);
    } 
}
