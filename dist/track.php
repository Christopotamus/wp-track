<?php

$transPix = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QgLDwcqaRu2awAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAAC0lEQVQI12NgAAIAAAUAAeImBZsAAAAASUVORK5CYII=");
header("Content-Type: image/png");

if  ( isset($_GET['wptrack_id']) ) {
  // header("Content-Length: " . filesize($transPix));
  global $wpdb;
  
  $table = $wpdb->prefix . 'wp_track';

  $trackingId = $_GET['wptrack_id'];
  $user_ip = $_SERVER['REMOTE_ADDR'];

  $query = new WP_Query( array( 'post_type' => 'wptrack_tracking', 'meta_key' => 'wptrack_tracking_id', 'meta_value' => $trackingId ) );
  
  $wpdb->insert(
    $table,
    array(
      'time' => current_time('mysql'),
      'ip_address' => $user_ip,
      'wp_track_id' => $trackingId,
    )
  );

  if( $query->have_posts()  ) {
    // echo "Here's the tracking info<br/>";
    while ( $query->have_posts() ) {
      $query->the_post();
      // echo "Title:". get_the_title(). "<br/>"; 
      // echo 'Tracking id is: ' . $trackingId;
      echo $transPix;
    }
  }
} else {
  
  echo $transPix;
}
?>
