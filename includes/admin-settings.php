<?php

/**
 * Prevent direct access to the file.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Adds a settings menu page.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_add_admin_menu() {
    add_options_page(
        esc_html__( 'Pattern Pal Settings', 'pattern-pal' ),
        esc_html__( 'Pattern Pal', 'pattern-pal' ),
        'manage_options',
        'pattern-pal-settings',
        'pattern_pal_settings_page'
    );
}
add_action( 'admin_menu', 'pattern_pal_add_admin_menu' );

/**
 * Registers Pattern Pal settings.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_register_settings() {
    register_setting( 'pattern_pal_ai_settings', 'pattern_pal_api_key', [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ] );
}
add_action( 'admin_init', 'pattern_pal_register_settings' );

/**
 * Renders the Pattern Pal settings page.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Pattern Pal Settings', 'pattern-pal' ); ?></h1>
        <form method="post" action="options.php">
            <?php 
                settings_fields( 'pattern_pal_ai_settings' ); 
                do_settings_sections( 'pattern_pal_ai_settings' ); 
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="pattern_pal_api_key"><?php esc_html_e( 'OpenAI API Key', 'pattern-pal' ); ?></label>
                    </th>
                    <td>
                        <input 
                            type="password" 
                            name="pattern_pal_api_key" 
                            id="pattern_pal_api_key" 
                            value="<?php echo esc_attr( get_option( 'pattern_pal_api_key' ) ); ?>" 
                            class="regular-text"
                        />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
