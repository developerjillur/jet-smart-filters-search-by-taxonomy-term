<?php
namespace JSF_SBTAX;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main file
 */
class Plugin {

	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.1.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	private $search_query = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.1.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Hook into the query and modify it to search by taxonomy terms.
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function maybe_hook_taxonomy_clause( $query ) {
		$data = isset( $_REQUEST['query'] ) ? $_REQUEST['query'] : $_REQUEST;

		foreach ( $data as $key => $value ) {
			if ( false !== strpos( $key, '|search' ) ) {
				$this->search_query = sanitize_text_field( $value );
				add_filter( 'posts_where', array( $this, 'add_taxonomy_clause' ), 10, 2 );
			}
		}

		return $query;
	}

	/**
	 * Add the taxonomy term search clause to the query.
	 *
	 * @param $where
	 * @param $query
	 *
	 * @return string
	 */
        // In includes/plugin.php
        public function add_taxonomy_clause( $where, $query ) {
        
            if ( ! $this->search_query || ! $query->get( 'jet_smart_filters' ) ) {
                return $where;
            }
        
            global $wpdb;
        
            // Retrieve and sanitize the taxonomy slugs
            $options = get_option( 'jsf_sbtax_settings' );
            $taxonomy_slugs = isset( $options['jsf_sbtax_taxonomy_slug'] ) ? $options['jsf_sbtax_taxonomy_slug'] : '';
            $taxonomy_slugs = array_map( 'sanitize_text_field', explode( ',', $taxonomy_slugs ) );
        
            $taxonomy_queries = [];
        
            foreach ( $taxonomy_slugs as $taxonomy ) {
                $taxonomy = trim( $taxonomy );
                if ( ! empty( $taxonomy ) ) {
                    $taxonomy_queries[] = $wpdb->prepare(
                        " EXISTS (
                            SELECT 1
                            FROM $wpdb->term_relationships AS tr
                            INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                            INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id
                            WHERE tr.object_id = {$wpdb->posts}.ID
                            AND tt.taxonomy = %s
                            AND t.name LIKE %s
                        )",
                        $taxonomy,
                        '%' . $wpdb->esc_like( $this->search_query ) . '%'
                    );
                }
            }
        
            if ( ! empty( $taxonomy_queries ) ) {
                $where .= " OR (" . implode( ' OR ', $taxonomy_queries ) . ")";
            }
        
            $this->search_query = null;
            remove_filter( 'posts_where', array( $this, 'add_taxonomy_clause' ), 10, 2 );
        
            return $where;
        }

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		add_action( 'jet-smart-filters/query/final-query', array( $this, 'maybe_hook_taxonomy_clause' ) );
	}

}

Plugin::instance();