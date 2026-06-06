<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Duplicate Post / Page
   Aggiunge il link "Duplica" nelle azioni riga del listato
   admin. Copia post, meta e tassonomie come bozza.
   ---------------------------------------------------------- */

add_filter( 'post_row_actions',  'js_duplicate_row_action', 10, 2 );
add_filter( 'page_row_actions',  'js_duplicate_row_action', 10, 2 );

function js_duplicate_row_action( array $actions, WP_Post $post ): array {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return $actions;
    }

    $url = wp_nonce_url(
        add_query_arg( [
            'action'  => 'js_duplicate_post',
            'post_id' => $post->ID,
        ], admin_url( 'admin.php' ) ),
        'js_duplicate_post_' . $post->ID
    );

    $actions['duplicate'] = '<a href="' . esc_url( $url ) . '">' . __( 'Duplica', 'jovaddstudio' ) . '</a>';

    return $actions;
}

add_action( 'admin_action_js_duplicate_post', 'js_duplicate_post_handler' );

function js_duplicate_post_handler(): void {
    $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

    if ( ! $post_id ) {
        wp_die( esc_html__( 'ID articolo non valido.', 'jovaddstudio' ) );
    }

    check_admin_referer( 'js_duplicate_post_' . $post_id );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( esc_html__( 'Permessi insufficienti.', 'jovaddstudio' ) );
    }

    $original = get_post( $post_id );

    if ( ! $original ) {
        wp_die( esc_html__( 'Articolo non trovato.', 'jovaddstudio' ) );
    }

    // Inserisce il duplicato come bozza
    $new_id = wp_insert_post( [
        'post_title'     => $original->post_title . ' — ' . __( 'Copia', 'jovaddstudio' ),
        'post_content'   => $original->post_content,
        'post_excerpt'   => $original->post_excerpt,
        'post_status'    => 'draft',
        'post_type'      => $original->post_type,
        'post_author'    => get_current_user_id(),
        'post_parent'    => $original->post_parent,
        'menu_order'     => $original->menu_order,
        'comment_status' => $original->comment_status,
        'ping_status'    => $original->ping_status,
    ] );

    if ( is_wp_error( $new_id ) ) {
        wp_die( esc_html( $new_id->get_error_message() ) );
    }

    // Copia i meta
    foreach ( get_post_meta( $post_id ) as $key => $values ) {
        foreach ( $values as $value ) {
            add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
        }
    }

    // Copia le tassonomie
    foreach ( get_object_taxonomies( $original->post_type ) as $taxonomy ) {
        $terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            wp_set_object_terms( $new_id, $terms, $taxonomy );
        }
    }

    // Reindirizza all'editor del duplicato
    wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
    exit;
}
