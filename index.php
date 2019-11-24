<?php

/*
 * Plugin Name: xemlock/wp-links
 * Plugin URI:  https://github.com/xemlock/wp-links
 * Description: Link custom post type
 * Version:     0.1.0
 * Author:      xemlock
 * Author URI:  https://github.com/xemlock
 */

defined('ABSPATH') || die;

add_action('init', function () {
    $pt = array(
        'labels' => array(
            'name'          => __('Links'),
            'singular_name' => __('Link'),
            'add_new'       => _x('Add New', 'link'),
            'add_new_item'  => __('Add New Link'),
            'edit_item'     => __('Edit Link'),
        ),
        'description' => __('Links'),
        'menu_icon' => 'dashicons-admin-links',
        'has_archive' => false,
        'supports' => array(
            // 'author',
            'editor',
            'title',
            'excerpt',
            'thumbnail',
        ),
        'hierarchical'         => false,
        'menu_position'        => 6,
        'show_in_admin_bar'    => true,
        'show_in_nav_menus'    => true,
        'show_ui'              => true,
        'public' => true,
        'rewrite' => false,
        // 'rewrite' => array(
        //     'slug' => 'links',
        // ),

        // 'capability_type' => $POST_TYPE,
        // 'map_meta_cap' => true, // use built-in map_meta_cap impl

//        'capabilities' => array(
//            'publish_posts'          => 'publish_' . $POST_TYPE . 's',
//            'edit_posts'             => 'edit_' . $POST_TYPE . 's',
//            'edit_others_posts'      => 'edit_others_' . $POST_TYPE . 's',
//            'edit_private_posts'     => 'edit_private_' . $POST_TYPE . 's',
//            'edit_published_posts'   => 'edit_published_' . $POST_TYPE . 's',
//            'delete_posts'           => 'delete_' . $POST_TYPE . 's',
//            'delete_others_posts'    => 'delete_others_' . $POST_TYPE . 's',
//            'delete_private_posts'   => 'delete_private_' . $POST_TYPE . 's',
//            'delete_published_posts' => 'delete_published_' . $POST_TYPE . 's',
//            'read_private_posts'     => 'read_private_' . $POST_TYPE . 's',
//            'edit_post'              => 'edit_' . $POST_TYPE,
//            'delete_post'            => 'delete_' . $POST_TYPE,
//            'read_post'              => 'read_' . $POST_TYPE,
//        ),
        'taxonomies' => array(
            'link_category',
            // 'link_tag',
        ),
    );

    $pt = apply_filters('register_post_type_link', $pt);
    register_post_type('link', $pt);

    $rt = array(
        'public'            => true,
        'hierarchical'      => true, // categories are hierarchical
        'label'             => __('Categories'),
        'singular_label'    => __('Category'),
        'show_admin_column' => true,
        'rewrite'           => false,
        'slug' => '/',
        'with_front' => false
//        'capabilities' => array(
//            'manage_terms' => 'manage_categories',
//            'edit_terms'   => 'manage_categories',
//            'delete_terms' => 'manage_categories',
//            'assign_terms' => 'edit_' . self::POST_TYPE . 's',
//        ),
    );

    $rt = apply_filters('register_taxonomy_link_category', $rt);
    register_taxonomy('link_category', 'link', $rt);

    add_action('admin_enqueue_scripts', function () {
        // wp_enqueue_style('linkset', plugin_dir_url(__FILE__) . '/assets/css/style.css', array('dashicons', 'thickbox'));
         wp_enqueue_script('wp-links', plugin_dir_url(__FILE__) . '/admin/editor.js', array('media', 'wp-ajax-response', 'thickbox'), mt_rand());
    });
});

add_action('wp_loaded', function () {
    if (is_admin() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'wp-links-get-post') {
        $post = get_post($_REQUEST['post_id']);
        $response = array();

        // strip out post_ prefix to match WP post api: /wp-json/posts/{post_id}
        foreach (get_object_vars($post) as $key => $value) {
            $response[preg_replace('/^post_/', '', $key)] = $value;
        }
        unset($response['filter'], $response['password'], $response['pinged'], $response['to_ping']);

        $response['link'] = get_permalink($post);

        $img = wp_get_attachment_image_src($post->ID, 'post-thumbnail');
        $response['featured_image'] = $img ? $img[0] : null;

        wp_send_json($response);
        exit;
    }
});

add_action('edit_form_after_title', function (\WP_Post $post) {
    if ($post->post_type !== 'link') {
        return;
    }
?>
<style>
/* hide 'See updated post' */
body .notice.updated a {
    display: none;
}
body #titlediv #edit-slug-box {
    display: none;
}
</style>
<?php require __DIR__ . '/admin/views.php' ?>
<div class="postbox" style="margin: 15px 0 0">
    <h3 style="border-bottom:1px solid #eee;position:relative;">
        <button id="link-type-unselect" type="button" onclick="unselectLinkType()" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);padding-left: 6px;" class="button">
            <i class="dashicons dashicons-no" style="line-height:26px;"></i><?php esc_html_e('Clear') ?>
        </button>
        <span><?php esc_html_e('Link Details') ?></span>

    </h3>
    <div class="inside" style="margin:0;padding:0;">
