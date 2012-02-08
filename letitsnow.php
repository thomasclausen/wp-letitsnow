<?php
/*
Plugin Name: Let it snow, let it snow, let it snow...
Description: For the holiday season.
Version: 0.3
License: GPLv2
Author: Thomas Clausen
Author URI: http://www.thomasclausen.dk/wordpress/
*/

// Add snowstorm script
function letitsnow_scripts() {
	$settings = get_option( 'letitsnow_options' );
	if ( $settings['use'] == 'on' ) {
		wp_register_script( 'letitsnow-script', plugins_url( '/letitsnow.js', __FILE__ ), array( 'jquery' ), '0.3' );
		wp_enqueue_script( 'letitsnow-script' );
	} else if ( $settings['use'] == 'forecast' ) {
		$xmlUrl = 'http://www.google.com/ig/api?weather=' . urlencode( $settings['city'] );
		$output = wp_remote_fopen( $xmlUrl );
		$xmlData  = simplexml_load_string( $output );
		$conditions = array();
		$conditions['current']['city'] = $xmlData->weather->forecast_information->city['data'];
		$conditions['current']['condition'] = $xmlData->weather->current_conditions->condition['data'];
		$conditions['current']['icon'] = $xmlData->weather->current_conditions->icon['data'];

		$search = array( 'sne', 'snow', 'flurries', 'sleet', 'icy' ); // words to search for
		if ( strpos( strtolower( $conditions['current']['condition'] ), $search ) == false ) {
			wp_register_script( 'letitsnow-script', plugins_url( '/letitsnow.js', __FILE__ ), array( 'jquery' ), '0.3' );
			wp_enqueue_script( 'letitsnow-script' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'letitsnow_scripts' );

function letitsnow_array() {
	$letitsnow_array = array (
		array(
			'type' => 'section_start'
		),
		array(
			'type' => 'radio',
			'label' => __( 'Aktiver sne', 'letitsnow' ),
			'id' => 'use',
			'description' => __( 'Bestemmer om der skal vises sne, om det skal afh&aelig;nge af vejroplysninger fra Google eller om det ikke skal sne.', 'letitsnow' ),
			'options' => array(
				array(
					'value' => 'on',
					'label' => __( 'Ja, det er jo den tid p&aring; &aring;ret', 'thomasclausen' )
				),
				array(
					'value' => 'forecast',
					'label' => __( 'Afh&aelig;ngig af vejrudsigt', 'thomasclausen' )
				),
				array(
					'value' => 'off',
					'label' => __( 'Nej, kan ikke fordrage sne', 'thomasclausen' )
				)
			)
		),
		array(
			'type' => 'input',
			'label' => __( 'F&oslash;lg vejret i denne by', 'letitsnow' ),
			'id' => 'city',
			'description' => __( 'Indtast din by her.<br />Udfra byen hentes der lokale vejroplysninger fra Google og viser automatisk sne, hvis der i vejrudsigten er sne. Eksempel: Aalborg, Denmark', 'letitsnow' ),
			'value' => __( '', 'letitsnow' )
		),
		array(
			'type' => 'section_end'
		)
	);
	return $letitsnow_array;
}

function letitsnow_field_name( $value ) {
	return 'letitsnow_options[' . $value . ']';
}

function letitsnow_options_page() {
	$letitsnow_options = letitsnow_array();
	
	if ( !isset( $_REQUEST['settings-updated'] ) ) :
		$_REQUEST['settings-updated'] = false;
	endif; ?>
	<style type="text/css">
	.hr-divider { height: 1px; color: #e3e3e3; margin: 24px 0; background: #e3e3e3; overflow: hidden; clear: both; }
	.required { margin: 0 0 0 5px; }
	.required strong, .required b { color: #93332e; }
	</style>
	<div class="wrap">
		<?php screen_icon(); echo '<h2>' . __( 'Let it snow...', 'letitsnow' ) . '</h2>'; ?>
		<?php if ( false !== $_REQUEST['settings-updated'] ) : ?><div class="updated fade"><p><strong><?php _e( 'Indstillingerne er gemt', 'letitsnow' ); ?></strong></p></div><?php endif; ?>
		<form method="post" action="options.php">
			<?php wp_nonce_field( 'letitsnow-options' ); ?>
			<?php settings_fields( 'letitsnow' ); ?>
			<?php $settings = get_option( 'letitsnow_options' ); ?>
			<?php echo "\n"; ?>

			<?php foreach ( $letitsnow_options as $option ) {
				switch ( $option['type'] ) {
					case 'text':
						if ( $option['title'] != '' ) :
							echo '<h3>' . $option['title'] . '</h3>';
						endif;
						if ( $option['text'] != '' ) :
							echo '<p>' . $option['text'] . '</p>';
						endif;
						break;
					case 'section_start':
						echo '<table class="form-table"><tbody>';
						break;
					case 'section_end':
						echo '</tbody></table>';
						break;
					case 'section_divider':
						echo '<tr valign="top"><td colspan="2"><hr /></td></tr>';
						break;
					case 'input':
						if ( $option['description'] != '' ) :
							$description = ' <span class="description">' . $option['description'] . '</span>';
						endif;
						
						echo '<tr valign="top">';
						echo '<th scope="row"><label for="' . letitsnow_field_name( $option['id'] ) . '">' . $option['label'] . '</label></th>';
						echo '<td><input type="text" name="' . letitsnow_field_name( $option['id'] ) . '" id="' . letitsnow_field_name( $option['id'] ) . '" value="' . esc_attr( $settings[$option['id']] ) . '" class="regular-text" />' . $description . '</td>';
						echo '</tr>';
						break;
					case 'checkbox':
						if ( $option['description'] != '' ) :
							$description = '<p><span class="description">' . $option['description'] . '</span></p>';
						endif;
						
						echo '<tr valign="top">';
						echo '<th scope="row">' . $option['label'] . $description . '</th>';
						echo '<td>';
						$checkboxes = $option['options'];
						foreach ( $checkboxes as $checkbox ) {
							echo '<input type="checkbox" name="' . letitsnow_field_name( $option['id'] . '_' . $checkbox['value'] ) . '" id="' . letitsnow_field_name( $option['id'] . '_' . $checkbox['value'] ) . '" value="' . $checkbox['value'] . '" ' . ( $settings[$option['id'] . '_' . $checkbox['value']] == $checkbox['value'] ? 'checked="checked"' : '' ) . ' /><label for="' . letitsnow_field_name( $option['id'] . '_' . $checkbox['value'] ) . '">' . $checkbox['label'] . '</label><br />';
						}
						echo '</td>';
						echo '</tr>';
						break;
					case 'radio':
						if ( $option['description'] != '' ) :
							$description = '<p><span class="description">' . $option['description'] . '</span></p>';
						endif;
						
						echo '<tr valign="top">';
						echo '<th scope="row">' . $option['label'] . $description . '</th>';
						echo '<td>';
						$radiobuttons = $option['options'];
						foreach ( $radiobuttons as $radiobutton ) {
							echo '<input type="radio" name="' . letitsnow_field_name( $option['id'] ) . '" id="' . letitsnow_field_name( $option['id'] ) . '" value="' . $radiobutton['value'] . '" ' . ( $settings[$option['id']] == $radiobutton['value'] ? 'checked="checked"' : '' ) . ' /><label for="' . letitsnow_field_name( $option['id'] ) . '">' . $radiobutton['label'] . '</label><br />';
						}
						echo '</td>';
						echo '</tr>';
						break;
					case 'textarea':
						if ( $option['description'] != '' ) :
							$description = '<p><span class="description">' . $option['description'] . '</span></p>';
						endif;
						
						echo '<tr valign="top">';
						echo '<th scope="row"><label for="' . letitsnow_field_name( $option['id'] ) . '">' . $option['label'] . '</label>' . $description . '</th>';
						echo '<td><textarea name="' . letitsnow_field_name( $option['id'] ) . '" id="' . letitsnow_field_name( $option['id'] ) . '" rows="' . $option['rows'] . '" class="large-text">' . esc_textarea( $settings[$option['id']] ) . '</textarea></td>';
						echo '</tr>';
						break;
					case 'select':
						if ( $option['description'] != '' ) :
							$description = ' <span class="description">' . $option['description'] . '</span>';
						endif;
						
						echo '<tr valign="top">';
						echo '<th scope="row"><label for="' . letitsnow_field_name( $option['id'] ) . '">' . $option['label'] . '</label></th>';
						echo '<td><select name="' . letitsnow_field_name( $option['id'] ) . '" id="' . letitsnow_field_name( $option['id'] ) . '">';
						$selectoptions = $option['options'];
						foreach ( $selectoptions as $selectoption ) {
							echo ' <option value="' . $selectoption['value'] . '" ' . ( $settings[$option['id']] == $selectoption['value'] ? 'selected="selected"' : '' ) . '>' . $selectoption['label'] . '</option>';
						}
						echo '</select/>' . $description . '</td>';
						echo '</tr>';
						break;
				}
			} ?>
			<p class="submit"><input type="submit" name="submit" value="<?php _e( 'Gem &aelig;ndringer', 'letitsnow' ) ?>" class="button-primary" /></p>
		</form>
		<?php echo '<pre>'; ?>
		<?php print_r( $settings ); ?>
		<?php echo '</pre>'; ?>
	</div>
<?php }

function letitsnow_validate( $input ) {
	$letitsnow_options = letitsnow_array();
	
	foreach ( $letitsnow_options as $option ) {
		switch ( $option['type'] ) {
			case 'input':
				$input[$option['id']] = wp_kses( $input[$option['id']], array( 'a' => array( 'href' => array(), 'title' => array() ), 'i' => array(), 'em' => array(), 'b' => array(), 'strong' => array() ) );
				break;
			case 'checkbox':
				$input[$option['id']] = wp_kses( $input[$option['id']], '' );
				break;
			case 'radio':
				$input[$option['id']] = wp_kses( $input[$option['id']], '' );

				break;
			case 'textarea':
				$input[$option['id']] = wp_kses( $input[$option['id']], array( 'a' => array( 'href' => array(), 'title' => array() ), 'span' => array( 'id' => array(), 'class' => array() ), 'i' => array(), 'em' => array(), 'b' => array(), 'strong' => array(), 'script' => array( 'type' => array() ) ) );
				break;
			case 'select':
				$input[$option['id']] = wp_kses( $input[$option['id']], '' );
				break;
		}
	}
	
	return $input;
}

function letitsnow_init() {
	register_setting( 'letitsnow', 'letitsnow_options', 'letitsnow_validate' );
}

function letitsnow_add_page() {
	add_options_page( __( 'Let it snow...', 'letitsnow' ), __( 'Let it snow...', 'letitsnow' ), 'manage_options', 'letitsnow-options', 'letitsnow_options_page' );
}

add_action( 'admin_init', 'letitsnow_init' );
add_action( 'admin_menu', 'letitsnow_add_page' ); ?>