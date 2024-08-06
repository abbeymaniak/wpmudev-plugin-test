<?php
/**
 * Base class for all endpoint classes.
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
use WP_REST_Response;
use WP_REST_Controller;

// If this file is called directly, abort.
defined('WPINC') || die;

class Endpoint extends WP_REST_Controller
{
    /**
     * API endpoint version.
     *
     * @since 1.0.0
     *
     * @var int $version
     */
    protected $version = 1;

    /**
     * API endpoint namespace.
     *
     * @since 1.0.0
     *
     * @var string $namespace
     */
    protected $namespace;

    /**
     * API endpoint for the current endpoint.
     *
     * @since 1.0.0
     *
     * @var string $endpoint
     */
    protected $endpoint = '/auth/confirm';

    /**
     * Endpoint constructor.
     *
     * We need to register the routes here.
     *
     * @since 1.0.0
     */
    protected function __construct()
    {
        // Setup namespace of the endpoint.
        $this->namespace = 'wpmudev/v' . $this->version;

        // If the single instance hasn't been set, set it now.
        $this->register_hooks();
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

    /**
     * Set up WordPress hooks and filters
     *
     * @return void
     * @since  1.0.0
     */
    public function register_hooks()
    {
        add_action('rest_api_init', array( $this, 'register_routes' ));
		add_shortcode('google_oauth_shortcode',[$this, 'google_oauth_shortcode']);
    }

    /**
     * Check if a given request has access to manage settings.
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return bool
     * @since  1.0.0
     */
    public function edit_permission( $request )
    {
        $capable = current_user_can('manage_options');

        /**
         * Filter to modify settings rest capability.
         *
         * @param \WP_REST_Request $request Request object.
         * @param bool $capable Is user capable?.
         *
         * @since 1.0.0
         */
        return apply_filters('wpmudev_plugintest_rest_settings_permission', $capable, $request);
    }

    /**
     * Get formatted response for the current request.
     *
     * @param array $data    Response data.
     * @param bool  $success Is request success.
     *
     * @return WP_REST_Response
     * @since  1.0.0
     */
    public function get_response( $data = array(), $success = true )
    {
        // Response status.
        $status = $success ? 200 : 400;

        return new WP_REST_Response(
            array(
            'success' => $success,
            'data'    => $data,
            ),
            $status
        );
    }

    /**
     * Get the Endpoint's namespace
     *
     * @return string
     */
    public function get_namespace()
    {
        return $this->namespace;
    }

    /**
     * Get the Endpoint's endpoint part
     *
     * @return string
     */
    public function get_endpoint()
    {
        return $this->endpoint;
    }

    public function get_endpoint_url()
    {
        return trailingslashit(rest_url()) . trailingslashit($this->get_namespace()) . $this->get_endpoint();
    }

    /**
     * Register the routes for the objects of the controller.
     *
     * This should be defined in extending class.
     *
     * @since 1.0.0
     */
    public function register_routes()
    {

        register_rest_route(
            $this->get_namespace(), $this->get_endpoint(), array(
            'methods' => 'GET',
            'callback' => [$this, 'process_oauth_confirm'],
            'permission_callback' => '__return_true',
            )
        );
    }

	/**
	 * get auth credentials.
	 * @return array
	 */
    public function get_auth_settings()
    {

        $credentials =     get_option('wpmudev_plugin_test_settings') ?? '';
        $client_id = $credentials['client_id'];
        $client_secret = $credentials['client_secret'];


        return array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        );

    }

	/**
	 * Process oauth confirm.
	 * @param $request
	 * @return array|int|void|\WP_Error
	 */
    public function process_oauth_confirm($request)
    {
        $code = $request->get_param('code');


        if (!$code) {

            return new \WP_Error('missing_code', 'Authorization code is Missing', array(['status' => 400]));
        }

        $response = wp_remote_post(
            'https://oauth2.googleapis.com/token', array(
            'body' => array(
            'code' => $code,
            'client_id' => $this->get_auth_settings()['client_id'],
            'client_secret' => $this->get_auth_settings()['client_secret'],
            'redirect_uri' => $this->get_endpoint_url(),
            'grant_type' => 'authorization_code',
            ),
            )
        );


        if (is_wp_error($response)) {

            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['access_token'])) {
            return new \WP_Error('Invalid_token', 'Failed to retrieve access token', array('status' => 404));
        }

        $user_info_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $data['access_token']);

        if (is_wp_error($user_info_response)) {
            return $user_info_response;
        }

        $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);


        if (!isset($user_info['email'])) {
            return new \WP_Error('missing_email', 'Email address not found.', array('status' => 400));
        }

        $user_email = $user_info['email'];

        // Check if the user exists
        $user = get_user_by('email', $user_email);

        if ($user) {
            // Log the user in if they exist
            wp_set_auth_cookie($user->ID);
            error_log('User logged in: ' . $user->ID);
            wp_redirect(home_url()); // Redirect to home page or admin page
            exit;
        } else {
            // Create a new user and log them in
            $user_id = wp_create_user($user_email, wp_generate_password(), $user_email);
            if (is_wp_error($user_id)) {

                return $user_id;
            }

            wp_set_auth_cookie($user_id);
            error_log('New user created and logged in: ' . $user_id);
            wp_redirect(home_url()); // Redirect to home page or admin page
            exit;


        }
    }

	/**
	 * google oauth shortcode.
	 * @return string
	 */
	public function google_oauth_shortcode()
	{
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			return sprintf(__('Hello, %s! You are logged in.', 'wpmudev-plugin-test'), esc_html($current_user->display_name));
		} else {
			$auth_url = 'https://accounts.google.com/o/oauth2/auth' .
				'?response_type=code' .
				'&client_id=' . urlencode($this->get_auth_settings()['client_id']) .
				'&redirect_uri=' . urlencode($this->get_endpoint_url()) .
				'&scope=' . urlencode('profile email') .
				'&access_type=offline' .
				'&prompt=consent';

			return sprintf(__('Please <a href="%s">log in with Google</a>.', 'wpmudev-plugin-test'), esc_url($auth_url));
		}
	}

}
