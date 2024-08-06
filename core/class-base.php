<?php
/**
 * File Description:
 * Base abstract class to be inherited by other classes
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

use WPMUDEV\PluginTest\Singleton;

// Abort if called directly.
defined('WPINC') || die;

/**
 * Class Base
 *
 * @package WPMUDEV\PluginTest
 */
abstract class Base extends Singleton
{
    /**
     * Getter method.
     *
     * Allows access to extended site properties.
     *
     * @param string $key Property to get.
     *
     * @return mixed Value of the property. Null if not available.
     * @since  1.0.0
     */
    public function __get( $key )
    {
        // If set, get it.
        if (isset($this->{$key}) ) {
            return $this->{$key};
        }

        return null;
    }

    /**
     * Setter method.
     *
     * Set property and values to class.
     *
     * @param string $key   Property to set.
     * @param mixed  $value Value to assign to the property.
     *
     * @since 1.0.0
     */
    public function __set( $key, $value )
    {
        $this->{$key} = $value;
    }
}
