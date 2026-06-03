<?php get_header(); ?>

<main id="main" class="site-main" role="main">

  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

  <section>
    <div class="container">

      <h1><?php the_title(); ?></h1>

      <div class="entry-content">
        <?php the_content(); ?>
      </div>

    </div>
  </section>

  <?php endwhile; endif; ?>

</main>

<?php get_footer(); ?>
