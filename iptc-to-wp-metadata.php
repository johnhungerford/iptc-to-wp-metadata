<?php
/**
 * Plugin Name: IPCT to Wordpress Metadata
 * Plugin URI: n/a
 * Description: A plugin to autopopulate the "Description" field for image media with the "Keywords" field from IPCT metadata
 * Version: 1.0
 * Author: John Hungerford
 * Author URI: n/a
 */

add_action( 'add_attachment', 'populate_img_desc_iptc_keywords');

function populate_img_desc_iptc_keywords($attachment_id) {
    // Get EXIF data from the attachment file
    $image = getimagesize( get_attached_file( $attachment_id ), $info );
    $iptc = iptcparse( $info['APP13'] );
    
    if( isset( $iptc['2#025'] ) && is_array( $iptc['2#025'] ) ) {

        // Last param is true to append these tags to existing tags,
        // set it to false to replace existing tags
        // See https://codex.wordpress.org/Function_Reference/wp_set_post_tags
        $my_image_title = implode(' ', $iptc['2#025']);
        $my_image_meta = array(
            'ID'		    => $attachment_id,			// Specify the image (ID) to be updated
			'post_title'	=> $my_image_title,		// Set image Title to sanitized title
			'post_content'	=> $my_image_title,		// Set image Description (Content) to sanitized title
        );
        
        wp_update_post( $my_image_meta );
   }
}
