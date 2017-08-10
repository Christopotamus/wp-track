<?php
if  ( isset($_GET['wptrack_id']) ) {
  $trackingId = $_GET['wptrack_id'];

  $query = new WP_Query( array( 'post_type' => 'wptrack_tracking', 'meta_key' => 'wptrack_tracking_id', 'meta_value' => $trackingId ) );
  if( $query->have_posts()  ) {
    echo "Here's the tracking info<br/>";
    while ( $query->have_posts() ) {
      $query->the_post();
      echo "Title:". get_the_title(). "<br/>"; 
      echo 'Tracking id is: ' . $trackingId;
    }
  }
}
?>
