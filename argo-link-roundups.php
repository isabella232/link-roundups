<?php
/**
	* @package Argo_Links
	* @version 0.01
	*/
/*
*Argo Links - Link Roundups Code
*/

/* The Argo Link Roundups class - so we don't have function naming conflicts */
class ArgoLinkRoundups {

	/* Initialize the plugin */
	public static function init() {
		/*Register the custom post type of argolinks */
		add_action('init', array(__CLASS__, 'register_post_type' ));

		/*Add our custom post fields for our custom post type*/
		add_action("admin_init", array(__CLASS__, "add_custom_post_fields"));

		/*Add the Argo Link Roundups Options sub menu*/
		add_action("admin_menu", array(__CLASS__, "add_argo_link_roundup_options_page"));

		/*Save our custom post fields! Very important!*/
		add_action('save_post', array(__CLASS__, 'save_custom_fields'));

		/*Make sure our custom post type gets pulled into the river*/
		add_filter( 'pre_get_posts', array(__CLASS__,'my_get_posts') );
	}

	/*Pull the argolinkroundups into the rivers for is_home, is_tag, is_category, is_archive*/
	/*Merge the post_type query var if there is already a custom post type being pulled in, otherwise do post & argolinkroundups*/
	public static function my_get_posts( $query ) {
		// bail out early if suppress filters is set to true
		if ($query->get('suppress_filters')) return;

		/**
		 * Add argolinkroundups to the post type in the query if it is not already in it.
		 */

		if ( is_home() || is_tag() || is_category() ) {
			if (isset($query->query_vars['post_type']) && is_array($query->query_vars['post_type'])) {
			if ( ! in_array( 'argolinkroundups', $query->query_vars['post_type'] ) ) {
				// There is an array of post types and argolinkroundups is not in it
				$query->set( 'post_type', array_merge(array('argolinkroundups' ), $query->query_vars['post_type']) );
			}
			} elseif (isset($query->query_vars['post_type']) && !is_array($query->query_vars['post_type'])) {
			if ( $query->query_vars['post_type'] !== 'argolinkroundups' ) {
				// There is a single post type, so we shall add it to an array
				$query->set( 'post_type', array('argolinkroundups', $query->query_vars['post_type']) );
			}
			} else {
			// Post type is not set, so it shall be post and argolinkroundups
			$query->set( 'post_type', array('post','argolinkroundups') );
			}
		}
	}

	/*Register the Argo Links post type */
	public static function register_post_type() {
		$argolinkroundups_options = array(
			'labels' => array(
				'name' => 'Link Roundups',
				'singular_name' => 'Argo Link Roundup',
				'add_new' => 'Add New Roundup',
				'add_new_item' => 'Add New Argo Link Roundup',
				'edit' => 'Edit',
				'edit_item' => 'Edit Argo Link Roundup',
				'view' => 'View',
				'view_item' => 'View Argo Link Roundup',
				'search_items' => 'Search Argo Link Roundups',
				'not_found' => 'No Argo Links Roundups found',
				'not_found_in_trash' => 'No Argo Link Roundups found in Trash',
			),
			'description' => 'Argo Link Roundups',
			'supports' => array(
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields',
				'comments', 'revisions', 'page-attributes', 'post-formats'
			),
			'public' => true,
			'menu_position' => 7,
			'taxonomies' => apply_filters('argolinkroundups_taxonomies', array('category','post_tag')),
			'has_archive' => true,
		);

		if (get_option('argo_link_roundups_custom_url') != "")
			$argolinkroundups_options['rewrite'] = array('slug' => get_option('argo_link_roundups_custom_url'));

		register_post_type('argolinkroundups', $argolinkroundups_options);
	}

	/*Tell Wordpress where to put our custom fields for our custom post type*/
	public static function add_custom_post_fields() {
		add_meta_box(
			"argo_link_roundups_roundup", "Recent Roundup Links",
			array(__CLASS__, "display_custom_fields"), "argolinkroundups", "normal", "high"
		);
	}

	/*Show our custom post fields in the add/edit Argo Link Roundups admin pages*/
	public static function display_custom_fields() {
?>
		<div id='argo-links-display-area'></div>
		<script type='text/javascript'>
		jQuery(function(){
			jQuery('#argo-links-display-area').load('<?php echo plugin_dir_url(__FILE__); ?>display-argo-links.php');
		});
		</script>
<?php
	}

	/*Save the custom post field data.	Very important!*/
	public static function save_custom_fields($post_id) {
		if (isset($_POST["argo_link_url"])){
			update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_url", $_POST["argo_link_url"]);
		}
		if (isset($_POST["argo_link_description"])){
			update_post_meta((isset($_POST['post_id']) ? $_POST['post_ID'] : $post_id), "argo_link_description", $_POST["argo_link_description"]);
		}
	}
	/*Add the Argo Link Roundup options sub menu*/
	public static function add_argo_link_roundup_options_page() {
		add_submenu_page(
			"edit.php?post_type=argolinkroundups", "Options", "Options", "edit_posts",
			"argo-link-roundups-options", array(__CLASS__, 'build_argo_link_roundups_options_page')
		);
		//call register settings function
		add_action('admin_init', array(__CLASS__, 'register_mysettings'));
	}

	public static function register_mysettings() {
		//register our settings
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_custom_url');
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_custom_html');
		register_setting(
			'argolinkroundups-settings-group', 'argo_link_roundups_use_mailchimp_integration',
			array(__CLASS__, 'validate_mailchimp_integration')
		);
		register_setting('argolinkroundups-settings-group', 'argo_link_roundups_mailchimp_api_key');
		register_setting('argolinkroundups-settings-group', 'argo_link_mailchimp_template');
	}

	public static function validate_mailchimp_integration($input) {
		// Can't have an empty MailChimp API Key if the integration functionality is enabled.
		if (empty($_POST['argo_link_roundups_mailchimp_api_key']) && !empty($input)) {
			add_settings_error(
				'argo_link_roundups_use_mailchimp_integration',
				'argo_link_roundups_use_mailchimp_integration_error',
				'Please enter a valid MailChimp API Key.',
				'error'
			);
			return '';
		}

		return $input;
	}

	public static function build_argo_link_roundups_options_page() {
		$mc_api_key = get_option('argo_link_roundups_mailchimp_api_key');
		if (!empty($mc_api_key)) {
			$opts = array('debug' => (defined('WP_DEBUG') && WP_DEBUG)? WP_DEBUG:false);
			$mcapi = new Mailchimp($mc_api_key, $opts);

			$templates = $mcapi->templates->getList(
				array('gallery' => false, 'base' => false),
				array('include_drag_and_drop' => true)
			);
		}

		include_once __DIR__ . '/templates/options.php';
	}
}
