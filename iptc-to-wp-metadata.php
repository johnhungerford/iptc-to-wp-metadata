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
    
    // Only try to update WP metadata if 'Keywords' IPTC key ('2#025') is present
    if( isset( $iptc['2#025'] ) && is_array( $iptc['2#025'] ) ) {
        // Generate string from array of keywords, space-separated
        $keywords_string = implode(' ', $iptc['2#025']);
        $new_image_meta = array(
            'ID'		    => $attachment_id,		// Specify the image (ID) to be updated
			'post_title'	=> $keywords_string,	// Set image Title to string of keywords
			'post_content'	=> $keywords_string,	// Set image Description (Content) to string of keywords
        );
        
        wp_update_post( $new_image_meta );
   }
}
