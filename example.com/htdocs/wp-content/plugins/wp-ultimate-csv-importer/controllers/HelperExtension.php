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
 * Class HelperExtension
 * @package Smackcoders\FCSV
 */
class HelperExtension
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
     * HelperExtension constructor.
     */
    public function __construct()
    {
        $plugin = Plugin::getInstance();
    }

    /**
     * HelperExtension hooks.
     */
    public function doHooks()
    {
        add_action('wp_ajax_helperImport', array($this, 'HelperImport'));
        add_action('wp_ajax_helperSearch', array($this, 'SearchWord'));
    }
    public static function SearchWord()
    {
        self::handleAjaxRequest('Search');
    }

    public static function HelperImport()
    {
        self::handleAjaxRequest('Import', 'Import%20Update%20Media%20Export');
    }

    private static function handleAjaxRequest($mode, $search_term = null)
    {
        check_ajax_referer('smack-ultimate-csv-importer', 'securekey');

        if ($_POST) {
            // $input_mode = sanitize_text_field($_POST['helpermode']);
            $search_term = $search_term ?? sanitize_text_field($_POST['searchInput']);
            if (!empty($search_term)) {
                $search_term = rawurlencode($search_term);
               // $search_url = 'https://dev.smackcoders.com/?swp_form%5Bform_id%5D=2&s=' . $search_term;
                $search_url = 'https://www.smackcoders.com/?swp_form%5Bform_id%5D=1&s=' . $search_term;
                // $ch = curl_init($search_url);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // $html = curl_exec($ch);
                $html = file_get_contents($search_url);

                if ($html === false) {
                    echo json_encode(["error" => "Failed to fetch content"]);
                    return;
                }
                $data = self::parseHtml($html);
                if (!empty($data)) {
                    // Prepare the response
                    $response = ['result' =>  $data];

                    // Send the response as JSON
                    wp_send_json_success($response);
                    wp_die();
                } else {
                    wp_send_json_error(['message' => 'No data found.']);
                    wp_die();
                }
            }
        }
    }

    private static function parseHtml($html)
    {
        try {
            $doc = new \DOMDocument();
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $xpath = new \DOMXPath($doc);
            $result = [];
            foreach ($xpath->query("//article") as $index => $article) {

                $title = trim($xpath->query(".//h2", $article)->item(0)->textContent ?? '');
                $link = $xpath->query(".//a", $article)->item(0)->getAttribute("href") ?? '';
                $content = trim($xpath->query(".//p", $article)->item(0)->textContent ?? '');
                //$img_url = $xpath->query(".//img", $article)->item(0)->getAttribute("src") ?? '';

                $result[] = [
                    "title" => $title,
                    "content" => $content,
                    "link" => $link,
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
