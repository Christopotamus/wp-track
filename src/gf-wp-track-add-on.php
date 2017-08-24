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
GFForms::include_addon_framework();

class GFWPTrack extends GFAddOn {

    protected $_version = GF_WP_TRACK_VERSION;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'wp-track';
    protected $_path = 'wp-track/wp-track.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms WP-Track Plugin';
    protected $_short_title = 'WP Track';

    private static $_instance = null;

    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new GFWPTrack();
        }

        return self::$_instance;
    }

    public function init() {
        parent::init();
        add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
        add_filter('gform_notification', 'insert_wp_tracking_code', 10, 4);
    }

    public function scripts() {
        $scripts = array(
            array(
                'handle'  => 'my_script_js',
                'src'     => $this->get_base_url() . '/js/my_script.js',
                'version' => $this->_version,
                'deps'    => array( 'jquery' ),
                'strings' => array(
                    'first'  => esc_html__( 'First Choice', 'wptrack' ),
                    'second' => esc_html__( 'Second Choice', 'wptrack' ),
                    'third'  => esc_html__( 'Third Choice', 'wptrack' )
                ),
                'enqueue' => array(
                    array(
                        'admin_page' => array( 'form_settings' ),
                        'tab'        => 'wptrack'
                    )
                )
            ),

        );

        return array_merge( parent::scripts(), $scripts );
    }

    public function styles() {
        $styles = array(
            array(
                'handle'  => 'my_styles_css',
                'src'     => $this->get_base_url() . '/css/my_styles.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array( 'field_types' => array( 'poll' ) )
                )
            )
        );

        return array_merge( parent::styles(), $styles );
    }

    function form_submit_button( $button, $form ) {
        $settings = $this->get_form_settings( $form );
        if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
            $text   = $this->get_plugin_setting( 'mytextbox' );
            $button = "<div>{$text}</div>" . $button;
        }

        return $button;
    }

    public function plugin_page() {
        echo 'This page appears in the Forms menu';
    }

    public function plugin_settings_fields() {
        return array(
            array(
                'title'  => esc_html__( 'WPTrack Settings', 'wptrack' ),
                'fields' => array(
                    array(
                        // 'name'              => 'wptrack',
                        'tooltip'           => esc_html__( 'This is the tooltip', 'wptrack' ),
                        'label'             => esc_html__( 'This is the label', 'wptrack' ),
                        'type'              => 'text',
                        'class'             => 'small',
                        'feedback_callback' => array( $this, 'is_valid_setting' ),
                    )
                )
            )
        );
    }
    
    public function form_settings_fields( $form ) {
      $notifications = array_map('mapNotificationsToCheckboxes', $form['notifications']);

      return array(
        array(
          'title'  => esc_html__( 'WPTrack Form Settings', 'wptrack' ),
          'fields' => array(
            array(
              'label'   => esc_html__( 'Enable Tracking', 'wptrack' ),
              'type'    => 'checkbox',
              'name'    => 'enabled',
              'tooltip' => esc_html__( 'This enables tracking on this form', 'wptrack' ),
              'choices' => array(
                array(
                  'label' => esc_html__( 'Enabled', 'wptrack' ),
                  'name'  => 'enabled',
                  'default_value' => 0,
                ),
              ),
            ),
            array(
              'name'    => 'wptrack_notifications',
              'label'   => esc_html__( 'Tracking Notifications', 'wptrack' ),
              'type'    => 'checkbox',
              'tooltip' => esc_html__( 'Select your notifications here', 'wptrack' ),
              'choices' => $notifications,
            ),
          ),
        ),
      );
    }

    public function settings_my_custom_field_type( $field, $echo = true ) {
        echo '<div>' . esc_html__( 'My custom field contains a few settings:', 'wptrack' ) . '</div>';

        // get the text field settings from the main field and then render the text field
        $text_field = $field['args']['text'];
        $this->settings_text( $text_field );

        // get the checkbox field settings from the main field and then render the checkbox field
        $checkbox_field = $field['args']['checkbox'];
        $this->settings_checkbox( $checkbox_field );
    }

    public function is_valid_setting( $value ) {
        return strlen( $value ) < 10;
    }

}
function mapNotificationsToCheckboxes($notification) {
  $note = array(
    'label' => esc_html__( $notification['name'], 'wptrack' ),
    'name' => preg_replace('/\s+/','',$notification['id']),
    'default_value' => 0,
    'value' => 0,
  );
  return $note;
}
function insert_wp_tracking_code($notification, $form, $entry) {
  global $wpdb;
  // get activated notifications for form.
  $settings = (new GFWPTrack())->get_form_settings( $form );
  error_log(json_encode($settings));
  error_log(json_encode($notification));
  error_log(isset($settings[$notification['id']]));
  if( isset( $settings['enabled'] )&& $settings['enabled'] == '1'  ) {
    if ( isset( $notification['id']) && isset($settings[$notification['id']])
                && $settings[$notification['id']] == '1' ) 
    {
      error_log("Creating the post");
      $defaults = array(
        'post_title' => wp_strip_all_tags($notification['to']),
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'wptrack_tracking',
        'meta_input' => array (
          'wptrack_gform_id' => $entry['id'],
          'wptrack_tracking_id' => uniqid(),
        )
      );
      $post = wp_insert_post($defaults); 
      
      $notification['message'] .= '<img src="'.get_site_url().'/wptrack.png?wptrack_id=599f5aaeb9c5f">';
    }
  }
  return $notification;
}
?>
