<?php
/**
 * Plugin Name: Disciple.Tools - Custom Login JWT
 * Plugin URI: https://github.com/DiscipleTools/disciple-tools-custom-login-jwt
 * Description: Disciple.Tools - Custom Login JWT adds a custom login/registration page with additional SSO options (Google, Facebook).
 * Text Domain: disciple-tools-custom-login-jwt
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/DiscipleTools
 * GitHub Plugin URI: https://github.com/DiscipleTools/disciple-tools-custom-login-jwt
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 6.2
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Gets the instance of the `Disciple_Tools_Custom_Login_JWT` class.
 *
 * @since  0.2
 * @access public
 * @return object|bool
 */
function disciple_tools_custom_login_jwt() {
    return Disciple_Tools_Custom_Login_JWT::instance();
}
add_action( 'after_setup_theme', 'disciple_tools_custom_login_jwt', 20 );

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class Disciple_Tools_Custom_Login_JWT {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {

        if ( ! class_exists( 'Jwt_Auth' ) ) {
            require_once( 'jwt-library/wp-api-jwt-auth/jwt-auth.php' );
        }

        require_once('shortcodes/loader.php');
        require_once('pages/loader.php');
        require_once('admin/loader.php');

        $dt_custom_login_fields = dt_custom_login_fields();
        if( 'enabled' === $dt_custom_login_fields['login_page']['value'] ) {
            require_once('pages/login-registration.php');
        }
        if( 'enabled' === $dt_custom_login_fields['privacy_policy_page']['value'] ) {
            require_once('pages/privacy-policy.php');
        }
        if( 'enabled' === $dt_custom_login_fields['registration_hold_page']['value'] ) {
            require_once('pages/registration-holding.php');
        }
        if( 'enabled' === $dt_custom_login_fields['terms_of_service_page']['value'] ) {
            require_once('pages/terms-of-service.php');
        }
        if( 'enabled' === $dt_custom_login_fields['user_profile_page']['value'] ) {
            require_once('pages/user-profile.php');
        }

        if ( is_admin() ) {
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }

        $this->i18n();
    }

    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>';
        }

        return $links_array;
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-disciple-tools-custom-login-jwt' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'disciple-tools-custom-login-jwt';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'disciple-tools-custom-login-jwt';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( "disciple_tools_custom_login_jwt::" . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


// Register activation hook.
register_activation_hook( __FILE__, [ 'Disciple_Tools_Custom_Login_JWT', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'Disciple_Tools_Custom_Login_JWT', 'deactivation' ] );


if ( ! function_exists( 'disciple_tools_custom_login_jwt_hook_admin_notice' ) ) {
    function disciple_tools_custom_login_jwt_hook_admin_notice() {
        global $disciple_tools_custom_login_jwt_required_dt_theme_version;
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = "'Disciple.Tools - Custom Login JWT' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.";
        if ( $wp_theme->get_template() === "disciple-tools-theme" ){
            $message .= ' ' . sprintf( esc_html( 'Current Disciple.Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $disciple_tools_custom_login_jwt_required_dt_theme_version ) );
        }
        // Check if it's been dismissed...
        if ( ! get_option( 'dismissed-disciple-tools-custom-login-jwt', false ) ) { ?>
            <div class="notice notice-error notice-disciple-tools-custom-login-jwt is-dismissible" data-notice="disciple-tools-custom-login-jwt">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
                jQuery(function($) {
                    $( document ).on( 'click', '.notice-disciple-tools-custom-login-jwt .notice-dismiss', function () {
                        $.ajax( ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: 'disciple-tools-custom-login-jwt',
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            }
                        })
                    });
                });
            </script>
        <?php }
    }
}

/**
 * AJAX handler to store the state of dismissible notices.
 */
if ( ! function_exists( "dt_hook_ajax_notice_handler" ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST["type"] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST["type"] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}

add_action( 'plugins_loaded', function (){
    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
        // Check for plugin updates
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ){
                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
            Puc_v4_Factory::buildUpdateChecker(
                'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-custom-login-jwt/master/version-control.json',
                __FILE__,
                'disciple-tools-custom-login-jwt'
            );

        }
    }
} );

