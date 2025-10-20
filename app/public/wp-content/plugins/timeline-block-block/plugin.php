<?php

/**
 * Plugin Name: Timeline Block
 * Description: Display timeline content on your site. 
 * Version: 1.2.4
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: timeline-block
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'tlgb_fs' ) ) {
    register_activation_hook( __FILE__, function () {
        if ( is_plugin_active( 'timeline-block-block/plugin.php' ) ) {
            deactivate_plugins( 'timeline-block-block/plugin.php' );
        }
        if ( is_plugin_active( 'timeline-block-block-pro/plugin.php' ) ) {
            deactivate_plugins( 'timeline-block-block-pro/plugin.php' );
        }
    } );
} else {
    // Constant
    define( 'TLGB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.2.4' ) );
    define( 'TLGB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'TLGB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'TLGB_HAS_FREE', 'timeline-block-block/plugin.php' === plugin_basename( __FILE__ ) );
    define( 'TLGB_HAS_PRO', 'timeline-block-block-pro/plugin.php' === plugin_basename( __FILE__ ) );
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'tlgb_fs' ) ) {
        // ... Freemius integration snippet ...
        function tlgb_fs() {
            global $tlgb_fs;
            if ( !isset( $tlgb_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/bplugins_sdk/init.php';
                if ( TLGB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( TLGB_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $tlgbConfig = [
                    'id'                  => '17342',
                    'slug'                => 'timeline-block-block',
                    'premium_slug'        => 'timeline-block-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_624005a9d0c56ff46db6602f5f730',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'menu'                => ( TLGB_HAS_PRO ? [
                        'slug'    => 'edit.php?post_type=btimeline',
                        'support' => false,
                    ] : [
                        'slug'       => 'timeline-block',
                        'first-path' => 'tools.php?page=timeline-block#/dashboard',
                        'support'    => false,
                        'parent'     => [
                            'slug' => 'tools.php',
                        ],
                    ] ),
                ];
                $tlgb_fs = ( TLGB_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $tlgbConfig ) : fs_lite_dynamic_init( $tlgbConfig ) );
            }
            return $tlgb_fs;
        }

        // Init Freemius.
        tlgb_fs();
        // Signal that SDK was initiated.
        do_action( 'tlgb_fs_loaded' );
    }
    function tlgbIsPremium() {
        return ( TLGB_HAS_PRO ? tlgb_fs()->can_use_premium_code() : false );
    }

    // Conditional Admin Dashboard
    if ( TLGB_HAS_PRO && tlgbIsPremium() ) {
        include_once TLGB_DIR_PATH . 'b-timeline/b-timeline.php';
    } else {
        require_once TLGB_DIR_PATH . 'includes/AdminMenu.php';
    }
    if ( !class_exists( 'TLGBPlugin' ) ) {
        class TLGBPlugin {
            public function __construct() {
                add_action( 'init', [$this, 'init'] );
                // Block registration
                add_action( 'enqueue_block_assets', [$this, 'tlgb_enqueue_scripts'] );
                // Enqueue Block Assets For Frontend and Backend
                add_action( 'wp_ajax_tlgbPipeChecker', [$this, 'tlgbPipeChecker'] );
                add_action( 'wp_ajax_nopriv_tlgbPipeChecker', [$this, 'tlgbPipeChecker'] );
                add_action( 'admin_init', [$this, 'registerSettings'] );
                add_action( 'rest_api_init', [$this, 'registerSettings'] );
            }

            function tlgbPipeChecker() {
                if ( !isset( $_POST['_wpnonce'] ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                $nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                wp_send_json_success( [
                    'isPipe' => [
                        'isPipe'   => tlgbIsPremium(),
                        'adminUrl' => admin_url(),
                    ],
                ] );
            }

            function registerSettings() {
                register_setting( 'tlgbUtils', 'tlgbUtils', [
                    'show_in_rest'      => [
                        'name'   => 'tlgbUtils',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    'type'              => 'string',
                    'default'           => wp_json_encode( [
                        'nonce' => wp_create_nonce( 'wp_ajax' ),
                    ] ),
                    'sanitize_callback' => 'sanitize_text_field',
                ] );
            }

            // Function to enqueue block assets for backend and frontend
            public function tlgb_enqueue_scripts() {
                wp_enqueue_script(
                    'timelineJS',
                    TLGB_DIR_URL . 'assets/js/timeline.min.js',
                    ['jquery'],
                    TLGB_VERSION,
                    true
                );
                // Enqueue the CSS
                wp_enqueue_style(
                    'timelineCSS',
                    TLGB_DIR_URL . 'assets/css/timeline.min.css',
                    [],
                    TLGB_VERSION
                );
            }

            function init() {
                register_block_type( __DIR__ . '/build' );
                wp_set_script_translations( 'tlgb-editor', 'timeline-block', plugin_dir_path( __FILE__ ) . 'languages' );
            }

        }

        new TLGBPlugin();
    }
}