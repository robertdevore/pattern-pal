<?php

/**
 * Prevent direct access to the file.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the current theme's color palette from theme.json.
 *
 * @since  1.0.0
 * @return array Associative array of color names and their hex values.
 */
function pattern_pal_get_theme_colors() {
    $theme_json = wp_get_global_settings( [ 'color', 'palette' ] );

    if ( empty( $theme_json ) || ! isset( $theme_json['theme'] ) ) {
        return []; // Return empty if no colors found
    }

    $colors = [];
    
    foreach ( $theme_json['theme'] as $color ) {
        if ( isset( $color['slug'], $color['color'] ) ) {
            $colors[ $color['slug'] ] = $color['color'];
        }
    }

    return $colors;
}

/**
 * Fetch block pattern from OpenAI GPT-4o with theme awareness.
 *
 * @param string $prompt The user input prompt for generating the block pattern.
 * @return string|WP_Error The generated pattern content or a WP_Error on failure.
 */
function pattern_pal_generate_pattern( $prompt ) {
    $api_key = get_option( 'pattern_pal_api_key' );

    if ( empty( $api_key ) ) {
        return new WP_Error( 'no_api_key', __( 'No API key found.', 'pattern-pal' ) );
    }

    $theme_name   = wp_get_theme()->get( 'Name' );
    $theme_colors = pattern_pal_get_theme_colors();

    $color_info = '';
    if ( ! empty( $theme_colors ) ) {
        $color_info = "The theme uses the following colors:\n";
        foreach ( $theme_colors as $slug => $hex ) {
            $color_info .= "- $slug: $hex\n";
        }
        $color_info .= "Match the requested design (e.g., 'dark background with light text') with the closest colors from this palette.\n";
    }

    $api_url = 'https://api.openai.com/v1/chat/completions';

    $request_body = wp_json_encode( [
        'model'    => 'gpt-4o',
        'messages' => [
            [
                'role'    => 'system',
                'content' => sprintf(
                    "You are an AI that generates valid WordPress block patterns. 
                    ONLY return the block pattern using proper WordPress block markup. 
                    DO NOT use generic HTML elements like <div> or <section>. 
                    Always wrap elements in valid Gutenberg blocks (e.g., <!-- wp:paragraph -->, <!-- wp:group -->).

                    NO explanations, NO additional text, NO Markdown formatting like triple backticks.

                    The block pattern should be designed for the \"%s\" theme and should include proper spacing, padding and margins. Please make sure all inner blocks use content width.

                    %s",
                    esc_html( $theme_name ),
                    esc_html( $color_info )
                ),
            ],
            [
                'role'    => 'user',
                'content' => sanitize_text_field( $prompt ),
            ],
        ],
        'max_tokens'  => 2000,
        'temperature' => 0.2,
    ] );

    $response = wp_remote_post(
        $api_url,
        [
            'timeout'   => 60,
            'sslverify' => false,
            'headers'   => [
                'Authorization' => 'Bearer ' . sanitize_text_field( $api_key ),
                'Content-Type'  => 'application/json',
            ],
            'body'      => $request_body,
        ]
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body        = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $status_code !== 200 || empty( $body ) || ! isset( $body['choices'][0]['message']['content'] ) ) {
        return new WP_Error( 'api_error', __( 'Invalid response from OpenAI API.', 'pattern-pal' ) );
    }

    // Clean up response: Remove ```html and ```
    $pattern = trim( $body['choices'][0]['message']['content'] );
    $pattern = preg_replace( '/^```html\s*/', '', $pattern );
    $pattern = preg_replace( '/```$/', '', $pattern );

    return wp_kses_post( $pattern );
}

/**
 * Summary of pattern_pal_ajax_generate_pattern
 * 
 * @since  1.0.0
 * @return void
 */
function pattern_pal_ajax_generate_pattern() {
    if ( ! check_ajax_referer( 'pattern_pal_nonce', 'security', false ) ) {
        wp_send_json_error( [ 'message' => __( 'Security check failed.', 'pattern-pal' ) ], 403 );
    }

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'Unauthorized request.', 'pattern-pal' ) ], 403 );
    }

    $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';

    if ( empty( $prompt ) ) {
        wp_send_json_error( [ 'message' => __( 'Prompt cannot be empty.', 'pattern-pal' ) ], 400 );
    }

    $result = pattern_pal_generate_pattern( $prompt );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ], 500 );
    }

    wp_send_json_success( [ 'pattern' => $result ] );
}
add_action( 'wp_ajax_pattern_pal_generate_pattern', 'pattern_pal_ajax_generate_pattern' );
