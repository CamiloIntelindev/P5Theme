<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://developer.wordpress.org/themes/template-files-by-class/#404
 *
 * @package p5marketing
 */

get_header();
?>

<main id="primary" class="site-main" role="main">
	<div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
		<div class="text-center">
			<!-- Large 404 Display -->
			<div class="mb-8">
				<h1 class="text-6xl sm:text-8xl font-bold text-gray-900 mb-4">404</h1>
				<div class="h-1 w-24 bg-gradient-to-r from-blue-600 to-purple-600 mx-auto mb-8"></div>
			</div>

			<!-- Error Message -->
			<h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
				<?php esc_html_e( 'Page not found', 'p5marketing' ); ?>
			</h2>
			<p class="text-lg text-gray-600 mb-12 max-w-lg mx-auto">
				<?php esc_html_e( 'Sorry, the page you are looking for does not exist or has been moved. Here are some options to continue:', 'p5marketing' ); ?>
			</p>

			<!-- Search Form -->
			<div class="mb-12 max-w-md mx-auto">
				<?php get_search_form(); ?>
			</div>

			<!-- Action Buttons -->
			<div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
					<?php esc_html_e( '← Back to Home', 'p5marketing' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
					<?php esc_html_e( 'Go to Blog', 'p5marketing' ); ?>
				</a>
			</div>

			<!-- Recent Posts -->
			<div class="mt-16">
				<h3 class="text-2xl font-bold text-gray-900 mb-8">
					<?php esc_html_e( 'Recent Posts', 'p5marketing' ); ?>
				</h3>
				<?php
				$recent_posts = get_posts( array(
					'numberposts' => 3,
					'orderby'     => 'date',
					'order'       => 'DESC',
				) );

				if ( ! empty( $recent_posts ) ) {
					echo '<div class="grid grid-cols-1 md:grid-cols-3 gap-8">';
					foreach ( $recent_posts as $post ) {
						setup_postdata( $post );
						?>
						<article class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
							<?php
							if ( has_post_thumbnail() ) {
								?>
								<div class="h-48 overflow-hidden">
									<?php the_post_thumbnail( 'medium', array( 'class' => 'w-full h-full object-cover' ) ); ?>
								</div>
								<?php
							}
							?>
							<div class="p-6">
								<h4 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
									<a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
										<?php the_title(); ?>
									</a>
								</h4>
								<time class="text-sm text-gray-500">
									<?php echo esc_html( get_the_date( 'd F Y' ) ); ?>
								</time>
								<p class="mt-3 text-gray-600 text-sm line-clamp-3">
									<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?>
								</p>
								<a href="<?php the_permalink(); ?>" class="inline-block mt-4 text-blue-600 hover:text-blue-700 font-medium text-sm">
									<?php esc_html_e( 'Read more →', 'p5marketing' ); ?>
								</a>
							</div>
						</article>
						<?php
					}
					echo '</div>';
					wp_reset_postdata();
				} else {
					echo '<p class="text-gray-600">' . esc_html__( 'No posts available yet.', 'p5marketing' ) . '</p>';
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
