<?php
enum SocialMediaPlatform {
    case SoundCloud;
    case TikTok;
    case Instagram;
}

class SocialMediaGetUsername {
    public function __construct(
        public readonly string $url,
        public readonly SocialMediaPlatform $platform,
    ) {}

    private function startsWith(string $haystack, string $needle): bool {
        return str_starts_with($haystack, $needle);
    }

    public function getUsername(): string {
        return match($this->platform) {
            SocialMediaPlatform::SoundCloud => $this->getSoundCloudUsername(),
            SocialMediaPlatform::TikTok => $this->getTikTokUsername(),
            SocialMediaPlatform::Instagram => $this->getInstagramUsername(),
        };
    }

    private function getSoundCloudUsername(): string {
        if (!$this->startsWith($this->url, 'https://soundcloud.com/')) {
            throw new \Exception('Invalid SoundCloud URL. The URL should start with "https://soundcloud.com/".');
        }

        $segments = explode('/', parse_url($this->url, PHP_URL_PATH));
        return $segments[1] ?? '';
    }

    private function getTikTokUsername(): string {
        if (!$this->startsWith($this->url, 'https://www.tiktok.com/') && !$this->startsWith($this->url, 'https://tiktok.com/')) {
            throw new \Exception('Invalid TikTok URL. The URL should start with "https://www.tiktok.com/" or "https://tiktok.com/".');
        }

        $segments = explode('/', parse_url($this->url, PHP_URL_PATH));
        $username = str_replace('@', '', $segments[1] ?? '');

        if ($username == 't') {
            $headers = get_headers($this->url, 1);
            if (isset($headers['Location'])) {
                $redirectUrl = $headers['Location'];
                $newSegments = explode('/', parse_url($redirectUrl[0], PHP_URL_PATH));
                $username = str_replace('@', '', $newSegments[1] ?? '');
            }
        }
        return $username;
    }

    private function getInstagramUsername(): string {
        if (!$this->startsWith($this->url, 'https://www.instagram.com/')) {
            throw new \Exception('Invalid Instagram URL. The URL should start with "https://www.instagram.com/".');
        }


//        $app = __DIR__ . '/bin/ig-username';
//        $username = shell_exec($app . " --url=" . escapeshellarg($this->url));
//        if (!$username) {
//            throw new \Exception('Username not found.');
//        }
        var_dump($this->fetchInstagramUsername($this->url));
        return '';
    }

}
