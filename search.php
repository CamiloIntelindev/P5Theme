<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/template-files-by-class/#search-results
 *
 * @package p5marketing
 */

get_header();
?>

<main id="primary" class="site-main" role="main">
	<div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8 py-0 sm:py-16">
		<?php
		if ( have_posts() ) {
			?>
			<!-- Search Header -->
			<div class="mb-12">
				<h1 class="text-4xl font-bold text-gray-900 mb-4">
					<?php
					echo esc_html( sprintf(
						__( 'Results for: %s', 'p5marketing' ),
						get_search_query()
					) );
					?>
				</h1>
				<p class="text-lg text-gray-600">
					<?php
					echo esc_html( sprintf(
						_n(
							'Found %d result',
							'Found %d results',
							(int) $wp_query->found_posts,
							'p5marketing'
						),
						(int) $wp_query->found_posts
					) );
					?>
				</p>
			</div>

			<!-- Search Results Grid -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
				<?php
				while ( have_posts() ) {
					the_post();
					?>
					<article <?php post_class( 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden flex flex-col' ); ?>>
						<?php
						// Featured Image
						if ( has_post_thumbnail() ) {
							?>
							<div class="h-48 overflow-hidden bg-gray-200">
								<?php
								the_post_thumbnail(
									'medium',
									array(
										'class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300',
										'alt'   => get_the_title(),
									)
								);
								?>
							</div>
							<?php
						} else {
							?>
							<div class="h-48 bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
								<svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
								</svg>
							</div>
							<?php
						}
						?>

						<!-- Content -->
						<div class="p-6 flex flex-col flex-1">
							<!-- Post Type Badge -->
							<div class="mb-3">
								<span class="inline-block px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-100 rounded-full">
									<?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?>
								</span>
							</div>

							<!-- Title -->
							<h2 class="text-xl font-semibold text-gray-900 mb-2 line-clamp-2 flex-grow">
								<a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
									<?php the_title(); ?>
								</a>
							</h2>

							<!-- Meta -->
							<div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-4">
								<?php if ( 'post' === get_post_type() ) : ?>
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
										<?php echo esc_html( get_the_date( 'd F Y' ) ); ?>
									</time>
									<span class="flex items-center">
										<?php esc_html_e( 'By', 'p5marketing' ); ?> <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="ml-1 hover:text-blue-600"><?php the_author(); ?></a>
									</span>
								<?php endif; ?>
							</div>

							<!-- Excerpt -->
							<p class="text-gray-600 text-sm line-clamp-3 mb-4">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?>
							</p>

							<!-- Read More Link -->
							<a href="<?php the_permalink(); ?>" class="inline-block text-blue-600 hover:text-blue-700 font-medium text-sm mt-auto">
								<?php esc_html_e( 'Read more →', 'p5marketing' ); ?>
							</a>
						</div>
					</article>
					<?php
				}
				?>
			</div>

			<!-- Pagination -->
			<div class="mt-12 pt-12 border-t border-gray-200">
				<?php
				the_posts_pagination(
					array(
						'mid_size'           => 2,
						'prev_text'          => esc_html__( '← Previous', 'p5marketing' ),
						'next_text'          => esc_html__( 'Next →', 'p5marketing' ),
						'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'p5marketing' ) . ' </span>',
					)
				);
				?>
			</div>
			<?php
		} else {
			// No results found
			?>
			<div class="text-center py-12">
				<svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
				</svg>
				<h1 class="text-3xl font-bold text-gray-900 mb-2">
					<?php esc_html_e( 'No results found', 'p5marketing' ); ?>
				</h1>
				<p class="text-lg text-gray-600 mb-8">
					<?php
					echo esc_html( sprintf(
						__( 'Sorry, we found no results for "%s". Try different keywords.', 'p5marketing' ),
						get_search_query()
					) );
					?>
				</p>

				<!-- Retry Search -->
				<div class="max-w-md mx-auto mb-8">
					<?php get_search_form(); ?>
				</div>

				<!-- Links -->
				<div class="flex flex-col sm:flex-row gap-4 justify-center">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
						<?php esc_html_e( '← Back to Home', 'p5marketing' ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
						<?php esc_html_e( 'Explore Blog', 'p5marketing' ); ?>
					</a>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</main>

<?php
get_footer();
