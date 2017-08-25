<?php 
 /*
   Plugin Name: WP Track
   Plugin URI: http://wp-track.com
   Description: A Plugin that generates and tracks tracking pixels for any usage
   Version: 1.0
   Author: Christopher Budd
   Author URI: http://mynameischrisbuddandstuff.com
   License: MIT
   
   The MIT License (MIT)
   
   Permission is hereby granted, free of charge, to any person obtaining a
   copy of this software and associated documentation files (the "Software"),
   to deal in the Software without restriction, including without limitation
   the rights to use, copy, modify, merge, publish, distribute, sublicense,
   and/or sell copies of the Software, and to permit persons to whom the Software
   is furnished to do so, subject to the following conditions:
 
   The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 
   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
   OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
   THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
   FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
   IN THE SOFTWARE.
 
 
 */

function wp_track_initialize_post_Types() {
  register_post_type('wptrack_tracking',
    [
      'labels'      => [
        'name'          => __('Tracking Pixels'),
        'singular_name' => __('Tracking Pixel'),
      ],
      'public'      => true,
      'has_archive' => false,
      'rewrite'     => ['slug' => 'wptrack_tracking'],
      'supports' => array('title', 'wptrack_gform_id', 'wptrack_tracking_id', 'last_viewed', 'last_viewed_by'),
    ]
  );

}

function setup_wp_track_table() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();

  $table_name = $wpdb->prefix . "wp_track";
  
  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    ip_address text NOT NULL,
    wp_track_id text NOT NULL,
    PRIMARY KEY (id) 
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}
function init_wp_track_metaboxes() {
  
}

