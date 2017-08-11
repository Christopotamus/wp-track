<?php

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
                        'name'              => 'wptrack',
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
                            ),
                        ),
                    ),
                    array(
                        'label'   => esc_html__( 'Notifications', 'wptrack' ),
                        'type'    => 'multiselect',
                        'name'    => 'notifications',
                        'tooltip' => esc_html__( 'This is the tooltip', 'wptrack' ),
                        'choices' => array(
                            array(
                                'label' => esc_html__( 'First Choice', 'wptrack' ),
                                'value' => 'first',
                            ),
                            array(
                                'label' => esc_html__( 'Second Choice', 'wptrack' ),
                                'value' => 'second',
                            ),
                            array(
                                'label' => esc_html__( 'Third Choice', 'wptrack' ),
                                'value' => 'third',
                            ),
                        ),
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
?>
