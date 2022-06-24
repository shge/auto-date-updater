<?php
/*
Plugin Name: Auto Date Updater
Description: Update published / modified dates of all posts regularly. (Requires WP-Cron)
Version:     0.1
Author:      Soga Shigemura
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function adu_update_date() {

    global $wpdb;

    $date_format       = 'Y-m-d H:i:s';
    $date1             = strtotime( "-14 hour");
    $date2             = strtotime( "-8 hour");

    $posts = get_posts( [ 'numberposts' => -1, 'post_status' => 'publish', 'post_type' => 'post' ] );

    foreach ( $posts as $post ) {

        $random_date = mt_rand( $date1, $date2 );

        $post_date_gmt = date( $date_format, $random_date );
        $post_date = date( $date_format, $random_date + 32400 );

        $data = [
            'post_date'         => $post_date,
            'post_date_gmt'     => $post_date_gmt,
            'post_modified'     => $post_date,
            'post_modified_gmt' => $post_date_gmt
        ];
        $format = [ '%s', '%s', '%s', '%s' ];
        $where = [ 'id' => $post->ID ];
        $where_format = [ '%d' ];

        $wpdb->update( $wpdb->posts, $data, $where, $format, $where_format );

    }

}
