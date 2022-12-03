<?php
require_once("config.php");

@mkdir($DATA_DIR);

$res = file_get_contents("https://youtube.googleapis.com/youtube/v3/search?part=snippet&channelId=${YOUTUBE_CHANNEL_ID}&maxResults=1&order=date&type=video&key=${YOUTUBE_API_KEY}");

$json = json_decode($res);
$video_id = $json->items[0]->id->videoId;

$res = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=" . $video_id . "&key=" . $YOUTUBE_API_KEY);

$json = json_decode($res);

$snippet = $json->items[0]->snippet;
$duration = $json->items[0]->contentDetails->duration;

$d = new DateInterval($duration);
if ($d->h == 0 && $d->i == 0) {
  die();
}

$id = $json->items[0]->id;
$desc = explode("\n\n", $snippet->description)[0];

$data = array(
    "title" => $snippet->title,
    "description" => $desc,
    "thumbnail" => "https://i.ytimg.com/vi/${id}/maxresdefault.jpg",
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
