<?php
include_once "jwt.php";

// 1) 업로드 처리
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    exit("이미지 파일이 없습니다.");
}

$uploadDir = __DIR__ . "/upload/";
if (!file_exists($uploadDir)) mkdir($uploadDir);

$filename = time() . "_" . basename($_FILES["image"]["name"]);
$targetPath = $uploadDir . $filename;

move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath);

// 2) 업로드된 파일의 URL 생성
$serverUrl = "http://YOUR_SERVER_IP_OR_DOMAIN/klingai/upload/";
$imageUrl = $serverUrl . $filename;

// 3) API 요청 준비
$accessKey = "AQB3aMFyhhhEy8PF3YGd4hfeDrhKQCKY";
$secretKey = "4ghdFQhLRH3DRPYKC9Mgam34PRQkTGAa";

$jwt = generate_kling_jwt($accessKey, $secretKey);

// 4) JSON Body 구성
$data = [
    "model_name" => "kling-v1",
    "mode" => "pro",
    "duration" => 5,
    "image" => $imageUrl,
    "prompt" => "The astronaut stands up and walks",
    "cfg_scale" => 0.5
];

$jsonData = json_encode($data);

// 5) CURL API 호출
$ch = curl_init("https://api-singapore.klingai.com/v1/videos/image2video");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$jwt}",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => $jsonData
]);

$response = curl_exec($ch);
curl_close($ch);

// 6) 결과 출력
echo "<pre>";
echo "요청 데이터:\n";
print_r($data);

echo "\n\nAPI 응답:\n";
print_r($response);
echo "</pre>";
