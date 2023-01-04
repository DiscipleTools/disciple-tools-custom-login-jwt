<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if( is_admin() ) {
    DT_Custom_Login_JWT_Menu::instance();
}
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
        $is_dt = class_exists( 'Disciple_Tools' );
        $tabs = [];
        foreach( $vars as $val ) {
            if ( $is_dt === $val['requires_dt'] ) {
                $tabs[$val['tab']] = ucwords( $val['tab'] );
            }
            elseif ( ! $val['requires_dt'] ) {
                $tabs[$val['tab']] = ucwords( $val['tab'] );
            }
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
                            <!-- Box -->
                            <form method="post">
                                <?php wp_nonce_field( $this->token.get_current_user_id(), $this->token . '_nonce' ) ?>
                                <table class="widefat striped">
                                    <tbody>
                                    <?php
                                    if ( ! empty( $vars ) ) {
                                        foreach( $vars as $key => $value ) {
                                            if ( $tab === $value['tab'] ) {
                                                if ( $is_dt === $value['requires_dt'] ) {
                                                    $this->template( $value );
                                                }
                                                elseif ( ! $value['requires_dt'] ) {
                                                    $this->template( $value );
                                                }
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

            if ( isset( $params['delete'] ) ) {
                delete_option('dt_custom_login_fields' );
            } else {
                update_option( 'dt_custom_login_fields', $vars, true );
            }
        }

        return $vars;
    }
}
