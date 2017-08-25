<?php
/*
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
$transPix = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QgLDwcqaRu2awAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAC0lEQVQI12NgAAIAAAUAAeImBZsAAAAASUVORK5CYII=");
header("Content-Type: image/png");

if  ( isset($_GET['wptrack_id']) ) {
  // header("Content-Length: " . filesize($transPix));
  global $wpdb;
  
  $table = $wpdb->prefix . 'wp_track';

  $trackingId = sanitize_text_field($_GET['wptrack_id']);
  $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);

  $query = new WP_Query( array( 'post_type' => 'wptrack_tracking', 'meta_key' => 'wptrack_tracking_id', 'meta_value' => $trackingId ) );
  
  $wpdb->insert(
    $table,
    array(
      'time' => current_time('mysql', true),
      'ip_address' => $user_ip,
      'wp_track_id' => $trackingId,
    )
  );

  if( $query->have_posts()  ) {
    // echo "Here's the tracking info<br/>";
    while ( $query->have_posts() ) {
      $query->the_post();
      $post = $query->post;
      $enabled = get_post_meta($post->ID, 'wptrack_tracking_email_enabled', true);
      if( isset($enabled) && $enabled == "yes" ) {
        $email = get_post_meta($post->ID, 'wptrack_tracking_email', true);
        $subject = "WPTrack: New view of ".get_the_title()." from: ". $user_ip;
        $message = "$user_ip just viewed your tracking pixel. <br /> <a href='".get_site_url()."/wp-admin/post.php?post=".$post->ID."&action=edit'>Click Here to view it</a>";

        add_filter('wp_mail_content_type', 'set_html_email_content_type');
        wp_mail($email, $subject, $message);
        remove_filter('wp_mail_content_type', 'set_html_email_content_type');
      }
      echo $transPix;
    }
  }
} else {
  
  echo $transPix;
}

function set_html_email_content_type() {
  return 'text/html';
}
?>
