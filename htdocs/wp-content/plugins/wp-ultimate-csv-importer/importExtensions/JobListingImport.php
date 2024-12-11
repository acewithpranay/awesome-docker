<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class JobListingImport
{
    private static $job_listing_instance = null;

    public static function getInstance()
    {
        if (JobListingImport::$job_listing_instance == null)
        {
            JobListingImport::$job_listing_instance = new JobListingImport;
            return JobListingImport::$job_listing_instance;
        }
        return JobListingImport::$job_listing_instance;
    }

    public function set_job_listing_values($header_array, $value_array, $map, $post_id, $type)
    {
        $post_values = [];
        $helpers_instance = ImportHelpers::getInstance();
        $post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);

        $this->job_listing_import_function($post_values, $type, $post_id);
    }

    public function job_listing_import_function($data_array, $importas, $pID)
    {
        global $wpdb;
        foreach($data_array as $data_key => $data_value){
            update_post_meta($pID, '_'.$data_key, $data_value);
        }
    }
}