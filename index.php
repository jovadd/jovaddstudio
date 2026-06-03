<?php get_header(); ?>

<main id="main" class="site-main" role="main">
  <section>
    <div class="container">

      <?php if ( is_search() ) : ?>
        <div class="mb-8">
          <h1><?php printf( esc_html__( 'Risultati per: "%s"', 'jovaddstudio' ), get_search_query() ); ?></h1>
          <?php if ( $wp_query->found_posts ) : ?>
            <p class="text-muted"><?php printf( esc_html( _n( '%d risultato trovato', '%d risultati trovati', $wp_query->found_posts, 'jovaddstudio' ) ), $wp_query->found_posts ); ?></p>
          <?php endif; ?>
          </div>

      <?php elseif ( is_archive() ) : ?>
        <div class="mb-8">
          <?php the_archive_title( '<h1>', '</h1>' ); ?>
          <?php the_archive_description( '<p class="text-muted">', '</p>' ); ?>
        </div>
      <?php endif; ?>

      <?php if ( have_posts() ) : ?>
        <article>
<ul class="grid grid-3">
          <?php while ( have_posts() ) : the_post(); ?>

            <li id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>

              <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_permalink(); ?>" class="block overflow-hidden aspect-video" tabindex="-1" aria-hidden="true">
                  <?php the_post_thumbnail( 'medium_large', [
                    'class'   => 'w-full h-full object-cover transition',
                    'loading' => 'lazy',
                  ] ); ?>
                </a>
              <?php endif; ?>

              <div class="card-body">

                <?php
                $cats = get_the_category();
                if ( $cats ) : ?>
                  <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="badge badge-primary self-start">
                    <?php echo esc_html( $cats[0]->name ); ?>
                  </a>
                <?php endif; ?>

                <h2 class="text-l font-bold leading-snug clamp-2">
                  <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>

                <p class="text-muted text-s clamp-3 flex-1"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>

                <?php
                $tags = get_the_tags();
                if ( $tags ) : ?>
                  <div class="flex flex-wrap gap-2">
                    <?php foreach ( $tags as $tag ) : ?>
                      <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="badge">
                        <?php echo esc_html( $tag->name ); ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <div class="flex items-center justify-between py-4 border-t mt-auto">
                  <address class="not-italic flex items-center gap-2 text-xs text-muted">
                    <?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', '', [ 'class' => 'rounded-full' ] ); ?>
                    <span><?php esc_html_e( 'Scritto da:', 'jovaddstudio' ); ?><br>
                      <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author" class="text-muted font-medium">
                        <?php the_author(); ?>
                      </a>
                    </span>
                  </address>
                  <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-s" aria-label="<?php echo esc_attr( sprintf( __( 'Scopri di più su %s', 'jovaddstudio' ), get_the_title() ) ); ?>">
                    <?php esc_html_e( 'Scopri di più', 'jovaddstudio' ); ?>
                  </a>
                </div>

              </div>

            </li>

          <?php endwhile; ?>
        </ul>
        </article>
        

        <div class="mt-10">
          <?php the_posts_pagination(); ?>
        </div>

      <?php else : ?>

        <div class="alert alert-info mt-8">
          <?php if ( is_search() ) : ?>
            <?php esc_html_e( 'Nessun risultato trovato. Prova con termini diversi.', 'jovaddstudio' ); ?>
          <?php else : ?>
            <?php esc_html_e( 'Nessun contenuto trovato.', 'jovaddstudio' ); ?>
          <?php endif; ?>
        </div>

      <?php endif; ?>

    </div>
  </section>
</main>

<?php get_footer(); ?>
