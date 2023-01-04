<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode('zume_logon_button', 'zume_logon_button' );
function zume_logon_button() {
    ?>
    <div class="login-button-set center-text">
        <div class="user-login logged-out login-button">
            <i class="fi-torso torso-icon logged-out"></i>
        </div>
        <div class="user-login logged-in login-button" style="display:none;">
            <i class="fi-torso torso-icon logged-in"></i><br>
        </div>
    </div>
    <?php
}



