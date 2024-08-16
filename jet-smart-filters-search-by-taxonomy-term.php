<?php
/**
 * Plugin Name: JetSmartFilters - Search Posts by Taxonomy Term
 * Plugin URI:  https://crocoblock.com/freemium/tools/
 * Description: Extend JetSmartFilters to search by custom taxonomy term names. Have option to provied one or multiple taxonomy slugs under on Setting->JSF Taxonomy Search.
 * Version:     1.1.0
 * Author:      Developer Jillur
 * Author URI:  https://crocoblock.com/
 * Requires Plugins:  JetEngine, JetSmartFilters
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

add_action( 'plugins_loaded', function() {

	define( 'JSF_SBTAX_VERSION', '1.1.0' );

	define( 'JSF_SBTAX__FILE__', __FILE__ );
	define( 'JSF_SBTAX_PATH', plugin_dir_path( JSF_SBTAX__FILE__ ) );

	require JSF_SBTAX_PATH . 'includes/plugin.php';

} );

add_action( 'admin_menu', 'jsf_sbtax_add_admin_menu' );
add_action( 'admin_init', 'jsf_sbtax_settings_init' );

function jsf_sbtax_add_admin_menu() { 
	add_options_page( 'JetSmartFilters - Taxonomy Term Search', 'JSF Taxonomy Search', 'manage_options', 'jsf_taxonomy_search', 'jsf_sbtax_options_page' );
}

function jsf_sbtax_settings_init() { 
	register_setting( 'pluginPage', 'jsf_sbtax_settings' );

	add_settings_section(
		'jsf_sbtax_pluginPage_section', 
		__( 'Settings for JetSmartFilters - Taxonomy Term Search', 'jsf_sbtax' ), 
		null, 
		'pluginPage'
	);

	add_settings_field( 
		'jsf_sbtax_taxonomy_slug', 
		__( 'Custom Taxonomy Slug', 'jsf_sbtax' ), 
		'jsf_sbtax_taxonomy_slug_render', 
		'pluginPage', 
		'jsf_sbtax_pluginPage_section' 
	);
}

// In jet-smart-filters-search-by-taxonomy-term.php
function jsf_sbtax_taxonomy_slug_render() { 
    $options = get_option( 'jsf_sbtax_settings' );
    $taxonomy_slugs = isset( $options['jsf_sbtax_taxonomy_slug'] ) ? $options['jsf_sbtax_taxonomy_slug'] : '';
    ?>
    <input type='text' class='regular-text' name='jsf_sbtax_settings[jsf_sbtax_taxonomy_slug]' value='<?php echo esc_attr( $taxonomy_slugs ); ?>'>
    <p class="description"><?php _e( 'Enter taxonomy slugs separated by commas (e.g., category,post_tag,custom_taxonomy).', 'jsf_sbtax' ); ?></p>
    <?php
}

function jsf_sbtax_options_page() { 
	?>
	<form action='options.php' method='post'>
		
		<h2>JetSmartFilters - Search Posts by Taxonomy Term</h2>
		
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
		
	</form>
	<?php
}