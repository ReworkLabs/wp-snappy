<?php 

/*
Plugin Name:       WP Snappy
Plugin URI:        https://github.com/imknight/wp-snappy
Description:       <a href="http://besnappy.com/">Snappy</a> WordPress Plugin
Version:           0.4
Author:            Knight
License:           GNU General Public License v2
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Domain Path:       /languages
Text Domain:       wp-snappy
GitHub Plugin URI: imknight/wp-snappy
GitHub Branch:     master
*/

class WP_Snappy {

	protected $wp_snappy_settings;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'wp_snappy_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'wp_snappy_add_menus' ) );
		$this->wp_snappy_settings = get_option('wp_snappy_settings');
		$this->wp_snappy_widget();
	}

	public function wp_snappy_admin_init(){
		
		$wp_snappy_settings = $this->wp_snappy_settings;

		//for color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		add_action(	'admin_footer', array( $this , 'wp_snappy_colorpicker') );

		register_setting( 'wp_snappy_settings', 'wp_snappy_settings', array( $this, 'wp_snappy_validate_options' ) );
		add_settings_section( 'wp_snappy_settings',__return_null(),'__return_false', 'wp_snappy_widget_section' );
		$settings = $this->get_snappy_settings();
			foreach ($settings as $id => $option){
				add_settings_field(
					'wp_snappy_settings[' . $option['id'] . ']',
					$option['name'],
					array( $this,'wp_snappy_settings_'.$option['type'].'_callback'),
					'wp_snappy_widget_section',
					'wp_snappy_settings',
					$option
				);
			}
	}

	public function wp_snappy_widget(){
		
		$wp_snappy_settings = $this->wp_snappy_settings;

		//setting not set yet so don't set the widget
		if ( empty($wp_snappy_settings['script_url']) OR empty($wp_snappy_settings['data_domain']) ) {
			return;
		}

		//if widget only display on frontend or both
		if ( $wp_snappy_settings['display'] == 'both' ||  $wp_snappy_settings['display'] == 'frontend' ) {
			add_action(	'wp_footer', array( $this , 'wp_snappy_widget_display') );
		}

		//if widget only display on admin or both
		if ( $wp_snappy_settings['display'] == 'both' ||  $wp_snappy_settings['display'] == 'admin' ) {
			add_action(	'admin_footer', array( $this , 'wp_snappy_widget_display') );
		}


	}

	public function wp_snappy_widget_display(){
		
		$wp_snappy_settings = $this->wp_snappy_settings;

		$widget = '<script src="'.$wp_snappy_settings['script_url'].'"';
		$widget .= ' data-domain="'.$wp_snappy_settings['data_domain'].'"';
		$widget .= ' data-title="'.$wp_snappy_settings['title'].'"';
		$widget .= ' data-position="'.$wp_snappy_settings['position'].'"';
		$widget .= ' data-contact="'.$wp_snappy_settings['contact'].'"';

		if ($wp_snappy_settings['prepoinfo'] == 1 && is_user_logged_in() ){
			$current_user = wp_get_current_user();
			$widget .= $current_user ? ' data-name="'.$current_user->display_name.'"' : '';
			$widget .= $current_user ? ' data-email="'.$current_user->user_email.'"' : '';
		}

		$widget .= ' data-background="'.$wp_snappy_settings['background'].'"';
		$widget .= isset($wp_snappy_settings['debug']) ? ' data-faq="'.$wp_snappy_settings['faq'].'"' : '';
		$widget .= ' data-debug="'.$wp_snappy_settings['debug'].'"';
		$widget .= '></script>';

		echo $widget;
	}

	public function wp_snappy_colorpicker(){

		$html = '<script>
				(function( $ ) {
				    $(function() {
				        $( ".wp-snappy-color-picker" ).wpColorPicker();
				         
				    });
				})( jQuery );
				</script>';
		echo $html;
	}

	public function wp_snappy_add_menus(){
		add_options_page(
			__('WP Snappy Options','wp-snappy'),
			__('WP Snappy','wp-snappy'),
			'manage_options',
			'wp_snappy',
			array( $this,'wp_snappy_page_display')
		);
	}

	public function wp_snappy_page_display(){
		?>
			<div class='wrap'>
			<h2>WP Snappy Settings</h2>
			<form method='post' action='options.php'>
			<?php 
				settings_fields( 'wp_snappy_settings' );
				do_settings_sections( 'wp_snappy_widget_section' );
				submit_button();
			?>
			</form>
			</div>
		<?php 
	}

	//validate the options before saving
	public function wp_snappy_validate_options($input){

		$output = array();

		//highlight to user that both script url and data domain is required
		if ( empty( $input['script_url'] ) &&  empty( $input['data_domain'] ) ) {
			add_settings_error( 'wp_snappy_settings', 'wp_snappy_settings_error', __( 'Script URL and Data Domain is required.', 'wp-snappy' ), 'error' );
		}

		foreach( $input as $key => $value ) {		 
			if( isset( $input[$key] ) ) {
			 		    $output[$key] = strip_tags( stripslashes( trim( $input[ $key ] ) ) );
			}
		}

		if ( isset( $input['background'] ) &&  !preg_match( '/^#[a-f0-9]{6}$/i', $input['background'] ) ) {
			add_settings_error( 'wp_snappy_settings', 'wp_snappy_settings_error', __( 'Insert a valid color for Background.', 'wp-snappy' ), 'error' );
			$input['background'] = '';
		}

		return apply_filters( 'wp_snappy_validate_options', $output, $input );
	}

	//getting all the field settings
	public function get_snappy_settings(){
	    $settings = array(
	        'script_url' => array(
	            'id' => 'script_url',
	            'name' => __( 'Script URL', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'text',
	            'size' => 'regular'
	        ),
	        'data_domain' => array(
	            'id' => 'data_domain',
	            'name' => __( 'Data Domain', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'text',
	            'size' => 'regular'
	        ),
	        'title' => array(
	            'id' => 'title',
	            'name' => __( 'Title', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'text',
	            'size' => 'regular',
	            'std' => 'Help & Support'
	        ),
	        'position' => array(
	            'id' => 'position',
	            'name' => __( 'Position', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'select',
				'options' => array(
					'top left' => __( 'Top Left', 'wp-snappy' ),
					'top right' => __( 'Top Right', 'wp-snappy' ),
					'bottom left'=>__('Bottom Left', 'wp-snappy' ),
					'bottom right'=>__('Bottom Right', 'wp-snappy' )
				),
				'std' => 'bottom right'
	        ),
	        'display' => array(
	            'id' => 'display',
	            'name' => __( 'Display', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'select',
				'options' => array(
					'both' => __( 'Both', 'wp-snappy' ),
					'admin' => __( 'Admin Only', 'wp-snappy' ),
					'frontend'=>__('Frontend Only', 'wp-snappy' )
				)
	        ),
	        'contact' => array(
	            'id' => 'contact',
	            'name' => __( 'Show Contact Form', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'radio',
				'options' => array(
					'1' => __( 'Yes', 'wp-snappy' ),
					'0' => __( 'No', 'wp-snappy' )
				),
				'std' => '1'
	        ),
	        'prepoinfo' => array(
	            'id' => 'prepoinfo',
	            'name' => __( 'Pre-Populate User Info', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'radio',
				'options' => array(
					'1' => __( 'Yes', 'wp-snappy' ),
					'0' => __( 'No', 'wp-snappy' )
				),
				'std' => '1'
	        ),
	        'background' => array(
	            'id' => 'background',
	            'name' => __( 'Background', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'color'
	        ),
	        'faq' => array(
	            'id' => 'faq',
	            'name' => __( 'FAQ', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'text',
	            'size' => 'small'
	        ),
	        'debug' => array(
	            'id' => 'debug',
	            'name' => __( 'Debug', 'wp-snappy' ),
	            'desc' => __return_null(),
	            'type' => 'radio',
				'options' => array(
					'1' => __( 'On', 'wp-snappy' ),
					'0' => __( 'Off', 'wp-snappy' )
				),
				'std' => '0'
	        ),
	    );

	    return $settings;
	}

	//setup text field
	public function wp_snappy_settings_text_callback( $args ) {
		$wp_snappy_settings = $this->wp_snappy_settings;

		if ( isset( $wp_snappy_settings[ $args['id'] ] ) )
			$value = $wp_snappy_settings[ $args['id'] ];
			else
			$value = isset( $args['std'] ) ? $args['std'] : '';

	    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	    $html = '<input type="text" class="' . $size . '-text" id="wp_snappy_settings[' . $args['id'] . ']" name="wp_snappy_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	    $html .= '<label for="wp_snappy_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	    echo $html;
	}

	//setup radio field
	function wp_snappy_settings_radio_callback( $args ) {
		$wp_snappy_settings = $this->wp_snappy_settings;

		foreach ( $args['options'] as $key => $option ) :
			$checked = false;

			if ( isset( $wp_snappy_settings[ $args['id'] ] ) && $wp_snappy_settings[ $args['id'] ] == $key )
				$checked = true;
			elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $wp_snappy_settings[ $args['id'] ] ) )
				$checked = true;

			echo '<input name="wp_snappy_settings[' . $args['id'] . ']"" id="wp_snappy_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
			echo '<label for="wp_snappy_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label>&nbsp;&nbsp;&nbsp;';
		endforeach;

		echo '<p class="description">' . $args['desc'] . '</p>';
	}

	//setup color field
	public function wp_snappy_settings_color_callback( $args ) {
		$wp_snappy_settings = $this->wp_snappy_settings;

		if ( isset( $wp_snappy_settings[ $args['id'] ] ) )
			$value = $wp_snappy_settings[ $args['id'] ];
			else
			$value = isset( $args['std'] ) ? $args['std'] : '';

	    $html = '<input type="text" id="wp_snappy_settings[' . $args['id'] . ']" name="wp_snappy_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '" class="wp-snappy-color-picker" />';
	    $html .= '<label for="wp_snappy_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	    echo $html;
	}

	//setup select field
	public function wp_snappy_settings_select_callback($args) {
		$wp_snappy_settings = $this->wp_snappy_settings;

		if ( isset( $wp_snappy_settings[ $args['id'] ] ) )
			$value = $wp_snappy_settings[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$html = '<select id="wp_snappy_settings[' . $args['id'] . ']" name="wp_snappy_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<label for="wp_snappy_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

}

new WP_Snappy;