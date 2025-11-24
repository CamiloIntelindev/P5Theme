<?php
/**
 * The comments template.
 *
 * @package p5marketing
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area mx-auto max-w-screen-md px-4 sm:px-6 lg:px-8">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title text-2xl font-bold mb-6">
			<?php
			printf( _nx( '%1$s comment', '%1$s comments', get_comments_number(), 'comments title', 'p5marketing' ), number_format_i18n( get_comments_number() ) );
			?>
		</h2>

		<ol class="comment-list space-y-6">
			<?php
			wp_list_comments( array(
				'short_ping' => true,
				'avatar_size' => 40,
				'reply_text' => esc_html__( 'Reply', 'p5marketing' ),
			) );
			?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav class="comment-navigation mt-6" aria-label="Comment Navigation">
				<div class="flex justify-between">
					<div class="prev"><?php previous_comments_link( esc_html__( 'Older Comments', 'p5marketing' ) ); ?></div>
					<div class="next"><?php next_comments_link( esc_html__( 'Newer Comments', 'p5marketing' ) ); ?></div>
				</div>
			</nav>
		<?php endif; ?>

	<?php endif; // Check for have_comments(). ?>

	<?php
	$comment_form_args = array(
		'title_reply' => esc_html__( 'Leave a Reply', 'p5marketing' ),
		'class_submit' => 'inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded',
		'logged_in_as' => '',
		'comment_notes_after' => '',
	);
	comment_form( $comment_form_args );
	?>

</div>
