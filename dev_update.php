<?php
require_once("config.php");

$res = file_get_contents("https://youtube.googleapis.com/youtube/v3/search?part=snippet&channelId=${YOUTUBE_CHANNEL_ID}&maxResults=1&order=date&type=video&key=${YOUTUBE_API_KEY}");

$json = json_decode($res);
$videoId = $json->items[0]->id->videoId;

$res = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" . $videoId . "&key=" . $YOUTUBE_API_KEY);

$json = json_decode($res);

$snippet = $json->items[0]->snippet;

$id = $json->items[0]->id;
$desc = explode("\n\n", $snippet->description)[0];

if (file_exists("latest.txt")) {
    $lastID = file_get_contents("latest.txt");
    if (trim($lastID) == trim($id)) {
        die("Done");
    }    
}

$data = array();
$data["article"]["body_markdown"] = 
    "---\n" .
    "title: " . $snippet->title . "\n" .
    "published: false\n" .
    "description: " . $desc . "\n" .
    "tags:\n" .
    "cover_image: " . $snippet->thumbnails->maxres->url . "\n" .
    "---\n\n" .
    "{% youtube " . $id . " %}\n\n" . $desc;

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

file_put_contents("latest.txt", $id);

