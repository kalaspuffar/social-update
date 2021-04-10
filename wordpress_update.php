<?php
require_once("config.php");
require_once($WORDPRESS_PATH . '/wp-config.php');
require_once($WORDPRESS_PATH . '/wp-includes/formatting.php');
require_once($WORDPRESS_PATH . '/wp-includes/post.php');
require_once($WORDPRESS_PATH . '/wp-includes/user.php');
require_once($WORDPRESS_PATH . '/wp-includes/functions.php');

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

$content = "[youtube " . $youtube_data->youtube_url . "]\n\n" . $youtube_data->description;
$my_post = array(
    'post_title'    => wp_strip_all_tags( $youtube_data->title ),
    'post_content'  => $content,
    'post_status'   => 'publish',
    'post_author'   => $WORDPRESS_POST_AS_USER_ID,
    'post_category' => $WORDPRESS_POST_CATEGORY_ID
);

// Insert the post into the database
$post_id = wp_insert_post( $my_post );

$filename = $youtube_id . '.png';
$upload_file = wp_upload_bits($filename, null, file_get_contents($youtube_data->thumbnail));
if (!$upload_file['error']) {
    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_parent' => 0,
        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );

    if (!is_wp_error($attachment_id)) {
        require_once($WORDPRESS_PATH . '/wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
        wp_update_attachment_metadata( $attachment_id,  $attachment_data );
    }

    set_post_thumbnail( $post_id, $attachment_id );
} else {
    die($upload_file['error']);
}

file_put_contents($WORDPRESS_STATUS_FILE, $youtube_id);