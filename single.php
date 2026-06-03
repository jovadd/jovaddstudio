<?php get_header(); ?>

<main id="main" class="site-main" role="main">

  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

  <div class="container py-6">
    <?php js_breadcrumbs(); ?>
  </div>

  <section>
    <div class="container">
      <div class="grid grid-3 items-center gap-8">

        <div class="col-span-2 flex flex-col gap-5">

          <?php
          $cats = get_the_category();
          if ( $cats ) : ?>
            <a href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>" class="badge badge-primary self-start">
              <?php echo esc_html( $cats[0]->name ); ?>
            </a>
          <?php endif; ?>

          <h1 class="text-display uppercase"><?php the_title(); ?></h1>
          <h2 class="text-m lede leading-normal font-normal">Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse mollitia in, nulla corporis totam reiciendis nisi! Soluta fuga, doloribus magni fugit, ipsa ullam eum hic ut architecto maiores similique voluptatem?</h2>

          <p class="text-s text-muted">
            <?php esc_html_e( 'Scritto da:', 'jovaddstudio' ); ?>
            <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author" class="font-medium text-muted-hover">
              <?php the_author(); ?>
            </a>
            <?php esc_html_e( 'in data', 'jovaddstudio' ); ?>
            <time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>">
              <?php echo esc_html( get_the_date() ); ?>
            </time>
          </p>

        </div>

        <?php if ( has_post_thumbnail() ) : ?>
          <div class="overflow-hidden rounded-m">
            <?php the_post_thumbnail( 'large', [
              'class'   => 'w-full h-auto object-cover',
              'loading' => 'eager',
            ] ); ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </section>

  <section>
    <div class="container-s">
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </div>
  </section>

  <?php
  // Articoli correlati — stessa categoria, escluso il post corrente
  $cats       = get_the_category();
  $cat_ids    = $cats ? wp_list_pluck( $cats, 'term_id' ) : [];

  $related = $cat_ids ? new WP_Query( [
      'post_type'           => 'post',
      'posts_per_page'      => 3,
      'post__not_in'        => [ get_the_ID() ],
      'category__in'        => $cat_ids,
      'orderby'             => 'date',
      'order'               => 'DESC',
      'no_found_rows'       => true,
      'ignore_sticky_posts' => true,
  ] ) : null;

  if ( $related && $related->have_posts() ) : ?>

  <section>
    <div class="container">

      <h2 class="mb-8"><?php esc_html_e( 'Leggi anche', 'jovaddstudio' ); ?></h2>

      <ul class="grid grid-3">
        <?php while ( $related->have_posts() ) : $related->the_post(); ?>

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
              $r_cats = get_the_category();
              if ( $r_cats ) : ?>
                <a href="<?php echo esc_url( get_category_link( $r_cats[0]->term_id ) ); ?>" class="badge badge-primary self-start">
                  <?php echo esc_html( $r_cats[0]->name ); ?>
                </a>
              <?php endif; ?>

              <h3 class="text-l font-bold leading-snug clamp-2">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>

              <p class="text-muted text-s clamp-3 flex-1"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>

              <div class="flex items-center justify-between pt-4 border-t mt-auto">
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

        <?php endwhile; wp_reset_postdata(); ?>
      </ul>

    </div>
  </section>

  <?php endif; ?>

  <?php endwhile; endif; ?>

</main>

<?php get_footer(); ?>
