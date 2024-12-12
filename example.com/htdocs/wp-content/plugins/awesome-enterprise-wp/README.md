<p align="center">
	<a href="https://www.wpoets.com/" target="_blank"><img width="200"src="https://www.wpoets.com/wp-content/uploads/2018/05/WPoets-logo-1.svg" alt='Your Web and WordPress Experts'></a>
</p>

# Awesome Enterprise (WP)

Awesome Enterprise is a shortcode based low code platform that comes with useful services and apps that enable us to create easily custom Workflows in WordPress. This is the WordPress plugin to enable code editing for Awesome Enterprise Platform and enable low code flow within the WordPress framework.

To use this plugin, you will also need to install the Awesome Enterprise Framework, which you can do using

`composer create-project wpoets/awesome-enterprise`

You will need to update the wp-config.php file with various defines specified in sample-config.php in the samples-index folder.


### Changelog 

##### 3.1.9

* Fixed: When "dev" role was being assigned to a user, then it was resulting in 500 server error.

  
##### 3.1.8

* Fixed: wp.get sidebar.<id> was not working properly.
* Fixed: error message " trim(): Passing null to parameter #1 ($string) of type string is deprecated"

##### 3.1.7

* Fixed: the error "Undefined index: extension"
* Fixed: the issue, where the sidebar was not getting called properly.
* Fixed: the  issue: undefined variable $o
* Fixed: app_seo was making too many requests to the settings module, and if we have a large number of apps, then it becomes significant time, so we corrected the approach.

##### 3.1.6
* Fixed: fixed an issue where pages were getting overwritten unintentionally, and code editor was showing.

##### 3.1.5
* Improved: made awesome core and awesome app CPTs to be dynamic based on values defined in AWESOME_CORE_POST_TYPE, AWESOME_APPS_POST_TYPE, which can be defined in wp-config.php 

##### 3.1.4
* Improved: Added ability to import the html tar.gz package from the export screen.
* Improved: Arranged the screen showing the services list by the connections.
* Fixed: sitemap_index.xml file was showing the wrong timestamp for app site map files.

##### 3.1.3
* Improved: Added ability to show preview images for Awesome Gutenberg blocks
* Improved: Added new category "Awesome UI Blocks" in the list of Gutenberg blocks
* Fixed: awesome_core was hard-coded in the export module, we softcoded it 

##### 3.1.2
* Improved: Added support for using wp user as the virtual user

##### 3.1.1
* Improved: Added ability to import GT Blocks from awxdocs.com site, to do that goto admin menu **__tools->import awesome gt blocks__**

##### 3.1.0
* Improved: In connectors to enable service execution from the folder, external DB and CDN.
* Improved: Added ability to export apps code as HTML
* Improved: Added wp-cli command to import codes exported via 'Export As HTML' using command `wp awesome-import import_html --code-path='/var/import-code/20211013-105631' --overwrite=false`
* Improved: updated the awesome enterprise screen to show Redis DB used by various connections
* Fixed: A notice while registering post types using post_types shortcode
* Fixed: Date issue caused sitemap.xml file giving errors in goolge console for yoast
* Fixed: Module list for Apps was not visible in case of external Apps.

##### 3.0.8
* Improved: Awesome Enterprise screen now shows the active Redis db number.
* Improved: You can clean any Redis DB if you have the number.
* Improved: Now "less-variables" module is used to register less variables.
* Fixed: Replaced the deprecated function 'wp_make_content_images_responsive' with wp_filter_content_tags, minimum wp version 5.5 required.
* Improved: SVG uploads are now allowed by default with Awesome.
* Fixed: Removed the 'upload without refresh' button from non Awesome Post Types
* Improved: Added support for 'wp.image_resize' handler.

##### 3.0.7 
* Fixed: More PHP Notices & Warnings
* Fixed: _edit_last meta key was not updating.
 
##### 3.0.6 
* Fixed: PHP Notices & Warnings

##### 3.0.5 
* Fixed: Required tables were not auto-creating.
* Fixed: PHP Notices & Warnings

##### 3.0.4  
* Improved: Added support for better handling of errors & exceptions
* Improved: Required tables are auto-created when the plugin is activated.

##### 3.0.3  
* Improved: Added ability to export HTML modules for services to expose as an external package.
* Improved: Resturecuted less variable registration so that env.dump is cleaner
* New: Added wp.get for accessing WordPress properties
* Fixed: The export was referencing the wrong path for JS

##### 3.0.2  
* Improved: Added support for Rank Math SEO plugin for app site maps. 

##### 3.0.1  
* Fixed the path for util.php 

##### 3.0.0  
* Initial release

## We're Hiring!

<p align="center">
<a href="https://www.wpoets.com/careers/"><img src="https://www.wpoets.com/wp-content/uploads/2020/11/work-with-us_1776x312.png" alt="Join us at WPoets, We specialize in designing, building and maintaining complex enterprise websites and portals in WordPress."></a>
</p>
