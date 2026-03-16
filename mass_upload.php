<?php
use App\{User, Status, Media};
use App\Services\StatusService; // 버전마다 경로가 다를 수 있음
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. 설정
$admin = User::find(1); // 게시물을 올릴 계정 ID
$jsonPath = '/mnt/mscoco/annotations/captions_val2014.json';
if (!file_exists($jsonPath)) {
    die("에러: 파일을 찾을 수 없습니다. 경로를 확인하세요: " . $jsonPath);
}
$imageDir = '/mnt/mscoco/val2014/';
$limit = 100; // 테스트를 위해 100개만 먼저 업로드

$data = json_decode(file_get_contents($jsonPath), true);
$captions = [];
foreach ($data['annotations'] as $ann) {
    $captions[$ann['image_id']] = $ann['caption'];
}

// 2. 실행
$count = 0;
foreach ($data['images'] as $imgInfo) {
    if ($count >= $limit) break;

    $fileName = $imgInfo['file_name'];
    $fullPath = $imageDir . $fileName;
    $caption = $captions[$imgInfo['id']] ?? 'MSCOCO Image';

    if (!file_exists($fullPath)) continue;

    try {
        // Pixelfed 로직을 사용하여 미디어와 게시물 생성
        // 주의: 아래는 개념적 로직이며 실제 버전에 따라 Media::store 등을 활용해야 함
        $status = new Status();
        $status->user_id = $admin->id;
        $status->caption = $caption;
        $status->type = 'photo';
        $status->scope = 'public';
        $status->save();

        // 미디어 파일 처리 로직 (실제 파일 복사 및 DB 연결)
        // Pixelfed의 미디어 처리 핸들러를 호출하는 것이 정석입니다.
        echo "Uploaded: {$fileName}\n";
        $count++;
    } catch (\Exception $e) {
        echo "Error {$fileName}: " . $e->getMessage() . "\n";
    }
}

