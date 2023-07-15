<?php
class AASM_Migration_Status_Controller {

	public static function error( $title, $message ) {
		self::log( array( 'type' => 'error', 'title' => $title, 'message' => $message ) );
	}

	public static function info( $message ) {
		self::log( array( 'type' => 'info', 'message' => $message ) );
	}

	public static function download( $message ) {
		self::log( array( 'type' => 'download', 'message' => $message ) );
	}

	public static function done( $title, $message ) {
		self::log( array( 'type' => 'done', 'title' => $title, 'message' => $message ) );
	}

	public static function progress( $percent ) {
		self::log( array( 'type' => 'progress', 'percent' => $percent ) );
	}

	public static function server_cannot_decrypt( $message ) {
		self::log( array( 'type' => 'server_cannot_decrypt', 'message' => $message ) );
	}

	public static function log( $data ) {
		update_option( AASM_MIGRATION_STATUS, $data );
	}
}
