<?php 
GFForms::include_addon_framework();

define( 'GF_WP_TRACK_VERSION', '2.0' );

add_action( 'gform_loaded', array( 'GF_WPTrack_Bootstrap', 'load' ), 5 );

class GF_WPTrack_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once('gf-wp-track-add-on.php');

        GFAddOn::register( 'GFWPTrack' );
    }

}


add_filter('gform_pre_send_email', 'insert_wp_tracking_code', 10, 4);

?>
