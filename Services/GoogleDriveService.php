<?php
/**
 * 🚀 GOOGLE DRIVE SERVICE
 * Handles file uploads to Google Drive using REST API
 */

class GoogleDriveService
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $folderId;

    public function __construct()
    {
        $this->clientId = defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '';
        $this->clientSecret = defined('GOOGLE_CLIENT_SECRET') ? GOOGLE_CLIENT_SECRET : '';
        $this->refreshToken = defined('GOOGLE_REFRESH_TOKEN') ? GOOGLE_REFRESH_TOKEN : '';
        $this->folderId = defined('GOOGLE_DRIVE_FOLDER_ID') ? GOOGLE_DRIVE_FOLDER_ID : '';
    }

    /**
     * Get a fresh access token using the refresh token
     */
    private function getAccessToken()
    {
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->refreshToken)) {
            return null;
        }

        $url = 'https://oauth2.googleapis.com/token';
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Google OAuth Error: " . $error);
            return null;
        }

        $data = json_decode($response, true);
        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    /**
     * Upload a file to Google Drive
     */
    public function uploadFile($filePath, $fileName, $mimeType)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['error' => 'Failed to obtain access token. Please check Google Drive credentials in config.php'];
        }

        // 1. Initial Metadata request
        $url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';

        $metadata = [
            'name' => $fileName,
        ];

        if (!empty($this->folderId)) {
            $metadata['parents'] = [$this->folderId];
        }

        $boundary = '-------' . md5(time());
        $content = "--$boundary\r\n";
        $content .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $content .= json_encode($metadata) . "\r\n";
        $content .= "--$boundary\r\n";
        $content .= "Content-Type: $mimeType\r\n\r\n";
        $content .= file_get_contents($filePath) . "\r\n";
        $content .= "--$boundary--";

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: multipart/related; boundary=$boundary",
            "Content-Length: " . strlen($content)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return ['error' => 'Curl Error: ' . $error];
        }

        if ($httpCode >= 400) {
            return ['error' => 'Google Drive API Error: ' . $response];
        }

        $data = json_decode($response, true);
        $fileId = $data['id'];

        // 2. Make the file public (optional but usually needed for display)
        $this->makeFilePublic($fileId, $accessToken);

        // Direct link for <img> tag: https://drive.google.com/uc?export=view&id=FILE_ID
        return [
            'success' => true,
            'id' => $fileId,
            'url' => "https://drive.google.com/uc?export=view&id=" . $fileId,
            'webViewLink' => isset($data['webViewLink']) ? $data['webViewLink'] : null
        ];
    }

    /**
     * Make file readable by anyone with the link
     */
    private function makeFilePublic($fileId, $accessToken)
    {
        $url = "https://www.googleapis.com/drive/v3/files/$fileId/permissions";
        $params = [
            'role' => 'reader',
            'type' => 'anyone'
        ];

        $headers = [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }
}
