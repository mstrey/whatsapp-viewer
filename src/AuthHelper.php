<?php
declare(strict_types=1);

class AuthHelper {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function isEmailAuthorized(string $email): bool {
        $allowedStr = getenv('ALLOWED_EMAILS') ?: '';
        $allowedEmails = array_map('trim', explode(',', $allowedStr));
        return in_array($email, $allowedEmails, true);
    }

    public function getLoginUrl(): string {
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $redirectUri = getenv('GOOGLE_REDIRECT_URI');
        $scope = 'email profile';
        
        $url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scope,
            'access_type' => 'online'
        ]);
        return $url;
    }

    public function authenticateWithCode(string $code): ?array {
        $tokenData = $this->fetchGoogleToken($code);
        if (!isset($tokenData['access_token'])) {
            return null;
        }

        $userInfo = $this->fetchGoogleUserInfo($tokenData['access_token']);
        if (!isset($userInfo['email'])) {
            return null;
        }

        return $userInfo;
    }

    private function fetchGoogleToken(string $code): array {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'client_id' => getenv('GOOGLE_CLIENT_ID'),
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => getenv('GOOGLE_REDIRECT_URI'),
            'grant_type' => 'authorization_code',
            'code' => $code
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result ? json_decode($result, true) : [];
    }

    private function fetchGoogleUserInfo(string $accessToken): array {
        $url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $options = [
            'http' => [
                'header' => "Authorization: Bearer {$accessToken}\r\n",
                'method' => 'GET'
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result ? json_decode($result, true) : [];
    }

    public function requireLogin(): void {
        if (!isset($_SESSION['user_email'])) {
            header('Location: login.php');
            exit;
        }
    }
}