<?php
defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------
   Breadcrumb — HTML + JSON-LD BreadcrumbList (SEO)
   Uso: js_breadcrumbs() nei template
   ---------------------------------------------------------- */

function js_breadcrumbs(): void {
    if ( is_front_page() ) {
        return;
    }

    $items = [];
    $items[] = [ 'name' => __( 'Home', 'jovaddstudio' ), 'url' => home_url( '/' ) ];

    if ( is_single() ) {
        $cats = get_the_category();
        if ( $cats ) {
            $items[] = [
                'name' => $cats[0]->name,
                'url'  => get_category_link( $cats[0]->term_id ),
            ];
        }
        $items[] = [ 'name' => get_the_title(), 'url' => '' ];

    } elseif ( is_category() ) {
        $items[] = [ 'name' => single_cat_title( '', false ), 'url' => '' ];

    } elseif ( is_tag() ) {
        $items[] = [ 'name' => single_tag_title( '', false ), 'url' => '' ];

    } elseif ( is_page() ) {
        $queried = get_queried_object();
        if ( $queried && $queried->post_parent ) {
            $ancestors = array_reverse( get_post_ancestors( $queried->ID ) );
            foreach ( $ancestors as $ancestor ) {
                $items[] = [ 'name' => get_the_title( $ancestor ), 'url' => get_permalink( $ancestor ) ];
            }
        }
        $items[] = [ 'name' => get_the_title(), 'url' => '' ];

    } elseif ( is_search() ) {
        $items[] = [ 'name' => sprintf( __( 'Risultati per: "%s"', 'jovaddstudio' ), get_search_query() ), 'url' => '' ];

    } elseif ( is_archive() ) {
        $items[] = [ 'name' => get_the_archive_title(), 'url' => '' ];
    }

    // JSON-LD BreadcrumbList
    $ld_items = [];
    foreach ( $items as $i => $item ) {
        $ld_items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['name'],
            'item'     => $item['url'] ?: get_permalink(),
        ];
    }

    echo '<script type="application/ld+json">'
        . wp_json_encode( [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $ld_items,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
        . '</script>' . "\n";

    // HTML
    $last = array_key_last( $items );
    ?>
    <nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'jovaddstudio' ); ?>">
        <ol class="flex flex-wrap items-center gap-2 text-s text-muted">
            <?php foreach ( $items as $i => $item ) :
                $is_last = ( $i === $last ); ?>
                <li class="flex items-center gap-2">
                    <?php if ( ! $is_last ) : ?>
                        <a href="<?php echo esc_url( $item['url'] ); ?>" class="text-muted breadcrumb-list">
                            <?php echo esc_html( $item['name'] ); ?>
                        </a>
                        <span aria-hidden="true">&rsaquo;</span>
                    <?php else : ?>
                        <span aria-current="page" class="text-primary clamp-1">
                            <?php echo esc_html( $item['name'] ); ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
}
