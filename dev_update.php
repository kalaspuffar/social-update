<?php
require_once("config.php");

@mkdir($DATA_DIR);

if (!file_exists($YOUTUBE_STATUS_FILE) || !file_exists($YOUTUBE_CONTENT_FILE)) {
    die("No data");
}

$youtube_id = file_get_contents($YOUTUBE_STATUS_FILE);

if (file_exists($DEV_STATUS_FILE)) {
    $dev_id = file_get_contents($DEV_STATUS_FILE);
    if (trim($dev_id) == trim($youtube_id)) {
        die("Done");
    }    
}

$youtube_data = json_decode(file_get_contents($YOUTUBE_CONTENT_FILE));

$data = array();
$data["article"]["body_markdown"] = 
    "---\n" .
    "title: " . $youtube_data->title . "\n" .
    "published: false\n" .
    "description: " . $youtube_data->description . "\n" .
    "tags:\n" .
    "cover_image: " . $youtube_data->thumbnail . "\n" .
    "---\n\n" .
    "{% youtube " . $youtube_id . " %}\n\n" . $youtube_data->description;

$postdata = json_encode($data);
$opts = array('http' =>
    array(
        'method'  => "POST",
        'header'  => "Content-Type: application/json\r\napi-key: " . $DEV_API_KEY,
        'content' => $postdata
    )
);

$context = stream_context_create($opts);
file_get_contents('https://dev.to/api/articles', false, $context);

file_put_contents($DEV_STATUS_FILE, $youtube_id);