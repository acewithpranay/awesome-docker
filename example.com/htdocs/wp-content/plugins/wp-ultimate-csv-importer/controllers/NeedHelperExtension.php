<?php

/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if (! defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Class NeedHelperExtension
 * @package Smackcoders\FCSV
 */
class NeedHelperExtension
{

    protected static $instance = null, $plugin;

    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self;
            self::$instance->doHooks();
        }
        return self::$instance;
    }

    /**
     * NeedHelperExtension constructor.
     */
    public function __construct()
    {
        $plugin = Plugin::getInstance();
    }

    /**
     * NeedHelperExtension hooks.
     */
    public function doHooks()
    {
        add_action('wp_ajax_needHelper', array($this, 'Need_Helper'));
    }


    public function Need_Helper()
    {
        check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
        //$_POST['ID'] = 'import#2'; // Test case
        if (!empty($_POST['ID']) && isset($_POST['ID'])) {
            $id = sanitize_text_field($_POST['ID']);
            $data = [
                /**** choose file Upload *****/
                [
                    "id" => 'import#1',
                    "title" => "Upload File into Plugin",
                    "content" => "Drag and drop CSV, XML, TXT, or ZIP files in Simple Mode for a quick import. For more options, switch to Advanced Mode to upload files from your desktop, FTP/SFTP, or URL.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportValue"
                ],
                [
                    "id" => 'import#1',
                    "title" => "Upload from Desktop",
                    "content" => "Upload files directly from your computer to the plugin by clicking Browse, selecting the desired file, and then clicking Open. This option simplifies data import by selecting files from your device.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportValue"
                ],
                [
                    "id" => 'import#1',
                    "title" => "Upload from FTP/SFTP server",
                    "content" => "Enter your FTP/SFTP credentials, including hostname, username, password, and host path, to allow the importer to access files directly from your remote server. This enables secure and efficient importing of files hosted on external servers.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportValue"
                ],
                [
                    "id" => 'import#1',
                    "title" => "Upload from URL",
                    "content" => "Enter a file URL, like a link from Google Sheets or Dropbox, to import data directly from an online source. Ensure that the link is publicly accessible to enable a smooth import process.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportValue"
                ],
                [
                    "id" => 'import#1',
                    "title" => "Import Google Sheets Data",
                    "content" => "Connect your Google Sheet by entering its URL to seamlessly import data. Publish the spreadsheet to the web to make it accessible, then paste the generated link into the plugin to begin the import.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportValue"
                ],

                /**** choose post Type *****/

                [
                    "id" => 'import#2',
                    "title" => "Start a New Import or Update Existing Records",
                    "content" => "Select whether to start a new import to add fresh data or update existing records (available in the Pro version). [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-csv#0-toc-title",
                    "keyvalue" => "ChooseType"
                ],
                [
                    "id" => 'import#2',
                    "title" => "Configure a WordPress Post Type to Import",
                    "content" => "Select a WordPress post type—such as posts, pages, or custom types—to import your CSV or XML data into. The chosen post type determines the available mapping options, allowing you to align your data fields accordingly.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-csv",
                    "keyvalue" => "ChooseType"
                ],

                /**** mapping section *****/

                [
                    "id" => 'import#3',
                    "title" => "Map File Headers to WordPress Fields",
                    "content" => "Ensure your file headers align with the corresponding WordPress fields for precise data placement during the import process. You can select from advanced mapping options or use the intuitive drag-and-drop feature for your convenience.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "MappingFields"
                ],
                [
                    "id" => 'import#3',
                    "title" => "How to use the Header Manipulation",
                    "content" => "In the advanced mapping, select the Header Manipulation option for a field from the dropdown to perform actions like math operations, concatenation, and custom functions. This ensures your field data aligns correctly during import. For more details, check our documentation.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "MappingFields"
                ],
                [
                    "id" => 'import#3',
                    "title" => "Content Enhancement with OpenAI Integration",
                    "content" => "Set up your OpenAI API key in the settings to enable content generation during the import process. Use the OpenAI icon in the post content, excerpt, and product description fields while mapping to configure the prompt field name [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/chatgpt-integration",
                    "keyvalue" => "MappingFields"
                ],
                [
                    "id" => 'import#3',
                    "title" => "How to Add New Custom Fields",
                    "content" => "During the import process, you can create additional WordPress custom fields to capture data specified in your import file. Simply enter a name for the custom field in the 'Create WP Custom Fields' option within the mapping area [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "MappingFields"
                ],

                /**** media handling *****/

                [
                    "id" => 'import#4',
                    "title" => "Manage Duplicate Media Files",
                    "content" => "Decide how to handle duplicate images during import. You can either use existing images, overwrite them (Pro feature), or create new ones (Pro feature). You can also set up the option to download images from your post content into the media library.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "MediaUpload"
                ],

                /**** import configuration *****/

                [
                    "id" => 'import#5',
                    "title" => "Configure Import Settings",
                    "content" => "Customize your import preferences by enabling maintenance mode, choosing how to handle duplicate records, and specifying the number of records to process in each iteration during the import.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/how-to-import-schedule-or-update",
                    "keyvalue" => "ImportConfiguration"
                ],

                /**** media  dashboard*****/
                [
                    "id" => 'media#1',
                    "title" => "How to Import Images from URLs",
                    "content" => "Upload a file containing image fields such as file name, title, caption, actual url, and more. Map the fields to WordPress fields and make sure to map the actual url field. Run the import and review with the logs to check for any failed media imports, which can then be updated and re-imported as needed. [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/import-images-into-wordpress#1-toc-title",
                    "keyvalue" => "MediaImport"
                ],
                [
                    "id" => 'media#1',
                    "title" => "Media Import to WordPress",
                    "content" => "Select 'Device' to upload and import images directly from your local storage, or choose 'Remote' to import images from external URLs. This feature lets you easily import multiple images into your WordPress media library.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/import-images-into-wordpress#0-toc-title",
                    "keyvalue" => "MediaUpload"
                ],
                [
                    "id" => 'media#1',
                    "title" => "Import Images from Desktop",
                    "content" => "Upload a ZIP file containing multiple images and select the specific images you'd like to import. Then, upload the CSV or XML file that includes image field values, allowing you to import images along with their associated data.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/import-images-into-wordpress#0-toc-title",
                    "keyvalue" => "MediaUpload"
                ],
                /**** media  mapping*****/
                [
                    "id" => 'media#2',
                    "title" => "Match Image File Fields to WP Fields",
                    "content" => "Select the appropriate image field name from the dropdown and map it to the corresponding WordPress fields. Make sure to map the filename field as it is a mandatory field. Either use advanced mapping or drag-and-drop options based on your convenience. [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/import-images-into-wordpress#0-toc-title",
                    "keyvalue" => "MediaMapping"
                ],
                /**** media handling*****/
                [
                    "id" => 'media#3',
                    "title" => "Media Upload Management",
                    "content" => "Use this option to use the existing images in the media library if duplicates are detected during import and eliminate the creation of new images. Next, run the import and verify the results with the import logs. [...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-csv-importer-pro/import-images-into-wordpress#0-toc-title",
                    "keyvalue" => "MediaHandling"
                ],
                /**** Export dashboard*****/

                [
                    "id" => 'export#1',
                    "title" => "Export Your WordPress Data",
                    "content" => "Select a module from the list, such as posts, pages, custom posts, users, products, or orders, to export its data directly from your site. This feature lets you generate a downloadable backup file for the selected content type.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-exporter/wordpress-exporter-guide#0-toc-title",
                    "keyvalue" => "ExportModule"
                ],

                /**** Data Export *****/

                [
                    "id" => 'export#2',
                    "title" => "Flexible Data Export Formats",
                    "content" => "Select from a variety of file formats, including CSV, XML, JSON, or Excel, based on your preferences. The exported data will be downloaded in the format you choose.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-exporter/wordpress-exporter-guide#1-toc-title",
                    "keyvalue" => "DataExport"
                ],
                [
                    "id" => 'export#2',
                    "title" => "Filtered Data Exports",
                    "content" => "Enable various filter options available to customize your export file by criteria such as delimiters, status, author, date, fields, and category. This helps you efficiently extract just the data you need.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-exporter/wordpress-exporter-guide#2-toc-title",
                    "keyvalue" => "DataExport"
                ],
                [
                    "id" => 'export#2',
                    "title" => "Split and Export Records",
                    "content" => "Select the option to split your records and enter a specific number for the split. If you have 100 posts and set the split value to 20, you will receive 5 separate files within a ZIP, each containing 20 records when you download.[...]",
                    "link" => "https://www.smackcoders.com/documentation/wp-ultimate-exporter/wordpress-exporter-guide",
                    "keyvalue" => "DataExport"
                ],


            ];

            // Filter data by the dynamic $id value
            $filtered_data = array_filter($data, function ($item) use ($id) {
                return $item['id'] === $id;
            });
            //echo json_encode(array_values($filtered_data));
            $data = array_values($filtered_data);
            $response = ['result' =>  $data];
            wp_send_json_success($response);
            wp_die();
        } else {
            wp_send_json_error(['message' => 'Invalid ID passing.']);
            wp_die();
        }
    }
}
