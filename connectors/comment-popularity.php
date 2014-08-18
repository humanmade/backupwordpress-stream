<?php

class WP_Stream_Connector_Comment_Popularity extends WP_Stream_Connector {

	/**
	 * Connector slug
	 *
	 * @var string
	 */
	public static $name = 'comment-popularity';

	/**
	 * Actions registered for this connector
	 *
	 * @var array
	 */
	public static $actions = array(
		'hmn_cp_comment_vote'
	);

	/**
	 * Return translated connector label
	 *
	 * @return string Translated connector label
	 */
	public static function get_label() {
		return __( 'Comment Popularity', 'comment-popularity' );
	}

	/**
	 * Return translated action labels
	 *
	 * @return array Action label translations
	 */
	public static function get_action_labels() {
		return array(
			'voted'    => __( 'Voted', 'comment-popularity-stream' )
		);
	}

	/**
	 * Return translated context labels
	 *
	 * @return array Context label translations
	 */
	public static function get_context_labels() {
		return array(
			'comments' => __( 'Comment Popularity', 'comment-popularity-stream' ),
		);
	}

	/**
	 * Add action links to Stream drop row in admin list screen
	 *
	 * @filter wp_stream_action_links_{connector}
	 * @param  array $links      Previous links registered
	 * @param  int   $record     Stream record
	 * @return array             Action links
	 */
	public static function action_links( $links, $record ) {

		return $links;
	}

	/**
	 * Fetches the comment author and returns the specified field.
	 *
	 * This also takes into consideration whether or not the blog requires only
	 * name and e-mail or that users be logged in to comment. In either case it
	 * will try to see if the e-mail provided does belong to a registered user.
	 *
	 * @param  object|int  $comment  A comment object or comment ID
	 * @param  string      $field    What field you want to return
	 * @return int|string  $output   User ID or user display name
	 */
	public static function get_comment_author( $comment, $field = 'id' ) {
		$comment = is_object( $comment ) ? $comment : get_comment( absint( $comment ) );

		$req_name_email = get_option( 'require_name_email' );
		$req_user_login = get_option( 'comment_registration' );

		$user_id   = 0;
		$user_name = __( 'Guest', 'comment-popuarity-stream' );

		if ( $req_name_email && isset( $comment->comment_author_email ) && isset( $comment->comment_author ) ) {
			$user      = get_user_by( 'email', $comment->comment_author_email );
			$user_id   = isset( $user->ID ) ? $user->ID : 0;
			$user_name = isset( $user->display_name ) ? $user->display_name : $comment->comment_author;
		}

		if ( $req_user_login ) {
			$user      = wp_get_current_user();
			$user_id   = $user->ID;
			$user_name = $user->display_name;
		}

		if ( 'id' === $field ) {
			$output = $user_id;
		} elseif ( 'name' === $field ) {
			$output = $user_name;
		}

		return $output;
	}


	public static function callback_hmn_cp_comment_vote( $user_id, $comment_id, $vote ) {

		$comment = get_comment( $comment_id );
		$user_name    = self::get_comment_author( $comment, 'name' );
		$post_id      = $comment->comment_post_ID;
		$post_title   = ( $post = get_post( $post_id ) ) ? "\"$post->post_title\"" : __( 'a post', 'comment-popularity-stream' );

		self::log(
			_x(
				'%1$s\'s comment on %2$s was just %3$sd',
				'1: Comment author, 2: Post name, 3: Vote Type',
				'comment-popularity-stream'
			),
			compact( 'user_name', 'post_title', 'vote' ),
			$comment_id,
			array( 'comment' => $vote )
		);
	}

}
