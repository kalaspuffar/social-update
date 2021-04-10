<?php
require_once("config.php");

@mkdir("data");

$res = file_get_contents("https://youtube.googleapis.com/youtube/v3/search?part=snippet&channelId=${YOUTUBE_CHANNEL_ID}&maxResults=1&order=date&type=video&key=${YOUTUBE_API_KEY}");

$json = json_decode($res);
$video_id = $json->items[0]->id->videoId;

$res = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" . $video_id . "&key=" . $YOUTUBE_API_KEY);

$json = json_decode($res);

$snippet = $json->items[0]->snippet;

$id = $json->items[0]->id;
$desc = explode("\n\n", $snippet->description)[0];

$data = array(
    "title" => $snippet->title,
    "description" => $desc,
    "thumbnail" => $snippet->thumbnails->maxres->url,
    "youtube_url" => "https://www.youtube.com/watch?v=${id}",
);

file_put_contents($YOUTUBE_CONTENT_FILE, json_encode($data));

if (file_exists($YOUTUBE_STATUS_FILE)) {
    $last_id = file_get_contents($YOUTUBE_STATUS_FILE);
    if (trim($last_id) == trim($id)) {
        die("Done");
    }    
}
file_put_contents($YOUTUBE_STATUS_FILE, $id);