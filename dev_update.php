<?php
require_once("config.php");

if (!file_exists("youtube_fetch_latest.txt") || !file_exists("youtube_content.json")) {
    die("No data");
}

$youtube_id = file_get_contents("youtube_fetch_latest.txt");

if (file_exists("dev_latest.txt")) {
    $dev_id = file_get_contents("dev_latest.txt");
    if (trim($dev_id) == trim($youtube_id)) {
        die("Done");
    }    
}

$youtube_data = json_decode(file_get_contents("youtube_fetch_latest.txt"));

$data = array();
$data["article"]["body_markdown"] = 
    "---\n" .
    "title: " . $youtube_data["title"] . "\n" .
    "published: false\n" .
    "description: " . $youtube_data["description"] . "\n" .
    "tags:\n" .
    "cover_image: " . $youtube_data["thumbnail"] . "\n" .
    "---\n\n" .
    "{% youtube " . $youtube_id . " %}\n\n" . $youtube_data["description"];

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

file_put_contents("dev_latest.txt", $youtube_id);

