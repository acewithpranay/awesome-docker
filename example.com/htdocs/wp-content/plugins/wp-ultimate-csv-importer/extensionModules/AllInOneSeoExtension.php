<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class AllInOneSeoExtension extends ExtensionHandler
{
    private static $instance = null;

    public static function getInstance()
    {

        if (AllInOneSeoExtension::$instance == null)
        {
            AllInOneSeoExtension::$instance = new AllInOneSeoExtension;
        }
        return AllInOneSeoExtension::$instance;
    }

    /**
     * Provides All In One Seo mapping fields for specific post type
     * @param string $data - selected import type
     * @return array - mapping fields
     */
    public function processExtension($data)
    {
        $response = [];
        $all_in_one_seo_Fields = array(
            'NO INDEX' => 'noindex',
            'NO FOLLOW' => 'nofollow',
            'Canonical URL' => 'custom_link',
            'Disable Analytics' => 'disable_analytics',
            'NO ODP' => 'noodp',
            'NO YDIR' => 'noydir',
            'SEO Title' => 'aioseo_title',
            'SEO description' => 'aioseo_description',
            'Facebook Title' => 'og_title',
            'Facebook Description' => 'og_description',
            'Facebook Image Source' => 'og_image_type',
            'Facebook Custom Image' => 'og_image_custom_url',
            'Facebook Image Custom Fields' => 'og_image_custom_fields',
            'Video URL' => 'og_video',
            'Object Type' => 'og_object_type',
            'Disable' => 'disable',
            'Article Section' => 'og_article_section',
            'Article Tags' => 'og_article_tags',
            'Use Data from Facebook Tab' => 'twitter_use_og',
            'Twitter Card Type' => 'twitter_card',
            'Twitter Custom Image' => 'twitter_image_custom_url',
            'Twitter Image Source' => 'twitter_image_type',
            'TwitterImage Custom Fields' => 'twitter_image_custom_fields',
            'Twitter Title' => 'twitter_title',
            'Twitter Description' => 'twitter_description',
            'Robots Settings' => 'robots_default',
            'Robots No Archive' => 'robots_noarchive',
            'Robots No Snippet' => 'robots_nosnippet',
            'Robots No Image Index' => 'robots_noimageindex',
            'Robots No Translate' => 'robots_notranslate',
            'Robots Max Snippet' => 'robots_max_snippet',
            'Robots Max Video Preview' => 'robots_max_videopreview',
            'Robots Max Image Preview' => 'robots_max_imagepreview',
            'Keyphrases' => 'keyphrases'
        );
        $all_in_one_seo_value = $this->convert_static_fields_to_array($all_in_one_seo_Fields);
        $response['all_in_one_seo_fields'] = $all_in_one_seo_value;
        return $response;
    }

    /**
     * All In One Seo extension supported import types
     * @param string $import_type - selected import type
     * @return boolean
     */
    public function extensionSupportedImportType($import_type)
    {
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php'))
        {
            if ($import_type == 'nav_menu_item')
            {
                return false;
            }

            $import_type = $this->import_name_as($import_type);
            if ($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'WooCommerce')
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
}

