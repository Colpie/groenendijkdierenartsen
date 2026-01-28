<?php
add_filter( 'do_shortcode_tag', 'artisteeq_append_image_icon_outside_span', 10, 4 );

function artisteeq_append_image_icon_outside_span( $output, $tag, $attr, $m ) {

    // Enkel Avada/Fusion buttons
    if ( ! in_array( $tag, [ 'fusion_button', 'button' ], true ) ) {
        return $output;
    }

    // Geen dubbel icoon
    if ( strpos( $output, 'fusion-global-btn-icon-img' ) !== false ) {
        return $output;
    }

    $icon_url  = get_stylesheet_directory_uri() . '/assets/images/icon/pijltje_wit.gif';

    $icon_html = '<span class="button-icon"><img src="' . esc_url( $icon_url ) . '" alt="" class="fusion-global-btn-icon-img" aria-hidden="true"></span>';

    /**
     * Plaats het icoon NET NA de fusion-button-text span
     */
    $new_output = preg_replace(
        '~(<span[^>]*class="[^"]*fusion-button-text[^"]*"[^>]*>.*?</span>)~s',
        '$1' . $icon_html,
        $output,
        1
    );

    return $new_output ?: $output;
}
