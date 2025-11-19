<?php
function generate_kling_jwt($accessKey, $secretKey) {

    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

    $payload = json_encode([
        'iss' => $accessKey,
        'exp' => time() + 1800,
        'nbf' => time() - 5
    ]);

    $base64UrlEncode = function($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    };

    $base64UrlHeader  = $base64UrlEncode($header);
    $base64UrlPayload = $base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secretKey, true);
    $base64UrlSignature = $base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}
