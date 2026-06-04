<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_js_save_meta_description', function () {
    check_ajax_referer( 'js_admin_nonce', 'nonce' );
    if ( ! current_user_can( 'edit_pages' ) ) {
        wp_send_json_error( [ 'message' => 'Permessi insufficienti.' ] );
    }

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    $desc    = isset( $_POST['meta_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['meta_description'] ) ) : '';

    if ( ! $post_id || ! get_post( $post_id ) ) {
        wp_send_json_error( [ 'message' => 'Post non trovato.' ] );
    }

    if ( $desc === '' ) {
        delete_post_meta( $post_id, '_js_meta_description' );
    } else {
        update_post_meta( $post_id, '_js_meta_description', $desc );
    }

    wp_send_json_success( [ 'message' => 'Salvato.' ] );
} );