<?php
$link_meta = array(
    'link_type' => '',
    'link_url'  => '',
);
foreach (get_post_meta($post->ID) as $key => $values) {
    if (substr($key, 0, 5) !== 'link_') {
        continue;
    }
    foreach ($values as $value) {
        $link_meta[$key] = (string) $value;
    }
}
?>
        <input type="hidden" name="link_type" value="<?php esc_attr_e($post->link_type) ?>" />
        <input type="hidden" name="link_url" value="<?php esc_attr_e($post->link_url) ?>" />
        <script>window.WP_Links_initialValues = <?php echo wp_json_encode($link_meta) ?></script>
        <div style="background:#f5f5f5;padding:12px;border-bottom:1px solid #dedede" id="link-type-select">
            <button type="button" onclick="setLinkType('url')">
                <i class="dashicons dashicons-admin-site"></i>
                <?php esc_html_e('Website') ?>
            </button>
            <button type="button" onclick="setLinkType('post')">
                <i class="dashicons dashicons-admin-post"></i>
                <?php esc_html_e('Post') ?>
            </button>
            <button type="button" onclick="setLinkType('file')">
                <i class="dashicons dashicons-media-default"></i>
                <?php esc_html_e('File') ?>
            </button>
            <button type="button" onclick="setLinkType('youtube')">
                <i class="dashicons dashicons-video-alt3"></i>
                <?php esc_html_e('YouTube Video') ?>
            </button>
        </div>
        <div id="wp_link">
<!-- here will be rendered link type templates -->
        </div>
    </div>
</div>
<?php
});

add_filter('wp_insert_post_data', function (array $data, array $post_data) {
    if ($post_data['post_type'] !== 'link' || empty($post_data['post_ID'])) {
        return $data;
    }

    $post_id = $post_data['post_ID'];
    foreach ($post_data as $key => $value) {
        if (substr($key, 0, 5) === 'link_') {
            update_post_meta($post_id, $key, (string) $value);
        }
    }

    return $data;
}, 10, 2);

add_filter('get_post_metadata', function ($metadata, $post_id, $meta_key) {
    if (strncmp($meta_key, 'link_thumbnail', 14) === 0) {
        $thumbnail_size = substr($meta_key, 14, 1) === '_'
            ? substr($meta_key, 15)
            : 'post-thumbnail';

        if (function_exists('get_the_post_thumbnail_url')) { // since 4.4.0
            return get_the_post_thumbnail_url((int) $post_id, $thumbnail_size);
        } else {
            $img = wp_get_attachment_image_src((int) $post_id, $thumbnail_size);
            return $img ? $img[0] : false;
        }
    }
    return $metadata;
}, 10, 3);

// link_thumbnail, link_thumbnail_medium


// stored metas:
// link_type --> which form fields to show
// link_url  --> this is computed on the frontend, for post and file urls, and YT videos. This does not need to be computed for url types (which is the default)
// data for specific link types: link_post_id, link_file_id, link_video_id


// post_type_exists($post_type)

//
// $this->posts = apply_filters_ref_array( 'posts_results', array( $this->posts, &$this ) );
// $this->posts = apply_filters_ref_array( 'the_posts', array( $this->posts, &$this ) );

add_filter( 'the_posts', function ($posts) {
    // fetch link_ metadata for post_type === 'link' found in posts in a single fetch
    // and put them in postmeta cache
    // query_posts();
    return $posts;
}, 10 );

add_filter('manage_link_posts_columns', function (array $columns) {
    $columns['title'] = __('Title');
    return $columns;
}, 10, 1);


//add_action('manage_link_posts_custom_column', function ($column, $post_ID) {
//    if ($column == 'title2') {
//        $oldtitle = get_post($post_ID);
//        $newtitle = str_replace(array("<span class='sub-title'>", "</span>"), array("", ""),$oldtitle);
//        $title = esc_attr($newtitle);
//        echo $title, 123;
//    }
//}, 100, 2);
//

add_filter('post_row_actions', function (array $actions, \WP_Post $post) {
    if ($post->post_type === 'link') {
        // Show link URL below link title
        echo '<div style="width:100%;overflow:hidden;text-overflow:ellipsis;">', esc_html($post->link_url), '</div>';

        // Make 'View' quick action link open target link
        if ($post->link_url) {
            $actions['view'] = '<a href="' . esc_attr($post->link_url) . '" target="_blank">' . esc_html__('View'). '</a>';
        } else {
            unset($actions['view']);
        }
    }
    return $actions;
},10,2);


// admin-ajax.php
// wp_ajax_find_posts
add_filter( 'pre_get_posts', function (&$posts) {
    // fetch link_ metadata for post_type === 'link' found in posts in a single fetch
    // and put them in postmeta cache
    return $posts;
}, 10 );

add_filter( 'posts_where', function ($where, &$posts) {
    return $where;
}, 10, 2);
