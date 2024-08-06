<?php
/**
 * Singleton class for all classes.
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

// Abort if called directly.
defined('WPINC') || die;

/**
 * Class Singleton
 *
 * @package WPMUDEV\PluginTest
 */
abstract class Singleton
{

    /**
     * Singleton constructor.
     *
     * Protect the class from being initiated multiple times.
     *
     * @param array $props Optional properties array.
     *
     * @since 1.0.0
     */
    protected function __construct( $props = array() )
    {
        // Protect class from being initiated multiple times.
    }

    /**
     * Instance obtaining method.
     *
     * @return static Called class instance.
     * @since  1.0.0
     */
    public static function instance()
    {
        static $instances = array();

     // @codingStandardsIgnoreLine Plugin-backported
     $called_class_name = get_called_class();

        if (! isset($instances[ $called_class_name ]) ) {
            $instances[ $called_class_name ] = new $called_class_name();
        }

        return $instances[ $called_class_name ];
    }
}
