<?php
/*
Plugin Name: Auto Date Updater
Description: 公開済みのすべての記事の投稿日時と更新日時を前日の11:00〜17:00にランダムで分散させます。処理は毎日深夜1:00に実行されます。停止するにはプラグインを無効にしてください。
Version:     1.0
Author:      Soga Shigemura
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function adu_activation() {
    // Schedules a recurring event.
    $run_timestamp = strtotime("16:00:00");  // 1:00 am JST
    if($run_timestamp < time()) {
        $run_timestamp = strtotime("+1 day", $run_timestamp);
    }
    wp_schedule_event($run_timestamp, 'daily', 'adu_update_date_hook');
}
function adu_deactivation() {
    // Unschedules all events attached to the hook with the specified arguments.
    wp_clear_scheduled_hook('adu_update_date_hook');
}
register_activation_hook(__FILE__, 'adu_activation');
register_deactivation_hook(__FILE__, 'adu_deactivation');

add_action( 'adu_update_date_hook', 'adu_update_date' );


function adu_update_date() {

    global $wpdb;

    $date_format       = 'Y-m-d H:i:s';
    $date1             = strtotime("2:00:00");  // 11:00 am JST
    $date2             = strtotime("8:00:00");  // 5:00 pm = 17:00 JST
    if($date2 > time()) {
        $date1 = strtotime("-1 day", $date1);
        $date2 = strtotime("-1 day", $date2);
    }

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
