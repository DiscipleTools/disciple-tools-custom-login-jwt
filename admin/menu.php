<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Custom_Login_JWT_Menu
 */
class DT_Custom_Login_JWT_Menu {

    public $token = 'disciple_tools_custom_login_jwt';
    public $tab_title = 'Custom Login';
    public $tabs;

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( "admin_menu", array( $this, "register_menu" ) );
        $this->tabs = [
            'general' => [
                'class' => 'DT_Custom_Login_JWT_Tab_General',
                'label' => 'General'
            ],
            'firebase' => [
                'class' => 'DT_Custom_Login_JWT_Tab_Firebase',
                'label' => 'Firebase'
            ],
            'captcha' => [
                'class' => 'DT_Custom_Login_JWT_Tab_Captcha',
                'label' => 'Captcha'
            ],
            'help' => [
                'class' => 'DT_Custom_Login_JWT_Tab_Help',
                'label' => 'Help'
            ]
        ];
    }

    public function register_menu() {
        if ( class_exists( 'Disciple_Tools' ) ) {
            add_submenu_page( 'dt_extensions', $this->tab_title, $this->tab_title, 'manage_dt', $this->token, [ $this, 'content' ] );
        } else {
            add_menu_page( $this->tab_title, $this->tab_title, 'manage_options', $this->token, [ $this, 'content' ] );
        }
    }

    /**
     * Menu stub. Replaced when Disciple.Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

//        $tabs = $this->tabs;
        $link = 'admin.php?page='.$this->token.'&tab=';

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $vars = $this->process_postback();
        $tabs = [];
        dt_write_log($vars);
        foreach( $vars as $val ) {
            $tabs[$val['tab']] = ucwords( $val['tab'] );
        }
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->tab_title ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach( $tabs as $key => $value ) {
                    ?>
                    <a href="<?php echo esc_attr( $link ) . $key ?>"
                       class="nav-tab <?php echo esc_html( ( $tab == $key ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $value ) ?></a>
                    <?php
                }
                ?>
            </h2>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <!-- Box -->
                            <form method="post">
                                <?php wp_nonce_field( $this->token.get_current_user_id(), $this->token . '_nonce' ) ?>
                                <table class="widefat striped">
                                    <tbody>
                                    <?php
                                    if ( ! empty( $vars ) ) {
                                        foreach( $vars as $key => $value ) {
                                            if ( $tab === $value['tab'] ) {
                                                $this->template( $value );
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2">
                                            <button class="button" type="submit">Save</button> <button class="button" type="submit" style="float:right;" name="delete" value="1">Reset</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                            <br>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
        </div><!-- End wrap -->
        <?php
    }

    public function template( $args ) {
        switch( $args['type'] ) {
            case 'text':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <input type="text" name="<?php echo $args['key'] ?>" value="<?php echo $args['value'] ?>" /> <?php echo $args['description'] ?>
                    </td>
                </tr>
                <?php
                break;
            case 'select':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <select name="<?php echo $args['key'] ?>">
                            <option></option>
                            <?php
                            foreach( $args['default'] as $item_key => $item_value ) {
                                ?>
                                <option value="<?php echo $item_key ?>" <?php echo ( $item_key === $args['value'] ) ? 'selected' : '' ?>><?php echo $item_value ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php echo $args['description'] ?>
                    </td>
                </tr>
                <?php
                break;
            case 'label':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <?php echo $args['description'] ?>
                        <?php echo ( isset( $args['description_2'] ) && ! empty($args['description_2']) ) ? '<p>' . $args['description_2'] . '</p>' : '' ?>
                    </td>
                </tr>
                <?php
                break;
            default:
                break;
        }
    }

    public function process_postback(){
        $vars = dt_custom_login_fields();

        // process POST
        if ( isset( $_POST[$this->token.'_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[$this->token.'_nonce'] ) ), $this->token . get_current_user_id() ) ) {
            $params = $_POST;

            foreach( $params as $key => $param ) {
                if ( isset( $vars[$key]['value'] ) ) {
                    $vars[$key]['value'] = $param;
                }
            }

            if ( $params['delete'] ) {
                delete_option('dt_custom_login_fields' );
            } else {
                update_option( 'dt_custom_login_fields', $vars, true );
            }

            dt_write_log($_POST);
        }

        return $vars;
    }
}
DT_Custom_Login_JWT_Menu::instance();

function dt_custom_login_fields() {
    $defaults = [
        'pages_label' => [
            'tab' => 'general',
            'key' => 'pages_label',
            'label' => 'PAGES',
            'description' => '',
            'value' => '',
            'type' => 'label',
        ],
        'login_page' => [
            'tab' => 'general',
            'key' => 'login_page',
            'label' => 'Login Page',
            'description' => 'Enables the Login Page',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
        ],
        'privacy_policy_page' => [
            'tab' => 'general',
            'key' => 'privacy_policy_page',
            'label' => 'Privacy Policy',
            'description' => 'Enables the Privacy Policy',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
        ],
        'terms_of_service_page' => [
            'tab' => 'general',
            'key' => 'terms_of_service_page',
            'label' => 'Terms of Service Page',
            'description' => 'Enables the Terms of Service Page',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
        ],
        'user_profile_page' => [
            'tab' => 'general',
            'key' => 'user_profile_page',
            'label' => 'User Profile',
            'description' => 'Enables the User Profile',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
        ],
        'registration_hold_page' => [
            'tab' => 'general',
            'key' => 'registration_hold_page',
            'label' => 'Registration Hold Page',
            'description' => 'Enables the Registration Hold ',
            'default' => [
                'enabled' => 'Enabled',
                'disabled' => 'Disabled',
            ],
            'value' => 'enabled',
            'type' => 'select',
        ],


        'shortcode_modal' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_modal',
            'label' => 'Modal Shortcode',
            'description' => '[zume_footer_logon_modal]',
            'description_2' => 'Use this shortcode in the footer of a page to add the full modal and login functions.',
            'value' => '',
            'type' => 'label',
        ],
        'shortcode_zume_logon_button' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_zume_logon_button',
            'label' => 'Logon Button',
            'description' => '[zume_logon_button]',
            'description_2' => '',
            'value' => '',
            'type' => 'label',
        ],
        'shortcode_zume_logon_button_with_name' => [
            'tab' => 'shortcodes',
            'key' => 'shortcode_zume_logon_button_with_name',
            'label' => 'Logon Button with Name',
            'description' => '[zume_logon_button_with_name]',
            'description_2' => '',
            'value' => '',
            'type' => 'label',
        ],

        'firebase' => [
            'tab' => 'firebase',
            'key' => 'firebase',
            'label' => 'Firebase',
            'description' => 'firebase description',
            'value' => '',
            'type' => 'text',
        ],

        'captcha_key' => [
            'tab' => 'captcha',
            'key' => 'captcha_key',
            'label' => 'Captcha Key',
            'description' => 'captcha description',
            'value' => '',
            'type' => 'text',
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

