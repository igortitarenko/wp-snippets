<?php

/*
Plugin Name: Mpdftest
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: Author
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

include_once(__DIR__ . '/rest/RestMpdf.php');

/**
 * Enqueues scripts.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function (): void {
    $pluginUri  = plugins_url('/restMpdf');
    $pluginPath = plugin_dir_path(__DIR__);

    /* Main script that handles REST call */
    wp_register_script(
        'restMpdf',
        $pluginUri . '/assets/js/rest-mpdf.js',
        [],
        filemtime($pluginPath . '/restMpdf/assets/js/rest-mpdf.js'),
        true
    );

    wp_localize_script(
        'restMpdf',
        'wpApiSettings',
        [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'), // WP nonce
            'nonce_local' => wp_create_nonce('restMpdf'), // Custom that prevents global access to the call
        ],
    );

    wp_enqueue_script('restMpdf');
});
