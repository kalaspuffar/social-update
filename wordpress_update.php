<?php
require_once("config.php");
require_once($WORDPRESS_PATH . 'wp-config.php');
require_once($WORDPRESS_PATH . 'wp-includes/formatting.php');
require_once($WORDPRESS_PATH . 'wp-includes/post.php');
require_once($WORDPRESS_PATH . 'wp-includes/user.php');
require_once($WORDPRESS_PATH . 'wp-includes/functions.php');

@mkdir($DATA_DIR);

if (!file_exists($YOUTUBE_STATUS_FILE) || !file_exists($YOUTUBE_CONTENT_FILE)) {
    die("No data");
}

$youtube_id = file_get_contents($YOUTUBE_STATUS_FILE);

if (file_exists($WORDPRESS_STATUS_FILE)) {
    $dev_id = file_get_contents($WORDPRESS_STATUS_FILE);
    if (trim($dev_id) == trim($youtube_id)) {
        die("Done");
    }    
}

$youtube_data = json_decode(file_get_contents($YOUTUBE_CONTENT_FILE));

$content = "[youtube " . $youtube_data->youtube_url . "&w=480&h=270]\n\n" . $youtube_data->description;
$my_post = array(
    'post_title'    => wp_strip_all_tags( $youtube_data->title ),
    'post_content'  => $content,
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_category' => array()
);

// Insert the post into the database
wp_insert_post( $my_post );

file_put_contents($WORDPRESS_STATUS_FILE, $youtube_id);