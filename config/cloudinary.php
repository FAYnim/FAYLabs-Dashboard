<?php
// ============================================================
// Cloudinary Configuration
// ============================================================

require_once dirname(__DIR__) . '/config/app.php';

class Cloudinary
{
    private static function getConfig(): array
    {
        return [
            'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '',
            'api_key'    => $_ENV['CLOUDINARY_API_KEY']    ?? '',
            'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? '',
            'folder'     => $_ENV['CLOUDINARY_FOLDER']     ?? 'portfolio',
        ];
    }

    /**
     * Upload a file to Cloudinary using the Upload API.
     * Returns ['secure_url' => ..., 'public_id' => ...] on success.
     * Throws Exception on failure.
     */
    public static function upload(string $filePath, string $folder = ''): array
    {
        $config = self::getConfig();

        if (empty($config['cloud_name']) || empty($config['api_key']) || empty($config['api_secret'])) {
            throw new Exception('Cloudinary credentials are not configured. Please update your .env file.');
        }

        $timestamp  = time();
        $useFolder  = $folder ?: $config['folder'];
        $paramsToSign = [
            'folder'    => $useFolder,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $signString = http_build_query($paramsToSign, '', '&', PHP_QUERY_RFC3986);
        $signature  = hash('sha1', $signString . $config['api_secret']);

        $uploadUrl = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";

        $postFields = [
            'file'      => new CURLFile($filePath),
            'api_key'   => $config['api_key'],
            'timestamp' => $timestamp,
            'folder'    => $useFolder,
            'signature' => $signature,
        ];

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("cURL error: {$curlError}");
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['secure_url'])) {
            $errorMsg = $data['error']['message'] ?? 'Unknown Cloudinary error';
            throw new Exception("Cloudinary upload failed: {$errorMsg}");
        }

        return [
            'secure_url' => $data['secure_url'],
            'public_id'  => $data['public_id'],
        ];
    }

    /**
     * Delete an image from Cloudinary by public_id.
     */
    public static function delete(string $publicId): bool
    {
        $config = self::getConfig();

        if (empty($config['cloud_name']) || empty($publicId)) {
            return false;
        }

        $timestamp  = time();
        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        ksort($paramsToSign);

        $signString = http_build_query($paramsToSign, '', '&', PHP_QUERY_RFC3986);
        $signature  = hash('sha1', $signString . $config['api_secret']);

        $destroyUrl = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/destroy";

        $postFields = http_build_query([
            'public_id' => $publicId,
            'api_key'   => $config['api_key'],
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $ch = curl_init($destroyUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return isset($data['result']) && $data['result'] === 'ok';
    }
}
