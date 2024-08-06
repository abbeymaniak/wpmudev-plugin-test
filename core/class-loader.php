<?php
/**
 * Class to boot up plugin.
 *
 * @package   WPMUDEV_PluginTest
 * @author    WPMUDEV <info@wpmudev.com>
 * @copyright 2023 Incsub
 * @license   http://opensource.org/licenses/MIT MIT License
 * @link      https://wpmudev.com/
 *
 * @since    1.0.0
 * @requires PHP 7.4
 */

namespace WPMUDEV\PluginTest;

use WPMUDEV\PluginTest\Base;

// If this file is called directly, abort.
defined('WPINC') || die;

final class Loader extends Base
{
    /**
     * Settings helper class instance.
     *
     * @since 1.0.0
     * @var   object
     */
    public $settings;

    /**
     * Minimum supported php version.
     *
     * @since 1.0.0
     * @var   float
     */
    public $php_version = '7.4';

    /**
     * Minimum WordPress version.
     *
     * @since 1.0.0
     * @var   float
     */
    public $wp_version = '6.1';

    /**
     * Initialize functionality of the plugin.
     *
     * This is where we kick-start the plugin by defining
     * everything required and register all hooks.
     *
     * @since  1.0.0
     * @access protected
     * @return void
     */
    protected function __construct()
    {
        if (! $this->can_boot() ) {
            return;
        }

        $this->init();
    }

    /**
     * Main condition that checks if plugin parts should continue loading.
     *
     * @return bool
     */
    private function can_boot()
    {
        /**
         * Checks
         *  - PHP version
         *  - WP Version
         * If not then return.
         */
        global $wp_version;

        return (
        version_compare(PHP_VERSION, $this->php_version, '>') &&
        version_compare($wp_version, $this->wp_version, '>')
        );
    }

    /**
     * Register all the actions and filters.
     *
     * @since  1.0.0
     * @access private
     * @return void
     */
    private function init()
    {
        App\Admin_Pages\Auth::instance()->init();
        Endpoints\V1\Auth::instance();
        Endpoint::instance();
        App\Admin_Pages\PostMaintenance::instance()->init();

    }
}
