<?php
require_once('config.php');

@mkdir($DATA_DIR);

if (!file_exists($YOUTUBE_STATUS_FILE) || !file_exists($YOUTUBE_CONTENT_FILE)) {
    die("No data");
}

$youtube_id = file_get_contents($YOUTUBE_STATUS_FILE);
if (file_exists($TWITTER_STATUS_FILE)) {
    $dev_id = file_get_contents($TWITTER_STATUS_FILE);
    if (trim($dev_id) == trim($youtube_id)) {
        die("Done");
    }    
}

$youtube_data = json_decode(file_get_contents($YOUTUBE_CONTENT_FILE));

function generateNonce() {
    $chars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    $char_len = strlen($chars)-1;
    $output = '';
    while (strlen($output) < 30) {
        $output .= $chars[rand(0, $char_len)];
    }
    return $output;
}

$postdata = [
    "status" => $youtube_data->title . "\n\n" . $youtube_data->youtube_url
];

$url = 'https://api.twitter.com/1.1/statuses/update.json';

$oauth_keys = [
    "oauth_consumer_key" => $TWITTER_OAUTH_CONSUMER_KEY,
    "oauth_nonce" => generateNonce(),
    "oauth_signature_method" => "HMAC-SHA1",
    "oauth_timestamp" => time(),
    "oauth_version" => "1.0",
    "oauth_token" => $TWITTER_OAUTH_TOKEN
];

$outparam = $oauth_keys;
$outparam['status'] = $postdata['status'];

ksort($outparam);

$parameterString = '';

$first = true;
foreach ($outparam as $key => $val) {
    if (!$first) $parameterString .= '&';
    $parameterString .= $key . '=' . rawurlencode($val);
    $first = false;
}

$signbase = 'POST&' . rawurlencode($url) . '&' . rawurlencode($parameterString);
$signkey = $TWITTER_OAUTH_CONSUMER_SECRET . '&' . $TWITTER_OAUTH_TOKEN_SECRET;

$oauth_keys['oauth_signature'] = base64_encode(hash_hmac("sha1", $signbase, $signkey, true));

$auth_sign = '';

$first = true;
foreach ($oauth_keys as $key => $val) {
    if (!$first) $auth_sign .= ', ';
    $auth_sign .= $key . '="' . rawurlencode($val) . '"';
    $first = false;
}

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
            'Authorization: OAuth ' . $auth_sign,
        'content' => http_build_query($postdata)
    ]
]);

echo file_get_contents($url, false, $context);

file_put_contents($TWITTER_STATUS_FILE, $youtube_id);