<?php
/**
 * The new-wave commenting and forums for Wordpress
 *
 * @package  Moot
 * @author   Tero Piirainen <tero@moot.it>
 * @license  MIT
 * @link    https://moot.it/docs/wordpress.html
 * @copyright 2014 Moot Inc
 */

class Moot {

  protected $version = '2.0.12';

  protected $plugin_slug = 'moot';

  protected static $instance = null;

  protected $plugin_screen_hook_suffix = null;


  private function __construct() {
	add_action( 'admin_notices', array( $this, 'display_upgrade_notice' ) );

    add_filter('the_content', array($this, 'default_comments'));

    add_action('wp_enqueue_scripts', array($this, 'moot_includes'));
    add_action('wp_head', array($this, 'moot_head'));
    add_action('admin_menu', array($this, 'moot_admin_menu'));
    add_action('admin_init', array($this, 'moot_settings'));

    add_shortcode('moot', array($this, 'moot_shortcode'));
    add_shortcode('no-moot', array($this, 'moot_disable'));
  }


  public static function get_instance() {
    if (null == self::$instance) { self::$instance = new self; }
    return self::$instance;
  }

  public function moot_head() {
    require_once(plugin_dir_path(__FILE__) . 'public.php');
  }

  public function moot_includes() {

    if (!is_home()) {
      wp_enqueue_style("moot", '//cdn.moot.it/latest/moot.css', array(), $this->version);

      $lang = get_option('moot_language');
      if ($lang == 'en') $lang = "";
      if ($lang) $lang = "." . $lang;

      wp_enqueue_script("", "//cdn.moot.it/latest/moot$lang.min.js", array('jquery'), $this->version);
    }
  }

  public function display_upgrade_notice() {
    if ( !class_exists( 'Muut' ) ) {
		add_thickbox();
		wp_enqueue_script( 'plugin-install' );
		// TODO: Change the plugin name to install to 'muut' rather than 'moot'.
		$install_nonce_url = wp_nonce_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=muut&TB_iframe=true&width=600&height=550' ) );
		echo '<div class="update-nag">';
		echo 'We at Moot have changed our name to <b>Muut</b>! Please download the plugin with the new name <a href="' . $install_nonce_url . '" class="thickbox" title="Install Muut!">here</a>, and you can visit out website at <a href="://muut.com">muut.com</a>.';
		echo '</div>';
	} else {
		echo '<div class="update-nag">';
		echo 'Now that you\'ve updated to <b>Muut</b>, please feel free to deactivate version 2.0.12 of the <b>Moot</b> plugin.';
		echo '</div>';
	}
  }

  public function default_comments($content) {
    $forumname = get_option('moot_forum_name');

    if (!is_home() && $forumname != null && get_option('moot_generate') == "true" && get_post_type() == "post") {
      $page_id = sanitize_title(get_the_title());
      $content .= "<a id='moot-default-comments' href='https://moot.it/i/$forumname/wordpress:$page_id'>Comments</a>";
    }

    return $content;
  }

  public function moot_settings($content) {
    register_setting('moot_options', 'moot_forum_name');
    register_setting('moot_options', 'moot_api_key');
    register_setting('moot_options', 'moot_secret_key');
    register_setting('moot_options', 'moot_language');
    register_setting('moot_options', 'moot_generate');
    register_setting('moot_options', 'moot_comments_under_forums');
  }

  // admin menu
  public function moot_admin_menu() {

    if (is_super_admin()) {
      $this->plugin_screen_hook_suffix = add_plugins_page(
        __('Moot', $this->plugin_slug),
        __('Moot', $this->plugin_slug),
        'read', $this->plugin_slug, array($this, 'moot_admin')
      );
    }

  }

  public function moot_admin() {
    include_once('settings.php');
  }

  public function moot_disable() {
    return "<span id='no-moot'></span>";
  }

  public function moot_shortcode($params) {

    extract( shortcode_atts( array(
      'forum' => false,
      'threaded' => false,
      'path' => false

    ), $params) );

    $forumname = get_option('moot_forum_name');

    if ($forumname == null) return "";

    $tag = "<a id='moot' href='https://moot.it/i/$forumname";
    $page_id = sanitize_title(get_the_title());


    // (bool ? this : that) not working
    if ($forum)   return "$tag'>$forumname forums</a>";
    if ($threaded) return "$tag/wordpress/$page_id'>Comments</a>";
    if ($path)    return "$tag/$path'>Comments are here</a>";
              return "$tag/wordpress:$page_id'>Comments</a>";

  }

}