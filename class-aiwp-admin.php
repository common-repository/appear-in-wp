<?php
/**
 * Amen
 *
 * @package   Appear In WP
 * @author    UaMV
 * @license   GPL-2.0+
 * @link      http://vandercar.net/wp
 * @copyright 2013 UaMV
 */

/**
 * Appear_In_WP_Admin
 *
 * Handles the admin section
 *
 * @package Appear In WordPress
 * @author  UaMV
 */
class Appear_In_WP_Admin {

	/*---------------------------------------------------------------------------------*
	 * Attributes
	 *---------------------------------------------------------------------------------*/

	/**
	 * Instance of this class.
	 *
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Custom plugin settings
	 *
	 * @since    1.0
	 *
	 * @var      object
	 */
	protected $options;

	/*---------------------------------------------------------------------------------*
	 * Consturctor / The Singleton Pattern
	 *---------------------------------------------------------------------------------*/

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0
	 */
	private function __construct() {

		global $pagenow;

		// check if plugin has updated and respond accordingly
		add_action( 'admin_init', array( $this, 'check_plugin_update' ) );

		// load activation notice to guide users to the next step
		add_action( 'admin_notices', array( $this, 'display_plugin_activation_message' ) );

		// add notices on plugin activation
		register_activation_hook( AIWP_DIR_PATH . 'appear-in-wp.php', array( $this, 'add_wpsn_notices' ) );

		// schedule crons on plugin activation
		register_activation_hook( AIWP_DIR_PATH . 'appear-in-wp.php', array( $this, 'schedule_cron' ) );

		// unschedule crons on plugin de-activation
		register_deactivation_hook( AIWP_DIR_PATH . 'appear-in-wp.php', array( $this, 'unschedule_cron' ) );

		// remove active plugin marker
		register_deactivation_hook( AIWP_DIR_PATH . 'appear-in-wp.php', array( $this, 'remove_activation_marker' ) );

		if ( 'options-media.php' == $pagenow || 'options.php' == $pagenow ) {
			// retrieve custom plugin settings
			$this->options = get_option( 'aiwp_settings', array() );

			// initialize custom room name
			$this->options['room'] = isset( $this->options['room'] ) ? $this->options['room'] : '';

			// register the settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		// call to enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'add_stylesheets_and_javascript' ) );

	} // end constructor

	/*---------------------------------------------------------------------------------*
	 * Public Functions
	 *---------------------------------------------------------------------------------*/

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		} // end if

