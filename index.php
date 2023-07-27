<?php

require_once 'SocialMediaGetUsername.php';

if (isset($_GET['platform'])) {
    $platform = $_GET['platform'];
    $url = $_GET['url'] ?? '';
    $decodedUrl = urldecode($url);

    try {
        $platformEnum = match ($platform) {
            'soundcloud' => SocialMediaPlatform::SoundCloud,
            'tiktok' => SocialMediaPlatform::TikTok,
            'instagram' => SocialMediaPlatform::Instagram,
            default => throw new Exception("Invalid platform specified."),
        };

        $socialMedia = new SocialMediaGetUsername($decodedUrl, $platformEnum);
        $username = $socialMedia->getUsername();

        echo htmlspecialchars($username);
    } catch (Exception $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Error: 'platform' parameter is missing.";
}
