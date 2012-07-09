<?php

add_action( 'admin_init', 'wr2x_admin_init' );

/**
 *
 * SETTINGS PAGE
 *
 */
 
function wr2x_settings_page() {

    $settings_api = WeDevs_Settings_API::getInstance();
	echo '<div class="wrap">';
	$method = wr2x_getoption( "method", "wr2x_advanced", 'Retina-Images' );
	echo "<div id='icon-options-general' class='icon32'><br></div><h2>WP Retina 2x</h2>";
	
	if ( $method == 'retina.js' ) {
		echo "<p><span style='color: orange;'>Current method: <u>Client-side</u>.</span> Oh, and don't forget to check the tutorial of this plugin on <a href='http://www.totorotimes.com/news/retina-display-wordpress-plugin'>Totoro Times</a>.</p>";
	}
	if ( $method == 'Retina-Images' ) {
		echo "<p><span style='color: green;'>Current method: Server-side.</span> Oh, and don't forget to check the tutorial of this plugin on <a href='http://www.totorotimes.com/news/retina-display-wordpress-plugin'>Totoro Times</a>.</p>";
	}
	
	if ( !function_exists( 'enable_media_replace' ) ) {
		echo "<p style='color: green;'>This plugin supports and uses the <a href='http://wordpress.org/extend/plugins/enable-media-replace/'>Enable Media Replace</a> plugin if available. A 'Replace' button will appear in case your images are too small. It is strongly recommended to install it.</p>";
	}
	
    
    settings_errors();
    $settings_api->show_navigation();
    $settings_api->show_forms();
    echo '</div>';
	echo "<center><p>This plugin is actively developped and maintained by <a href='https://plus.google.com/106075761239802324012'>Jordy Meow</a>.<br />Please visit me at <a href='http://www.totorotimes.com'>Totoro Times</a>, a website about Japan, photography and abandoned places.<br />And thanks for linking us on <a href='https://www.facebook.com/totorotimes'>Facebook</a> and <a href='https://plus.google.com/106832157268594698217'>Google+</a> :)</p></center>";
}

function wr2x_getoption( $option, $section, $default = '' ) {
	$options = get_option( $section );
	if ( isset( $options[$option] ) )
		return $options[$option];
	return $default;
}
 
function wr2x_admin_init() {

	require( 'class.settings-api.php' );

	if (delete_transient('wr2x_flush_rules')) {
		global $wp_rewrite;
		wr2x_generate_rewrite_rules( $wp_rewrite, true );
	}
	
	$sections = array(
        array(
            'id' => 'wr2x_basics',
            'title' => __( 'Basics', 'wp-retina-2x' )
        ),
		array(
            'id' => 'wr2x_advanced',
            'title' => __( 'Advanced', 'wp-retina-2x' )
        )
    );
	
	$wpsizes = wr2x_get_image_sizes();
	$sizes = array();
	foreach ( $wpsizes as $name => $attr )
		$sizes["$name"] = sprintf( "%s (%dx%d)", $name, $attr['width'], $attr['height'] );
	
	$fields = array(
        'wr2x_basics' => array(
			array(
                'name' => 'ignore_sizes',
                'label' => __( 'Disabled Sizes', 'wp-retina-2x' ),
                'desc' => __( 'The checked sizes will not be generated for Retina displays.', 'wp-retina-2x' ),
                'type' => 'multicheck',
                'options' => $sizes
            ),
			array(
                'name' => 'auto_generate',
                'label' => __( 'Auto Generate', 'wp-retina-2x' ),
                'desc' => __( 'Generate Retina images automatically when images are uploaded to the Media Library.', 'wp-retina-2x' ),
                'type' => 'checkbox',
                'default' => false
            )
        ),
		'wr2x_advanced' => array(
			array(
                'name' => 'method',
                'label' => __( 'Method', 'wp-retina-2x' ),
                'desc' => __( 'The <b>server-side method</b> is very fast and efficient. However, depending on the cache system you are using (including services like Cloudflare) you might encounter issues. Please contact me if that is the case.
                The <b>client-side method</b> is fail-safe and only uses a JavaScript file. When a Retina Display is detected, requests for every images on the page will be sent to the server and a high resolution one will be retrieved if available. This method is not efficient and quite slow.', 'wp-retina-2x' ),
                'type' => 'radio',
                'default' => 'Retina-Images',
                'options' => array(
					'Retina-Images' => 'Server-side: Retina-Images (https://github.com/Retina-Images/Retina-Images)',
					'retina.js' => 'Client-side: Retina.js (http://retinajs.com/)'
                )
            ),
			array(
                'name' => 'debug',
                'label' => __( 'Debug Mode', 'wp-retina-2x' ),
                'desc' => __( 'If checked, the client will be always served Retina images. Convenient for testing.', 'wp-retina-2x' ),
                'type' => 'checkbox',
                'default' => false
            ),
			array(
                'name' => 'hide_retina_column',
                'label' => __( 'Hide \'Retina\' column', 'wp-retina-2x' ),
                'desc' => __( 'Will hide the \'Retina Column\' from the Media Library.', 'wp-retina-2x' ),
                'type' => 'checkbox',
                'default' => false
            ),
			array(
                'name' => 'hide_retina_dashboard',
                'label' => __( 'Hide Retina Dashboard', 'wp-retina-2x' ),
                'desc' => __( 'Doesn\'t show the Retina Dashboard menu and tools.', 'wp-retina-2x' ),
                'type' => 'checkbox',
                'default' => false
            )
		)
    );
	$settings_api = WeDevs_Settings_API::getInstance();
    $settings_api->set_sections( $sections );
    $settings_api->set_fields( $fields );
    $settings_api->admin_init();
}

function wr2x_update_option( $option ) {
	if ($option == 'wr2x_advanced') {
		set_transient( 'wr2x_flush_rules', true );
	}
}

function wr2x_generate_rewrite_rules( $wp_rewrite, $flush = false ) {
	$method = wr2x_getoption( "method", "wr2x_advanced", "Retina-Images" );
	if ($method == "Retina-Images") {
		add_rewrite_rule( '.*\.(jpe?g|gif|png|bmp)', 'wp-content/plugins/wp-retina-2x/wr2x_image.php', 'top' );		
	}
	if ( $flush == true ) {
		$wp_rewrite->flush_rules();
	}
}

?>