<?php
/**
 * Post Maintenace block.
 *
 * @link  https://wpmudev.com/
 * @since 1.0.0
 *
 * @author  WPMUDEV (https://wpmudev.com)
 * @package WPMUDEV\PluginTest
 *
 * @copyright (c) 2023, Incsub (http://incsub.com)
 */

namespace WPMUDEV\PluginTest\App\Admin_Pages;

// Abort if called directly.
defined('WPINC') || die;

if (defined('WP_CLI') && \WP_CLI) {
    error_log('wp cli');
    \WP_CLI::add_command('scan_posts', 'scan_posts_cli');
}

use WPMUDEV\PluginTest\Base;


class PostMaintenance extends Base
{
    /**
     * The page title.
     *
     * @var string
     */
    private $_page_title;

    /**
     * The page slug.
     *
     * @var string
     */
    private $_page_slug = 'wpmudev_plugintest_postmaintenance';


    /**
     * Page Assets.
     *
     * @var array
     */
    private $_page_scripts = array();

    /**
     * Assets version.
     *
     * @var string
     */
    private $_assets_version = '';

    /**
     * A unique string id to be used in markup and jsx.
     *
     * @var string
     */
    private $_unique_id = '';

    /**
     * Initializes the page.
     *
     * @return void
     * @since  1.0.0
     */
    public function init()
    {
        $this->_page_title     = __('Post Maintenance', 'wpmudev-plugin-test');
        $this->_assets_version = ! empty($this->script_data('version')) ? $this->script_data('version') : WPMUDEV_PLUGINTEST_VERSION;
        $this->_unique_id      = "wpmudev_plugintest_postmaintenance_main_wrap-{$this->_assets_version}";

        add_action('admin_menu', array( $this, 'register_admin_page' ));
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_assets' ));
        // Add body class to admin pages.
        add_filter('admin_body_class', array( $this, 'admin_body_classes' ));
        add_action('rest_api_init', [$this, 'register_route']);
    }

    public function register_admin_page()
    {
        $page = add_menu_page(
            'Post Maintenance',
            $this->_page_title,
            'manage_options',
            $this->_page_slug,
            array( $this, 'callback' ),
            'dashicons-admin-tools',
            7
        );

        add_action('load-' . $page, array( $this, 'prepare_assets' ));
    }

    /**
     * The admin page callback method.
     *
     * @return void
     */
    public function callback()
    {
        $this->view();

    }

    /**
     * Prepares assets.
     *
     * @return void
     */
    public function prepare_assets()
    {
        if (! is_array($this->_page_scripts) ) {
            $this->_page_scripts = array();
        }

        $handle       = 'wpmudev_plugintest_authpage';
        $src          = WPMUDEV_PLUGINTEST_ASSETS_URL . '/js/postmaintenancepage.min.js';
        $style_src    = WPMUDEV_PLUGINTEST_ASSETS_URL . '/css/postmaintenancepage.min.css';
        $dependencies = ! empty($this->script_data('dependencies'))
        ? $this->script_data('dependencies')
        : array(
        'react',
        'wp-element',
        'wp-i18n',
        'wp-is-shallow-equal',
        'wp-polyfill',
        'wp-api-fetch'
        );

        $this->_page_scripts[ $handle ] = array(
        'src'       => $src,
        'style_src' => $style_src,
        'deps'      => $dependencies,
        'ver'       => $this->_assets_version,
        'strategy'  => true,
        'localize'  => array(
        'dom_element_id'   => $this->_unique_id,
        ),
        );
    }

    /**
     * Gets assets data for given key.
     *
     * @param string $key
     *
     * @return string|array
     */
    protected function script_data( string $key = '' )
    {
        $raw_script_data = $this->raw_script_data();

        return ! empty($key) && ! empty($raw_script_data[ $key ]) ? $raw_script_data[ $key ] : '';
    }

    /**
     * Gets the script data from assets php file.
     *
     * @return array
     */
    protected function raw_script_data(): array
    {
        static $script_data = null;

        if (is_null($script_data) && file_exists(WPMUDEV_PLUGINTEST_DIR . 'assets/js/postmaintenancepage.min.asset.php') ) {
            $script_data = include WPMUDEV_PLUGINTEST_DIR . 'assets/js/postmaintenancepage.min.asset.php';
        }

        return (array) $script_data;
    }

    /**
     * Prepares assets.
     *
     * @return void
     */
    public function enqueue_assets()
    {
        if (! empty($this->_page_scripts) ) {
            foreach ( $this->_page_scripts as $handle => $page_script ) {
                wp_register_script(
                    $handle,
                    $page_script['src'],
                    $page_script['deps'],
                    $page_script['ver'],
                    $page_script['strategy']
                );

                if (! empty($page_script['localize']) ) {
                       wp_localize_script($handle, 'wpmudevPluginTest', $page_script['localize']);
                }

                wp_enqueue_script($handle);

                if (! empty($page_script['style_src']) ) {
                    wp_enqueue_style($handle, $page_script['style_src'], array(), $this->_assets_version);
                }
            }
        }
    }

    /**
     * Prints the wrapper element which React will use as root.
     *
     * @return void
     */
    protected function view()
    {
        echo '<div id="' . esc_attr($this->_unique_id) . '" class="postmaintenance-wrap"></div>';
    }

    /**
     * Adds the SUI class on markup body.
     *
     * @param string $classes string containing teh exisiting class for the markup body.
     *
     * @return string
     */
    public function admin_body_classes( $classes = '' )
    {
        if (! function_exists('get_current_screen') ) {
            return $classes;
        }

        $current_screen = get_current_screen();

        if (empty($current_screen->id) || ! strpos($current_screen->id, $this->_page_slug) ) {
            return $classes;
        }

        $classes .= ' sui-' . str_replace('.', '-', WPMUDEV_PLUGINTEST_SUI_VERSION) . ' ';

        return $classes;
    }

    /**
     * Register Route.
     *
     * @return void
     */
    public function register_route()
    {
        register_rest_route(
            'wpmudev/v1', '/scan_posts', array(
            'methods' => 'GET',
            'callback' => [$this, 'scan_posts_cli'],
            'permission_callback' => '__return_true'
            )
        );
    }

    /**
     * Scan posts cli.
     *
     * @return void
     */
    public function scan_posts_cli()
    {
        $post_types = apply_filters('scan_posts_post_types', ['post', 'page']);
        $args = [
        'post_type' => $post_types,
        'post_status' => 'publish',
        'numberposts' => -1,
        ];
        $posts = get_posts($args);

        foreach ($posts as $post) {
            update_post_meta($post->ID, 'wpmudev_test_last_scan', current_time('mysql'));
            \WP_CLI::success("Updated post ID {$post->ID}");
        }

        \WP_CLI::success('Scan completed. Timestamp updated for all posts.');
        wp_send_json_success('Scan completed. Timestamp updated for all posts.');

    }

}
