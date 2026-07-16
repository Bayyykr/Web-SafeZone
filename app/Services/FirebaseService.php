<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private function httpClient()
    {
        return Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ]);
    }

    private function getCredentialsPath(): ?string
    {
        $path = env("FIREBASE_CREDENTIALS_PATH");
        if (!$path) return null;

        $fullPath = base_path($path);
        if (file_exists($fullPath)) return $fullPath;
        if (file_exists($path)) return $path;

        return null;
    }

    private function getAccessToken(string $scope): ?string
    {
        $path = $this->getCredentialsPath();
        if (!$path) {
            return null;
        }

        $config = json_decode(file_get_contents($path), true);
        if (!$config) {
            return null;
        }

        $clientEmail = $config["client_email"] ?? null;
        $privateKey = $config["private_key"] ?? null;
        if (!$clientEmail || !$privateKey) {
            return null;
        }

        $header = json_encode(["alg" => "RS256", "typ" => "JWT"]);
        $payload = json_encode([
            "iss" => $clientEmail,
            "scope" => $scope,
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => time() + 3600,
            "iat" => time(),
        ]);

        $base64Header = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($header));
        $base64Payload = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($payload));

        $signature = "";
        if (!openssl_sign($base64Header . "." . $base64Payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $base64Signature = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($signature));
        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;

        $response = $this->httpClient()->asForm()->post("https://oauth2.googleapis.com/token", [
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion" => $jwt,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json()["access_token"] ?? null;
    }

    public function sendNotification(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $path = $this->getCredentialsPath();
        if (!$path) {
            Log::warning("Firebase credentials file not found or not configured.");
            return false;
        }

        $config = json_decode(file_get_contents($path), true);
        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            Log::warning("Firebase project ID not found in credentials.");
            return false;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/firebase.messaging");
        if (!$accessToken) {
            Log::warning("Failed to obtain Firebase access token.");
            return false;
        }

        $response = $this->httpClient()->withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/" . $projectId . "/messages:send",
            [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                    "data" => array_merge($data, [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ]),
                ],
            ]
        );

        return $response->successful();
    }

    public function getFirebaseUidByEmail(string $email): ?string
    {
        $path = $this->getCredentialsPath();
        if (!$path) {
            return null;
        }

        $config = json_decode(file_get_contents($path), true);
        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            return null;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            return null;
        }

        $lookupResponse = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts:lookup",
            [
                "email" => [$email],
            ]
        );

        if ($lookupResponse->failed() || empty($lookupResponse->json()["users"])) {
            return null;
        }

        return $lookupResponse->json()["users"][0]["localId"] ?? null;
    }

    public function createUserInFirebaseAuth(string $email, string $password, string $displayName): ?string
    {
        $path = $this->getCredentialsPath();
        if (!$path) {
            Log::warning("Firebase credentials file not found or not configured.");
            return null;
        }

        $config = json_decode(file_get_contents($path), true);
        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            Log::warning("Firebase project ID not found in credentials.");
            return null;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            Log::warning("Failed to obtain Firebase access token.");
            return null;
        }

        $response = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts",
            [
                "email" => $email,
                "password" => $password,
                "displayName" => $displayName,
                "emailVerified" => true,
            ]
        );

        if ($response->successful()) {
            return $response->json()["localId"] ?? null;
        }

        if ($response->status() === 409 || ($response->status() === 400 && str_contains($response->body(), "EMAIL_EXISTS"))) {
            return $this->getFirebaseUidByEmail($email);
        }

        Log::warning("Firebase Auth Error for " . $email . ": " . $response->body());
        return null;
    }

    public function updateUserPasswordInFirebaseAuth(string $email, string $newPassword): bool
    {
        $path = $this->getCredentialsPath();
        if (!$path) {
            return false;
        }

        $config = json_decode(file_get_contents($path), true);
        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            return false;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            return false;
        }

        $localId = $this->getFirebaseUidByEmail($email);
        if (!$localId) {
            return false;
        }

        $updateResponse = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts:update",
            [
                "localId" => $localId,
                "password" => $newPassword,
            ]
        );

        return $updateResponse->successful();
    }
}
