<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode('zume_footer_logon_modal', 'zume_footer_logon_modal' );
function zume_footer_logon_modal() {
    ?>
    <div class="reveal" id="footer-logon-modal" data-v-offset="30px" data-reveal>
        <h3>Login/Register</h3>
        <hr>
        <div class="login-panel user-login logged-out">
            <div class="user-login logged-out input-field">
                <input type="email" class="user-login logged-out username" id="username" placeholder="Email" value="" />
            </div>
            <div class="user-login logged-out input-field">
                <input type="password" class="user-login logged-out password" id="password" placeholder="Password" value="" />
            </div>
            <div class="center">
                <button class="button user-login logged-out jwt_login">Login</button><br>
            </div>
        </div>
        <div class="register-panel user-login logged-out">
            <div class="user-login logged-out input-field">
                <input type="email" class="user-login logged-out new_username" id="new_username" placeholder="Email" value="" />
            </div>
            <div class="user-login logged-out input-field">
                <input type="password" class="user-login logged-out new_password" id="new_password" placeholder="Password" value="" />
            </div>
            <div class="center">
                <button class="button user-login logged-out jwt_register_user">Registration</button><br>
            </div>
        </div>

        <p class="user-login logged-in u-name"></p>
        <div class="user-login logged-in">
            <button class="button user-login logged-in jwt_logout">Logout</button><br>
        </div>

        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <style>
        .torso-icon {
            border-radius: 50%;
            padding:3px 10px;
            font-size: 1.5rem;
            font-weight: 400;
            font-family: Roboto, Arial, sans-serif !important;
        }
        .torso-icon.logged-in {
            background-color: #00AEFF;
            color:white;
        }
        .torso-icon.logged-out {
            background-color:lightgrey;
        }
        .user-login.login-button {
            text-align:center;
            cursor: pointer;
        }
        .user-login.input-field {
            text-align:center;
            margin: 0 auto;
            width:300px;
        }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('.user-login.login-button').on('click', function(e){
                jQuery('#footer-logon-modal').foundation('open');
            })

            /* remove */
            const d = new Date();
            window.time = d.getTime();
            jQuery('.user-login.new_username').val( window.time + '@email.com')
            jQuery('.user-login.new_password').val( window.time )
            /* remove */

            if ( typeof localStorage.zume_token !== 'undefined' ) {
                jQuery('.user-login').hide()
                jQuery('.user-login.logged-in').show()
                jQuery('.user-login.u-name').html( localStorage.zume_name )
            } else {
                jQuery('.user-login').hide()
                jQuery('.user-login.logged-out').show()
            }

            window.api_remote_post = ( endpoint, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify(data),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: `https://zume5.training/tools/wp-json/`+endpoint,
                    beforeSend: function (xhr) {
                        if (localStorage.zume_token) {
                            xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.zume_token);
                        }
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                    })
            }
            window.api_remote_get = ( endpoint ) => {
                return jQuery.ajax({
                    type: "GET",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: `https://zume5.training/tools/wp-json/`+endpoint,
                    beforeSend: function (xhr) {
                        if (localStorage.zume_token) {
                            xhr.setRequestHeader('Authorization', 'Bearer ' + localStorage.zume_token);
                        }
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                    })
            }

            jQuery('.user-login.jwt_register_user').click(function(){
                let new_email = jQuery('.user-login.new_username').val()
                let display_name = new_email.replace(/@.*$/,"").toLowerCase()
                let new_password = jQuery('.user-login.new_password').val()
                let new_user_params = {
                    'user-email': new_email,
                    'user-display': display_name,
                    'user-password': new_password,
                    'locale': 'en'
                }
                window.api_remote_post('dt/v1/users/register', new_user_params)
                    .done(function(data){
                        localStorage.zume_token = data.jwt.token
                        localStorage.zume_name = data.jwt.user_display_name
                        jQuery('.user-login').hide()
                        jQuery('.user-login.logged-in').show()
                        jQuery('.user-login.u-name').html( localStorage.zume_name )
                        jQuery('#footer-logon-modal').foundation('close');

                        window.api_remote_get('dt/v1/user/my')
                            .done(function(data){
                                console.log(data);
                                window.userObject = data
                            })
                    })
            })
            jQuery('.user-login.jwt_login').click(function(){
                let username = jQuery('.user-login.username').val()
                let password = jQuery('.user-login.password').val()
                let access = {
                    username: username,
                    password: password
                }
                window.api_remote_post('jwt-auth/v1/token', access)
                    .done(function(data){
                        if ( typeof data.token === 'undefined' ) {
                            console.log(data)
                            return
                        }
                        localStorage.zume_token = data.token
                        localStorage.zume_name = data.user_display_name
                        jQuery('.user-login').hide()
                        jQuery('.user-login.logged-in').show()
                        jQuery('.user-login.u-name').html( localStorage.zume_name )
                        jQuery('#footer-logon-modal').foundation('close');

                        window.api_remote_get('dt/v1/user/my')
                            .done(function(data){
                                console.log(data);
                                window.userObject = data
                            })
                        })
            })
            jQuery('.user-login.jwt_logout').click(function() {
                jQuery('.user-login').hide()
                jQuery('.user-login.logged-out').show()
                jQuery('#footer-logon-modal').foundation('close');
                localStorage.removeItem('zume_token')
                localStorage.removeItem('zume_name')
                window.userObject = false
            });

            if (typeof window.userObject === 'undefined' && typeof localStorage.zume_token !== 'undefined' ) {
                window.api_remote_post('jwt-auth/v1/token/validate' )
                    .done(function(data_validate){
                        console.log(data_validate)
                        window.api_remote_get('dt/v1/user/my')
                            .done(function(data){
                                if ( data.ID === 0 ) {
                                    // a failed return can occur from a previous cookie login
                                    window.api_remote_get('dt/v1/user/my')
                                        .done(function(data){ window.userObject = data })
                                } else {
                                    console.log(data);
                                    window.userObject = data
                                }
                            })
                    })
            }
        })
    </script>
    <?php
}

