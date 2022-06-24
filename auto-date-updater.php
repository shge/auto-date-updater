<?php
/*
Plugin Name: Auto Date Updater
Description: Update published / modified dates of all posts regularly. (Requires WP-Cron)
Version:     0.0.1
Author:      Soga Shigemura
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function adu_update_date() {
	remove_filter( 'content_save_pre', 'wp_filter_post_kses' );  // Prevent auto formatting

	$date_format       = 'Y-m-d H:i:s';
	$date1             = strtotime( "-14 hour");
	$date2             = strtotime( "-8 hour");
	$posts = get_posts( [ 'numberposts' => - 1, 'post_status' => 'publish', 'post_type' => 'post' ] );
	add_filter( 'wp_insert_post_data', 'adu_set_post_modified' );
	foreach ( $posts as $post ) {
		$random_date = mt_rand( $date1, $date2 );
		$post_date_gmt = date( $date_format, $random_date );
		$post_date = date( $date_format, $random_date+32400 );

		$update = [
			'ID'            => $post->ID,
			'post_date'     => $post_date,
			'post_date_gmt' => $post_date_gmt,
		];

		wp_update_post( $update );

	}

	add_filter( 'content_save_pre', 'wp_filter_post_kses' );  // Enable auto formatting again for unknown users
}

function adu_set_post_modified( $data ) {
	// $post_modified_gmt = strtotime($data['post_date_gmt']) + mt_rand(1, 3600);
	$post_modified_gmt = strtotime($data['post_date_gmt']);
	$data['post_modified'] = date('Y-m-d H:i:s', $post_modified_gmt+32400);
	$data['post_modified_gmt'] = date('Y-m-d H:i:s', $post_modified_gmt);
	return $data;
}
