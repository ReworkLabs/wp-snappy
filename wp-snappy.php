<?php 

/*
Plugin Name:       WP Snappy
Plugin URI:        https://github.com/imknight/wp-snappy
Description:       <a href="http://besnappy.com/">Snappy</a> WordPress Plugin
Version:           0.1
Author:            Knight
License:           GNU General Public License v2
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Domain Path:       /languages
Text Domain:       wp-snappy
GitHub Plugin URI: imknight/wp-snappy
GitHub Branch:     master
*/

class WP_Snappy {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'wp_snappy_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'wp_snappy_add_menus' ) );
		add_action(	'admin_footer', array( $this , 'wp_snappy_widget') );
	}

	public function wp_snappy_admin_init(){
		
		global $wp_snappy_settings;

		//getting the setting first
		$wp_snappy_settings = get_option('wp_snappy_settings');

		register_setting( 'wp_snappy_settings', 'wp_snappy_settings', '' );
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
		
		global $wp_snappy_settings;

		if ( empty($wp_snappy_settings['script_url']) OR empty($wp_snappy_settings['data_domain'])  ) {
			return;
		}

		$widget = '<script src="'.$wp_snappy_settings['script_url'].'"';
		$widget .= 'data-domain="'.$wp_snappy_settings['data_domain'].'"';
		$widget .= '></script>';

		echo $widget;
	}

	public function wp_snappy_add_menus(){
		add_menu_page(
			__('WP Snappy Options','wp-snappy'),
			__('WP Snappy','wp-snappy'),
			'manage_options',
			'wp_snappy',
			array( $this,'wp_snappy_page_callback')
		);
	}

	public function wp_snappy_page_callback(){
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
	    );

	    return $settings;
	}

	public function wp_snappy_settings_text_callback( $args ) {
		global $wp_snappy_settings;

		if ( isset( $wp_snappy_settings[ $args['id'] ] ) )
			$value = $wp_snappy_settings[ $args['id'] ];
			else
			$value = isset( $args['std'] ) ? $args['std'] : '';

	    $size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	    $html = '<input type="text" class="' . $size . '-text" id="wp_snappy_settings[' . $args['id'] . ']" name="wp_snappy_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	    $html .= '<label for="wp_snappy_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
	    echo $html;
	}

}

new WP_Snappy;