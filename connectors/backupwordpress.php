<?php

class WP_Stream_Connector_BackUpWordPress extends WP_Stream_Connector {

	/**
	 * Connector slug
	 *
	 * @var string
	 */
	public static $name = 'backupwordpress';

	/**
	 * Actions registered for this connector
	 *
	 * @var array
	 */
	public static $actions = array(
		'hmbkp_action_complete'
	);

	/**
	 * Return translated connector label
	 *
	 * @return string Translated connector label
	 */
	public static function get_label() {
		return __( 'BackUpWordPress', 'backupwordpress' );
	}

	/**
	 * Return translated action labels
	 *
	 * @return array Action label translations
	 */
	public static function get_action_labels() {
		return array(
			'backup_started'    => __( 'Backup started', 'backupwordpress-stream' )
		);
	}

	/**
	 * Return translated context labels
	 *
	 * @return array Context label translations
	 */
	public static function get_context_labels() {
		return array(
			'backups' => __( 'BackUpWordPress', 'backupwordpress-stream' ),
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

	public static function callback_hmbkp_action_complete( $action, $service ) {

		self::log(
			$service->get_status(),
			array(),
			$service->get_id(),
			array( 'backups' => 'Backups' ),
			null
		);
	}

}
