<?php
/**
 * Google Auth Shortcode.
 *
 * @package WPMUDEV\PluginTest
 *
 * @link  https://wpmudev.com/
 * @since 1.0.0
 *
 * @author WPMUDEV (https://wpmudev.com)
 *
 * @copyright (c) 2023, Incsub (http://incsub.com)
 */

namespace WPMUDEV\PluginTest\Endpoints\V1;

// Abort if called directly.
defined('WPINC') || die;

use WPMUDEV\PluginTest\Endpoint;
use WP_REST_Server;

class Auth extends Endpoint
{
    /**
     * API endpoint for the current endpoint.
     *
     * @since 1.0.0
     *
     * @var string $endpoint
     */
    protected $endpoint = 'auth/auth-url';

    /**
     * Register the routes for handling auth functionality.
     *
     * @return void
     * @since  1.0.0
     */
    public function register_routes()
    {
        // TODO
        // Add a new Route to logout.
        register_rest_route(
            $this->get_namespace(), '/auth/logout', array(
            'methods' => 'POST',
            'callback' => [$this, 'process_oauth_logout'],
            'permission_callback' => '__return_true',
            )
        );

        // Route to get auth url.
        register_rest_route(
            $this->get_namespace(),
            $this->get_endpoint(),
            array(
            array(
            'methods' => 'GET',
            'args' => array(
            'client_id' => array(
            'required' => true,
            'description' => __('The client ID from Google API project.', 'wpmudev-plugin-test'),
            'type' => 'string',
            ),
            'client_secret' => array(
            'required' => true,
            'description' => __('The client secret from Google API project.', 'wpmudev-plugin-test'),
            'type' => 'string',
            ),
                    ),
                     'permission_callback' => '__return_true',
            ),
            )
        );

        //POST request to save settings
        register_rest_route(
            $this->get_namespace(), $this->get_endpoint(), [
            'methods' => 'POST',
            'callback' => [$this, 'save_credentials'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
            ]
        );

        //GET request
        register_rest_route(
            $this->get_namespace(), 'get_settings', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_auth_settings'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
            )
        );




    }

    /**
     * @param  \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function save_credentials(\WP_REST_Request $request)
    {

        $client_id = sanitize_text_field($request->get_param('client_id'));
        $client_secret = sanitize_text_field($request->get_param('client_secret'));

        if (!$client_id) {
            return new \WP_REST_Response(__('Client ID field is empty!.', 'wpmudev-plugin-test'), 400);
        }

        if (!$client_secret) {
            return new \WP_REST_Response(__('Client field is empty!.', 'wpmudev-plugin-test'), 400);
        }

        if (!$client_id || !$client_secret) {
            return new \WP_REST_Response(__('Client ID & Client Secret is empty', 'wpmudev-plugin-test'), 400);
        }

        $settings = [
        'client_id' => $client_id,
        'client_secret' => $client_secret
        ];

        update_option('wpmudev_plugin_test_settings', $settings);

        return new \WP_REST_Response(__('Google Credentials saved.', 'wpmudev-plugin-test'));


    }

    /**
     * Get the client auth credentials.
     *
     * @return array
     */
    public function get_auth_settings()
    {

        $credentials = get_option('wpmudev_plugin_test_settings') ?? '';
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];


        return array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        );

    }

    /**
     * Logout user.
     *
     * @return \WP_REST_Response
     */
    public function process_oauth_logout()
    {
        // Clear authentication cookies
        wp_clear_auth_cookie();


        $redirect_url = home_url();
        return rest_ensure_response(
            array(
            'status' => 'success',
            'message' => 'Logged out successfully.',
            'redirect_url' => $redirect_url
            )
        );
    }
}
