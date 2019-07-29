# IPTC to Wordpress Metadata

This is a Wordpress plugin that automatically populates the "Description" field of an image with IPTC keywords when the image is uploaded to the media library. It is an easy way to make your media library searchable by embedded keywords, which can either be IPTC or XMP (from what I can tell, these work in basically the same way, at least when it comes to keywords).

It works by using php's built-in `iptcparse` function to parse data pulled php's built-in `getimagesize` function. If that doesn't work (for instance, when IPTC keywords are stored in the XMP slot and EXIF data is also present, php cannot find the IPTC data) it simply searches the file for xml encoded keyword tags (xmp format).

This plugin currently supports .jpg and .tif files (although the latter are not typically used on wordpress) but not .png, which has little standardization for metadata embedding.

**Disclaimer: I am inexperienced with both Wordpress and PHP and cannot vouch for the security of this plugin.**

To install, run zipscript to generate a zip archive of the php file, and [upload the zip file to Wordpress](https://www.siteground.com/tutorials/wordpress/install-plugins/). Note: if your Wordpress site is hosted by Pantheon, you will only be able to add this on your "Dev Site" and will need to push the changes to your "Test Site" and then to your "Live Site."
