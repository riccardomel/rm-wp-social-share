# RM Wp Social Share with counter

Wordpress Social Share Plugin - Clean, Fast, Simple. With Favorities integrations, and AJAX Support. Facebook Scrape block every 24h (useful to avoid Facebook API Alerts).

* Counter of shares for Facebook.
* Facebook, Pinterest, Whatsapp, Linkedin, Twitter, Print, Favorities
* Auto featured image and text to share message.
* Ajax support (if you have a caching system, works and tested with Varnish Proxy Server).
* Font awesome ready
* Facebook api limit friendly
* If enabled, works with: https://github.com/kylephillips/favorites
* Compatible with Autoptimize Plugin
* Works with any Caching Plugin.

## Installation and usage

* Upload the RM_ShareCounter inside your wp-content/plugins.
* Upload the RM_ajax_shareUpdater.php inside your  wp-content/themes/YOURTHEME.
* Activate the plugin
* Open Settings and modify for your use.
* Create and publish a page called "Rm Shareupdater" and connect the custom page template.
* Add this shortcode in your theme:
```bash
<?php if(shortcode_exists( 'share-counter' )) { echo do_shortcode("[share-counter]"); } ?>
```
* Enjoy

## Screenshots

![img](https://riccardomel.com/github/screenshots/rm-wp-social-share/rm-wp-social-share_3_1.png)
![img](https://riccardomel.com/github/screenshots/rm-wp-social-share/rm-wp-social-share_1.png)
![img](https://riccardomel.com/github/screenshots/rm-wp-social-share/rm-wp-social-share_2.png)

## Future enhancement

* google Events works for every button, now works only with print
* Print js code embed in the plugin (Please note: you can set up the print functions in your Theme js!)
* Icon bundle selections
* put postID var using  wp_localize_script

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## Contact
Riccardo Mel  
https://riccardomel.com  
info@riccardomel.com