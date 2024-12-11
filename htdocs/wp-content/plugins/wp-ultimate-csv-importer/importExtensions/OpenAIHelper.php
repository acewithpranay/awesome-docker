<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

 namespace Smackcoders\FCSV;

 if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class OpenAIHelper {
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1/chat/completions';
    private $image_baseUrl = 'https://api.openai.com/v1/images/generations';
    public function generateContent($prompt, $maxCharacters) {
        $get_key =get_option('openAI_settings');
        $this->apiKey = $get_key;

        $data = [
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => $maxCharacters,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        $response = wp_remote_post($this->baseUrl, array(
            'body' => json_encode($data),
            'headers' => $headers,
        ));

        $httpCode = wp_remote_retrieve_response_code($response);
        $decodedResponse = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($httpCode) && $httpCode != 200) {
            return $httpCode;
        }
        if (isset($decodedResponse['choices'][0]['message']['content'])) {
            return $decodedResponse['choices'][0]['message']['content'];
        } else {
            return false;
        }
    }
    public function generateImage($prompt) {
        $get_key =get_option('openAI_settings');
        $this->apiKey = $get_key;
        $data = [
            'prompt' => $prompt,
            'model' => 'dall-e-3',
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        $response = wp_remote_post($this->image_baseUrl, array(
            'body' => json_encode($data),
            'headers' => $headers,
            'timeout' => 60,
        ));
        $httpCode = wp_remote_retrieve_response_code($response);
        $decodedResponse = json_decode(wp_remote_retrieve_body($response), true);

        if ($httpCode !== 200) {
            return $httpCode;
        }
        if (isset($decodedResponse['data'][0]['url'])) {
            return $decodedResponse['data'][0]['url'];
        } else {
            return false;
        }
    }
}