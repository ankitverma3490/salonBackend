<?php
/**
 * 🚀 CLOUDINARY SERVICE
 * Handles file uploads and deletions using Cloudinary REST API
 */

class CloudinaryService
{
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    public function __construct()
    {
        $this->cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: (defined('CLOUDINARY_CLOUD_NAME') ? CLOUDINARY_CLOUD_NAME : '');
        $this->apiKey = getenv('CLOUDINARY_API_KEY') ?: (defined('CLOUDINARY_API_KEY') ? CLOUDINARY_API_KEY : '');
        $this->apiSecret = getenv('CLOUDINARY_API_SECRET') ?: (defined('CLOUDINARY_API_SECRET') ? CLOUDINARY_API_SECRET : '');
    }

    /**
     * Upload a file to Cloudinary
     * Supports images and PDFs
     */
    public function uploadFile($filePath, $options = [])
    {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret)) {
            return ['error' => 'Cloudinary credentials not configured'];
        }

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/upload";

        $timestamp = time();
        $params = [
            'timestamp' => $timestamp,
        ];

        if (isset($options['folder'])) {
            $params['folder'] = $options['folder'];
        }

        // Generate signature
        ksort($params);
        $paramString = "";
        foreach ($params as $key => $value) {
            $paramString .= "$key=$value&";
        }
        $paramString = rtrim($paramString, "&");
        $signature = sha1($paramString . $this->apiSecret);

        // Prepare multipart data
        $postData = $params;
        $postData['api_key'] = $this->apiKey;
        $postData['signature'] = $signature;
        $postData['file'] = new CURLFile($filePath);

        // Add resource_type to post data but not signature params
        if (isset($options['resource_type'])) {
            $postData['resource_type'] = $options['resource_type'];
        } else {
            $postData['resource_type'] = 'auto';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return ['error' => 'Curl Error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return ['error' => 'Cloudinary API Error: ' . ($data['error']['message'] ?? $response)];
        }

        return [
            'success' => true,
            'url' => $data['secure_url'],
            'public_id' => $data['public_id'],
            'resource_type' => $data['resource_type'],
            'format' => $data['format']
        ];
    }

    /**
     * Delete a file from Cloudinary
     */
    public function deleteFile($publicId, $resourceType = 'image')
    {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret)) {
            return ['error' => 'Cloudinary credentials not configured'];
        }

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/destroy";

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        ksort($params);
        $paramString = "";
        foreach ($params as $key => $value) {
            $paramString .= "$key=$value&";
        }
        $paramString = rtrim($paramString, "&");
        $signature = sha1($paramString . $this->apiSecret);

        $postData = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'api_key' => $this->apiKey,
            'signature' => $signature
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return ['error' => 'Cloudinary Delete Error: ' . ($data['error']['message'] ?? $response)];
        }

        return ['success' => true, 'result' => $data['result']];
    }
}
