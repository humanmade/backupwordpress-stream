<?php
/**
 * Plugin Name: Stream Connector - BackUpWordPress
 * Depends: Stream, BackUpWordPress
 * Plugin URI: https://bwp.hmn.md
 * Description: View BackUpWordPress activity in your Stream dashboard
 * Version: 0.1.0
 * Author: Human Made Limited
 * Author URI: https://hmn.md/
 * License: GPLv2+
 * Text Domain: stream-connector-backupwordpress
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Human Made Ltd (https://hmn.md/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class WP_Stream_Connector_BackUpWordPress_Wrapper {

	/**
	 * Holds Stream plugin minimum version required
	 *
	 * @const string
	 */
	const STREAM_MIN_VERSION = '1.4.9';

	/**
	 * Holds this plugin version
	 * Used in assets cache
	 *
	 * @const string
	 */
	const VERSION = '0.1.0';

	/**
	 * Holds BackUpWordPress plugin minimum version required
	 *
	 * @const string
	 */
	const BACKUPWORDPRESS_MIN_VERSION = '3.0';

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load' ) );
	}

	/**
	 * Register the BackUpWordPress connector
	 */
	public function register_connector( $classes ) {
		include dirname( __FILE__ ) . '/connectors/backupwordpress.php';

		$classes[] = 'WP_Stream_Connector_BackUpWordPress';

		return $classes;
	}

	/**
	 * Load our classes, actions/filters, only if Stream is activated.
	 *
	 * @return void
	 */
	public function load() {
		add_action( 'all_admin_notices', array( __CLASS__, 'admin_notices' ) );

		if ( ! $this->is_dependency_satisfied() ) {
			return;
		}

		add_filter( 'wp_stream_connectors', array( $this, 'register_connector' ) );

		// Register to Stream updates
//		if ( class_exists( 'WP_Stream_Updater' ) ) {
//			WP_Stream_Updater::instance()->register( plugin_basename( __FILE__ ) );
//		}
	}

	/**
	 * Check if plugin dependencies are satisfied and add an admin notice if not
	 *
	 * @return bool
	 */
	public function is_dependency_satisfied() {
		$notices = array();

		if ( ! class_exists( 'WP_Stream' ) ) {
			$notices[] = array(
				'message'  => sprintf( __( '<strong>Stream BackUpWordPress Connector</strong> requires the <a href="%1$s" target="_blank">Stream</a> plugin to be installed and activated.', 'stream-connector-backupwordpress' ), esc_url( 'http://wordpress.org/plugins/stream/' ) ),
				'is_error' => true,
			);
		} elseif ( version_compare( WP_Stream::VERSION, self::STREAM_MIN_VERSION, '<' ) ) {
			$notices[] = array(
				'message'  => sprintf( __( 'Please <a href="%1$s" target="_blank">install Stream</a> version %2$s or higher for the <strong>Stream BackUpWordPress Connector</strong> plugin to work properly.', 'stream-connector-backupwordpress' ), esc_url( 'http://wordpress.org/plugins/stream/' ), self::STREAM_MIN_VERSION ),
				'is_error' => true,
			);
		}

		if ( ! class_exists( 'HM_Backup' ) ) {
			$notices[] = array(
				'message'  => sprintf( __( '<strong>Stream BackupWordPress Connector</strong> requires the <a href="%1$s" target="_blank">BackupWordPress</a> plugin to be installed and activated.', 'stream-connector-backupwordpress' ), esc_url( 'http://wordpress.org/plugins/stream/' ) ),
				'is_error' => true,
			);
		} elseif ( defined( 'HMBKP_VERSION' ) && version_compare( HMBKP_VERSION, self::BACKUPWORDPRESS_MIN_VERSION, '<' ) ) {
			$notices[] = array(
				'message'  => sprintf( __( 'Please <a href="%1$s" target="_blank">install BackUpWordPress</a> version %2$s or higher for the <strong>Stream BackUpWordPress Connector</strong> plugin to work properly.', 'stream-connector-backupwordpress' ), esc_url( 'http://wordpress.org/plugins/backupwordpress/' ), self::BACKUPWORDPRESS_MIN_VERSION ),
				'is_error' => true,
			);
		}

		if ( ! empty( $notices ) ) {
			foreach( $notices as $notice => $data ) {
				$message  = isset( $data['message'] ) ? $data['message'] : null;
				$is_error = isset( $data['is_error'] ) ? $data['is_error'] : null;

				self::notice( $message, $is_error );
			}

			return false;
		}

		return true;
	}

	/**
	 * Handle notice messages according to the appropriate context (WP-CLI or the WP Admin)
	 *
	 * @param string $message
	 * @param bool $is_error
	 * @return void
	 */
	public static function notice( $message, $is_error = true ) {
		if ( defined( 'WP_CLI' ) ) {
			$message = strip_tags( $message );
			if ( $is_error ) {
				WP_CLI::warning( $message );
			} else {
				WP_CLI::success( $message );
			}
		} else {
			self::admin_notices( $message, $is_error );
		}
	}

	/**
	 * Show an error or other message in the WP Admin
	 *
	 * @param string $message
	 * @param bool $is_error
	 * @return void
	 */
	public static function admin_notices( $message, $is_error = true ) {
		if ( empty( $message ) ) {
			return;
		}

		$class_name   = $is_error ? 'error' : 'updated';
		$html_message = sprintf( '<div class="%s">%s</div>', esc_attr( $class_name ), wpautop( $message ) );

		echo wp_kses_post( $html_message );
	}

}

$GLOBALS['WP_Stream_Connector_BackUpWordPress'] = new WP_Stream_Connector_BackUpWordPress_Wrapper;
