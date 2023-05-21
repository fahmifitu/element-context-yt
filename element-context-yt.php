<?php

/**
 * Element Context YOOtheme
 *
 * @package           Element Context YOOtheme
 * @author            Fahmi Elfituri
 *
 * @wordpress-plugin
 * Plugin Name:       Element Context YOOtheme
 * Description:       Configure your YOOtheme elements to show and hide on certain sections of your site.
 * Version:           1.0.0
 * Author:            Fahmi Elfituri
 */

define('ELEMENT_CONTEXT_YT_PATH', dirname(__FILE__));

use YOOtheme\Application;

add_action('after_setup_theme', function () {
    // Check if YOOtheme Pro is loaded
    if (!class_exists(Application::class, false)) {
        return;
    }
    $app = Application::getInstance();
    $app->load(__DIR__ . '/bootstrap.php');
});