if ( ! class_exists( 'Disciple_Tools' ) && ! function_exists( 'dt_get_url_path' ) ) {
    function dt_get_url_path( $ignore_query_parameters = false ) {
        if ( isset( $_SERVER['HTTP_HOST'] ) ) {
            $url  = ( !isset( $_SERVER['HTTPS'] ) || @( $_SERVER['HTTPS'] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
            if ( isset( $_SERVER['REQUEST_URI'] ) ) {
                $url .= esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            }
            //remove the domain part. Ex: https://example.com/
            $url = trim( str_replace( get_site_url(), '', $url ), '/' );

            //remove query parameters
            if ( $ignore_query_parameters ){
                $url = strtok( $url, '?' ); //allow get parameters
            }
            //remove trailing '?'
            if ( substr( $url, -1 ) === '?' ){
                $url = substr( $url, 0, -1 );
            }
            // remove trailing '/'
            $url = untrailingslashit( $url );

            return $url;
        }
        return '';
    }
}

function dt_custom_login_fields() {
    $defaults = [

        // general
        'general_label' => [
            'tab' => 'general',
            'key' => 'general_label',
            'label' => 'GENERAL',
            'description' => '',
            'value' => '',
            'type' => 'label',
            'requires_dt' => false
        ],


        // pages
        'pages_label' => [
            'tab' => 'pages',
            'key' => 'pages_label',
            'label' => 'PAGES',
            'description' => '',
            'value' => '',
            'type' => 'label',
            'requires_dt' => true,
        ],
        'login_page' => [
            'tab' => 'pages',
            'key' => 'login_page',
            'label' => 'Login Page',
            'description' => 'Enables the Login Page (<a href="'.site_url().'/login-registration" target="_blank">'.site_url().'/login-registration</a>)',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
            'requires_dt' => true,
        ],
        'privacy_policy_page' => [
            'tab' => 'pages',
            'key' => 'privacy_policy_page',
            'label' => 'Privacy Policy',
            'description' => 'Enables the Privacy Policy (<a href="'.site_url().'/privacy-policy" target="_blank">'.site_url().'/privacy-policy</a>)',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
            'requires_dt' => true,
        ],
        'terms_of_service_page' => [
            'tab' => 'pages',
            'key' => 'terms_of_service_page',
            'label' => 'Terms of Service Page',
            'description' => 'Enables the Terms of Service Page (<a href="'.site_url().'/terms-of-service" target="_blank">'.site_url().'/terms-of-service</a>)',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
            'requires_dt' => true,
        ],
        'user_profile_page' => [
            'tab' => 'pages',
            'key' => 'user_profile_page',
            'label' => 'User Profile',
            'description' => 'Enables the User Profile (<a href="'.site_url().'/user-profile" target="_blank">'.site_url().'/user-profile</a>)',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
            'requires_dt' => true
        ],
        'registration_hold_page' => [
            'tab' => 'pages',
            'key' => 'registration_hold_page',
            'label' => 'Registration Hold Page',
            'description' => 'Enables the Registration Hold (<a href="'.site_url().'/registration-holding" target="_blank">'.site_url().'/registration-holding</a>)',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
            'requires_dt' => true
        ],

        // shortcode
        'shortcode_modal' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_modal',
            'label' => 'Modal Shortcode',
            'description' => '[zume_footer_logon_modal]',
            'description_2' => 'Use this shortcode in the footer of a page to add the full modal and login functions.',
            'value' => '',
            'type' => 'label',
            'requires_dt' => false
        ],
        'shortcode_zume_logon_button' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_zume_logon_button',
            'label' => 'Logon Button',
            'description' => '[zume_logon_button]',
            'description_2' => '',
            'value' => '',
            'type' => 'label',
            'requires_dt' => false
        ],
        'shortcode_zume_logon_button_with_name' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_zume_logon_button_with_name',
            'label' => 'Logon Button with Name',
            'description' => '[zume_logon_button_with_name]',
            'description_2' => '',
            'value' => '',
            'type' => 'label',
            'requires_dt' => false
        ],

        // firebase
        'firebase' => [
            'tab' => 'firebase',
            'key' => 'firebase',
            'label' => 'Firebase',
            'description' => 'firebase description',
            'value' => '',
            'type' => 'text',
            'requires_dt' => false
        ],

        // captcha
        'captcha_key' => [
            'tab' => 'captcha',
            'key' => 'captcha_key',
            'label' => 'Captcha Key',
            'description' => 'captcha description',
            'value' => '',
            'type' => 'text',
            'requires_dt' => false
        ],
    ];

    $defaults_count = count($defaults);

    $saved_fields = get_option('dt_custom_login_fields', [] );
    $saved_count = count($saved_fields);

    $fields = wp_parse_args($saved_fields, $defaults);

    if ( $defaults_count !== $saved_count ) {
        update_option( 'dt_custom_login_fields', $fields, true );
    }

    return $fields;
}
