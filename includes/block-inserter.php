<?php

/**
 * Prevent direct access to the file.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registers a block pattern with a unique ID.
 *
 * @param string $pattern_content The block pattern content.
 * 
 * @since  1.0.0
 * @return string The generated pattern ID.
 */
function pattern_pal_register_block_pattern( $pattern_content ) {
    $pattern_id = 'pattern-pal-' . wp_generate_uuid4();

    register_block_pattern(
        $pattern_id,
        [
            'title'      => esc_html__( 'Pattern Pal', 'pattern-pal' ),
            'categories' => [ 'patterns' ],
            'content'    => wp_kses_post( $pattern_content ), // Sanitize the pattern content
        ]
    );

    return $pattern_id;
}

/**
 * Registers the Pattern Pal block.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_register_block() {
    register_block_type( plugin_dir_path( __FILE__ ) . '../build' );
}
add_action( 'init', 'pattern_pal_register_block' );