		return self::$instance;

	} // end get_instance

	/**
	 * Displays a plugin message as soon as the plugin is activated.
	 *
	 * @since    1.0
	 */
	public function display_plugin_activation_message() {

		if ( ! get_option( 'aiwp_activated' ) ) {

			// Show the notice
			$html = '<div class="updated">';
				$html .= '<a href="http://appear.in"><img src="' . AIWP_DIR_URL . 'appearin-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.4em; margin-top: 0.4em" /></a>';
				$html .= '<p style="display: inline-block">';
					$html .= __( "<strong>Yes!</strong> One more thing. <a href='options-media.php#aiwp-section'>Click here</a> to customize your appear.in WP settings.", 'aiwp-locale' );
				$html .= '</p>';
			$html .= '</div><!-- /.updated -->';

			echo $html;

			update_option( 'aiwp_activated', TRUE );

		} else if ( 'plugins' == get_current_screen()->id || 'options-media' == get_current_screen()->id ) {

			// Show the notice
			$html = '<div class="error">';
				$html .= '<a href="http://appear.in"><img src="' . AIWP_DIR_URL . 'appearin-logo.png" style="float: left; width: 2em; height: 2em; margin-right: 0.4em; margin-top: 0.4em" /></a>';
				$html .= '<p style="display: inline-block">';
					$html .= __( "<strong>Note:</strong> The appear.in embed & developer SDK is being deprecated. Thus, the appear.in WP plugin can no longer serve to embed rooms. <a href=\"https://wordpress.org/support/topic/appear-in-embed-developer-sdk-deprecation/\">Read More</a>", 'aiwp-locale' );
				$html .= '</p>';
			$html .= '</div><!-- /.updated -->';

			echo $html;

		} // end if

	} // end display_plugin_activation_message

	/**
	 * Deletes activation marker so it can be displayed when the plugin is reinstalled or reactivated
	 *
	 * @since    1.0
	 */
	public static function remove_activation_marker() {

		delete_option( 'aiwp_activated' );

	} // end remove_activation_marker

	/**
	 * Check for plugin update and updates notices
	 *
	 * @since    1.0
	 */
	public function check_plugin_update() {

		// if current version greater than previous version stored in database, then update notices
		(float) AIWP_VERSION > (float) get_option( 'aiwp_db_version' ) ? $this->add_wpsn_notices() : FALSE;

	} // end check_plugin_update

	/**
	 * Define WP Side Notices for use in plugin
	 *
	 * @since    1.0
	 */
	public function add_wpsn_notices() {

		// Initialize a new side notice
		$wpsn = new WP_Side_Notice( 'aiwp' );

		// Define the notices
		$aiwp_notices = array(
			'ai-info' => array(
				'name' => 'ai-info',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => 'none',
				'content' => '<a href="http://wordpress.org/plugins/appear-in-wp">appear.in WP</a> plugin developed by <a href="http://typewheel.xyz/">Joshua Vandercar</a>.',
				'style' => array( 'height' => '72px', 'color' => '#85ae9b', 'icon' => 'f348' ),
				'location' => array( 'options-media.php' ),
				),
			'ai-support' => array(
				'name' => 'ai-support',
				'trigger' => TRUE,
				'time' => time() - 5,
				'dismiss' => 'none',
				'content' => 'Require assistance? Visit our <a href="http://wordpress.org/support/plugin/appear-in-wp/">support forum</a>.&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;All is well? Consider <a href="http://wordpress.org/support/view/plugin-reviews/appear-in-wp#postform">reviewing the plugin</a>.',
				'style' => array( 'height' => '72px', 'color' => '#85ae9b', 'icon' => 'f240' ),
				'location' => array( 'options-media.php' ),
				),
			);

		// remove the old notices
		method_exists( 'WP_Side_Notice', 'remove' ) ? $wpsn->remove() : FALSE;

		// Add each notice
		foreach ( $aiwp_notices as $notice => $args ) {
			$wpsn->add( $args );
		}

		// Update the aiwp database version
		update_option( 'aiwp_db_version', AIWP_VERSION );

	} // end add_wpsn_notices

	/**
	 * Schedules the cron for expiring and resetting a daily public room name.
	 *
	 * @since    1.0
	 */
	public function schedule_cron() {

		// on plugin activation, get random room code
		$random_room = aiwp_random_room();

		// assign to public room
		add_option( 'aiwp_public_room', $random_room );

		// schedule default public room name to reset each day at midnight
		wp_schedule_event( strtotime('today midnight'), 'daily', 'expireroom' );

	} // end schedule_cron

	/**
	 * Unschedules the cron when plugin is disabled
	 *
	 * @since    1.0
	 */
	public function unschedule_cron() {

		// get the cron array
		$crons = _get_cron_array();

		// loop through the cron schedule
		foreach ( $crons as $time => $cron_data ) {

			// loop through the cron events
			foreach ( $cron_data as $cron_event => $data ) {

				// if it's the expireroom event, unschedule it
				'expireroom' == $cron_event ? wp_unschedule_event( $time, $cron_event ) : FALSE;

			} // end foreach

		} // end foreach

		// remove the current room code
		delete_option( 'aiwp_public_room' );

	} // end unschedule cron

	/**
	 * Registers the admin stylesheets and scripts
	 *
	 * @since    1.0
	 */
	public function add_stylesheets_and_javascript() {

		//wp_enqueue_script( 'wp-color-picker' );
        // load the minified version of custom script
        wp_enqueue_script( 'aiwp-admin-js', AIWP_DIR_URL . 'aiwp-admin.js', array( 'jquery', 'wp-color-picker' ), AIWP_VERSION, true );
        wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style( 'aiwp-admin-style', AIWP_DIR_URL . 'aiwp-admin.css', array(), AIWP_VERSION, 'screen' );

	}

	/**
	 * Registers the settings fields with the WordPress Settings API.
	 *
	 * @since    1.0
	 */
	public function register_settings() {

		// First, register a settings section
		add_settings_section( 'aiwp', 'appear.in', array( $this, 'display_section' ), 'media' );

		// Then, register the settings for the fields
		register_setting( 'media', 'aiwp_settings', array( $this, 'validate_options' ) );

		// Now introduce the settings fields
		add_settings_field(
			'aiwp_settings',
			__( '<div id="aiwp-section"><a href="https://appear.in"><img src="' . AIWP_DIR_URL . 'appearin-logo.png" style="border-radius:100%;"/></a></div>' , 'aiwp-locale' ),
			array( $this, 'display_settings' ) ,
			'media',
			'aiwp'
		);

	} // end register_settings

	/**
	 * Renders the intro to the settings section.
	 *
	 * @since    1.0
	 */
	public function display_section() {

		// get the aiwp side notices setting height of 500px
		$notices = new WP_Side_Notice( 'aiwp', 150 );

		// display the notices
		$notices->display();

	}

	/**
	 * Renders the setting fields.
	 *
	 * @since    1.0
	 */
	public function display_settings() {

		// build the fields
		$html = '<fieldset>';

		// add button color picker
		$html .= '<label id="aiwp-button-color"><span>' . __( 'Room Buttons: ', 'aiwp-locale' ) . '</span>';
			$html .= '<input type="text" id="appear_in_button_color" name="aiwp_settings[color]" value="' . $this->options['color'] . '"></input>';
		$html .= '</label>';

		$html .= '<br />';

		// allow default public room name
		$html .= '<label>' . __( 'Public Room Name:', 'aiwp-locale' );
			$html .= ' <input type="text" id="appear_in_room" name="aiwp_settings[room]" value="' . $this->options['room'] . '"></input>';
		$html .= '</label>';

		$html .= '<br />';

		$html .= ' <span class="description">' . __( '(if not defined here or in shortcode, public room is given random name that expires daily)' , 'aiwp-locale' ) . '</span>';

		$html .= '</fieldset>';

		echo $html;

	} // end display_settings

	public function validate_options( $fields ) {

		$valid_fields = $fields;

		// Validate Background Color
		$color = trim( $fields['color'] );
		$color = strip_tags( stripslashes( $color ) );

		// Check if is a valid hex color
		if( FALSE === $this->is_color( $color ) ) {

		    // Set the error message
		    add_settings_error( 'aiwp_settings', 'aiwp_color_error', 'Insert a valid color for appear.in button', 'error' ); // $setting, $code, $message, $type

		    // Get the previous valid value
		    $valid_fields['color'] = $this->options['color'];

		} else {

		    $valid_fields['color'] = $color;

		}

		return apply_filters( 'validate_options', $valid_fields, $fields);

	}

	/**
	* Function that will check if value is a valid HEX color.
	*/
	public function is_color( $value ) {

		if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #
		    return true;
		}

		return false;
	}

} // end class