function wptrack_custom_meta_boxes() {

  if ( class_exists("GFForms" ))  {
    add_meta_box('wptrack_gform_id', 'GravityForms ID', 'wptrack_gform_id_box_html', 'wptrack_tracking');
  }
  add_meta_box('wptrack_tracking_id', 'Tracking Code', 'wptrack_tracking_id_box_html', 'wptrack_tracking');
  add_meta_box('wptrack_tracking_email', 'Alert Email', 'wptrack_tracking_email_box_html', 'wptrack_tracking');
  add_meta_box('wptrack_tracking_html', 'Tracking Beacons', 'wptrack_tracking_html', 'wptrack_tracking');
}
function wptrack_tracking_id_box_html($post)
{
  if( $post->ID ) {
    $tracking_id = get_post_meta($post->ID, 'wptrack_tracking_id', true);
  } 
  if (! $tracking_id ) { 
    $tracking_id = 'This will generate after you save';
  }
  $trackingURL = get_site_url()."/wptrack.png?wptrack_id=".htmlspecialchars($tracking_id);
  $trackingImgTag = htmlspecialchars("<img src=".$trackingURL."/>");
  wp_nonce_field( 'wptrack_tracking_id_save', 'wptrack_tracking_id_nonce' );
    ?>
    <div>
      <label for="wptrack_tracking_id">Tracking ID:</label>
      <input name="wptrack_tracking_id" disabled id="wptrack_tracking_id" class="postbox" type="text" value="<?php echo htmlspecialchars($tracking_id) ?>" />
      <br />
      <p>To use this tracking pixel, please embed the following code in an HTML formatted email or webpage</p>
      <code><?php echo $trackingImgTag; ?></code>
      <p>You may also use the URL directly however you see fit<br /><?php echo $trackingURL ?></p>
    </div>
    <?php
}
function wptrack_tracking_email_box_html($post)
{
  $tracking_email = get_post_meta($post->ID, 'wptrack_tracking_email', true);
  $tracking_email_enabled = get_post_meta($post->ID, 'wptrack_tracking_email_enabled', true);

  wp_nonce_field( 'wptrack_tracking_email_save', 'wptrack_tracking_email_nonce' );
  wp_nonce_field( 'wptrack_tracking_email_enabled_save', 'wptrack_tracking_email_enabled_nonce' );
    ?>
    <div>
      <label for="wptrack_tracking_email">Email Alerts:</label>
      <input name="wptrack_tracking_email" id="wptrack_tracking_email" class="postbox" type="text" value="<?php echo htmlspecialchars($tracking_email) ?>" />
      <br />
      <label for="wptrack_tracking_email_enabled">
        Send notification emails
        <input
          name="wptrack_tracking_email_enabled"
          id="wptrack_tracking_email_enabled"
          class="postbox"
          type="checkbox"
          value="yes"
          <?php if(isset($tracking_email_enabled)) {checked($tracking_email_enabled, 'yes');} ?>
        />
      </label>
    </div>
    <?php
}
function wptrack_gform_id_box_html($post)
{
  $value = get_post_meta($post->ID, 'wptrack_gform_id', true);
  wp_nonce_field( 'wptrack_gform_id_save', 'wptrack_gform_id_nonce' );
    ?>
    <div>
      <label for="wptrack_gform_id">gform_id</label>
      <input name="wptrack_gform_id" id="wptrack_gform_id" class="postbox" type="text" value="<?php echo htmlspecialchars($value) ?>" />
    </div>
    <?php
}
function wptrack_tracking_html($post){
  global $wpdb;
  $table = $wpdb->prefix . 'wp_track';
  if( $post->ID ) {
    $tracking_id = get_post_meta($post->ID, 'wptrack_tracking_id', true);
    $results = $wpdb->get_results( "SELECT * FROM $table WHERE wp_track_id = '$tracking_id';");
    $value = get_post_meta($post->ID, 'wptrack_gform_id', true);
    if ( $tracking_id) {

      ?>
      <ul>
        <?php
          for ($i = 0; $i < count($results); $i++) {
            $tz = get_option('timezone_string');
            $time = new DateTime($results[$i]->time);
            $time->setTimezone(new DateTimeZone($tz));
          ?>
            <li>
              Viewed at <b><?php echo $time->format("Y-m-d H:i:s")?></b> by <b><?php echo htmlspecialchars($results[$i]->ip_address) ?></b>
            </li>
        <?php
          }
        ?>
      </ul>
      <?php
    }
  } 
}
function wptrack_save_postdata($post_id)
{
  $tracking_id = get_post_meta($post_id, 'wptrack_tracking_id', true);

  if (array_key_exists('wptrack_gform_id', $_POST)) {
    if (  isset( $_POST['wptrack_gform_id_nonce'])
      &&  wp_verify_nonce( $_POST['wptrack_gform_id_nonce'], 'wptrack_gform_id_save' )
    ) {
      update_post_meta(
        $post_id,
        'wptrack_gform_id',
        $_POST['wptrack_gform_id']
      );
    }
  }
  if (array_key_exists('wptrack_tracking_email', $_POST)) {
    if (  isset( $_POST['wptrack_tracking_email_nonce'])
      &&  wp_verify_nonce( $_POST['wptrack_tracking_email_nonce'], 'wptrack_tracking_email_save' )
    ) {
      update_post_meta(
        $post_id,
        'wptrack_tracking_email',
        $_POST['wptrack_tracking_email']
      );
    }
  }
  if (  isset( $_POST['wptrack_tracking_email_enabled_nonce'])
    &&  wp_verify_nonce( $_POST['wptrack_tracking_email_enabled_nonce'], 'wptrack_tracking_email_enabled_save' )
  ) {
    $enabled = (isset($_POST['wptrack_tracking_email_enabled'])) ? 'yes' : '';
    update_post_meta(
      $post_id,
      'wptrack_tracking_email_enabled',
      $enabled
    );
  }
  if( !$tracking_id ) { 
    if (  isset( $_POST['wptrack_tracking_id_nonce'])
      &&  wp_verify_nonce( $_POST['wptrack_tracking_id_nonce'], 'wptrack_tracking_id_save' ) )
    {
      //generate a UUID
      $tracking_id = uniqid();
      update_post_meta(
        $post_id,
        'wptrack_tracking_id',
        $tracking_id
      );
    }
  }
}


// flush_rules() if our rules are not yet included
function wptrack_flush_rules(){
    $rules = get_option( 'rewrite_rules' );

    if ( ! isset( $rules['track/(.+?)'] ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}

// Adding a new rule
function wptrack_insert_rewrite_rules( $rules )
{
    $newrules = array();
    $newrules['^wptrack.png$'] = 'index.php?wptrack_id=$matches[1]';
    return $newrules + $rules;
}

// Adding the id var so that WP recognizes it
function wptrack_insert_query_vars( $vars )
{
    array_push($vars,'wptrack_id');
    return $vars;
}

function wptrack_parse_request (&$wp) {
  if(array_key_exists('wptrack_id', $wp->query_vars)) {
    include( dirname( __FILE__  ) . '/track.php');
    exit();
  } 
}

add_filter( 'rewrite_rules_array','wptrack_insert_rewrite_rules' );
add_filter( 'query_vars','wptrack_insert_query_vars' );
add_action( 'wp_loaded','wptrack_flush_rules' );
add_action('parse_request', 'wptrack_parse_request');

add_action('init', 'wp_track_initialize_post_Types');
add_action('admin_menu', 'init_wp_track_metaboxes');
add_action('add_meta_boxes', 'wptrack_custom_meta_boxes');
add_action('save_post', 'wptrack_save_postdata');

register_activation_hook( __FILE__, 'setup_wp_track_table' );
if ( class_exists("GFForms" ))  {
  require_once('gf-wp-track.php');
}
?>
