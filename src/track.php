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

  $trackingId = $_GET['wptrack_id'];
  $user_ip = $_SERVER['REMOTE_ADDR'];

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
      // echo "Title:". get_the_title(). "<br/>"; 
      // echo 'Tracking id is: ' . $trackingId;
      echo $transPix;
    }
  }
} else {
  
  echo $transPix;
}
?>
