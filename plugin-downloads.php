<?php

/*
  Plugin Name: Plugin Downloads
  Description: Shortcode to list plugin download links
  Version: 1.0.1
  Author: Wayne Allen
  Author URI: http://postieplugin.com
  Requires at least: 4.0
  Tested up to: 4.7.3
 */


/*
 * Change Log
 * 1.0.1 - 2017-03-31
 * Initial release
 */

if (!defined('WPINC')) {
    die; // Exit if accessed directly
}

// Add Shortcode
function plugin_downloads_shortcode($atts) {

    $atts = shortcode_atts(array('slug' => '', 'limit' => 20), $atts);

    $plugin = get_my_plugin_downloads($atts['slug']);

    $html = '<h2>Current Version</h2>';
    $html .= "<div>{$plugin['version']} - <a href='{$plugin['url']}'>download</a></div>";
    $html .= '<h2>Other Versions</h2>';
    $html .= '<ul>';
    $i = 0;
    foreach ($plugin['tags'] as $tag) {
        $html .= "<li>{$tag['version']} - <a href='{$tag['url']}'>download</a></li>";
        if ($i++ > $atts['limit']) {
            break;
        }
    }
    $html .= '</ul>';

    return $html;
}

add_shortcode('plugin-downloads', 'plugin_downloads_shortcode');

/*
 *  get_my_plugin_downloads
 *
 *  This function will return an array of plugin download information
 *
 *  @type	function
 *  @date	31/3/17
 *  @since	5.5.10
 *
 *  @param	$slug (string)
 *  @return	(array)
 */

function get_my_plugin_downloads($slug = '') {
    // vars
    $plugin = array(
        'version' => 0,
        'download' => '',
        'tags' => array()
    );
    $url = 'http://api.wordpress.org/plugins/info/1.0/' . $slug;


    // connect
    $request = wp_remote_post($url);


    // success
    if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {

        // unserialize
        $obj = @unserialize($request['body']);

        // version
        $plugin['version'] = $obj->version;
        $plugin['url'] = 'https://downloads.wordpress.org/plugin/' . $slug . '.zip';


        // tags
        preg_match_all('/<h4>(.+?)<\/h4>/', $obj->sections['changelog'], $matches);


        // add tags
        if (isset($matches[1])) {

            foreach ($matches[1] as $tag) {
                $versions = explode(' ', $tag);
                $version = $versions[0];
                $plugin['tags'][] = array(
                    'version' => $version,
                    'url' => str_replace('.zip', '.' . $version . '.zip', $plugin['url'])
                );
            }
        }
    }


    // return
    return $plugin;
}
