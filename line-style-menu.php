<?php

/*
Plugin Name: Line Style Menu
Plugin URI: https://github.com/cookforweb/Line-style-menu-for-WP
Description: Implementation of codrops line style menu in wordpress
Author: CookforWeb
Version: 0.1.0
Author URI: http://www.cookforweb.com
*/

//add_action( 'wp_enqueue_scripts', 'wp_enqueue_scripts' );
/*add_shortcode( 'line_menu', 'line_menu_callback' );


function line_menu_callback( $atts ){
	
	wp_enqueue_style( 'linemenucss', plugins_url( 'css/line-style-menu.css', __FILE__ ), false ); 
	var_dump( $atts );

	$defaults = array(
		'theme_location'  => $atts['menu'],
		'menu'            => '',
		'container'       => 'nav',
		'container_class' => 'menu menu--prospero',
		'container_id'    => '',
		'menu_class'      => 'menu__list',
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '',
		'link_before'     => '',
		'link_after'      => '',
		'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'depth'           => 1,
		'walker'          => 'new Walker_CookforWeb_Menu'
	);
	
	wp_nav_menu( $defaults );
}*/











if ( ! class_exists( 'LineMenu' ) ):
	
class LineMenu
{

	/**
	 * Initialize the class
	 *
	 * @param 
	 * @param 
	 * @return bool|string|void
	 */
	public static function initialize(){
		
		/*if ( file_exists( plugin_dir_path( __FILE__ ). 'class.walker.php' ) ){
			require_once( plugin_dir_path( __FILE__ ). 'class.walker.php' );
		}*/
		
		new self;
	}


	/**
	 * Class constructor
	 *
	 * @param 
	 * @return void
	 */	
	public function __construct(){
		
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_shortcode( 'line_menu', array( $this, 'line_menu_callback' ) );
	}
	
	public function wp_enqueue_scripts(){
		
	}
	
	/**
	 * Line menu callback function
	 *
	 * @param $atts
	 * @return void
	 */	
	 
	public function line_menu_callback( $atts ){
		
		wp_enqueue_style( 'linemenucss', plugins_url( 'css/line-style-menu.css', __FILE__ ), false ); 
		
		$defaults = array(
			'theme_location'  => $atts['menu'],
			'menu'            => '',
			'container'       => 'nav',
			'container_class' => 'menu menu--'. $atts['type'],
			'container_id'    => '',
			'menu_class'      => 'menu__list',
			'menu_id'         => '',
			'echo'            => true,
			'fallback_cb'     => 'wp_page_menu',
			'before'          => '',
			'after'           => '',
			'link_before'     => '',
			'link_after'      => '',
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'depth'           => 1,
			'walker'          => new Walker_CookforWeb_Menu,
		);
		
		wp_nav_menu( $defaults );
	}


	public function wp_nav_menu( $args = array() ) {
		static $menu_id_slugs = array();
	
		$defaults = array( 'menu' => '', 'container' => 'div', 'container_class' => '', 'container_id' => '', 'menu_class' => 'menu', 'menu_id' => '',
		'echo' => true, 'fallback_cb' => 'wp_page_menu', 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'depth' => 0, 'walker' => '', 'theme_location' => '' );
	
		$args = wp_parse_args( $args, $defaults );
		/**
		 * Filter the arguments used to display a navigation menu.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param array $args Array of wp_nav_menu() arguments.
		 */
		$args = apply_filters( 'wp_nav_menu_args', $args );
		$args = (object) $args;
	
		/**
		 * Filter whether to short-circuit the wp_nav_menu() output.
		 *
		 * Returning a non-null value to the filter will short-circuit
		 * wp_nav_menu(), echoing that value if $args->echo is true,
		 * returning that value otherwise.
		 *
		 * @since 3.9.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string|null $output Nav menu output to short-circuit with. Default null.
		 * @param object      $args   An object containing wp_nav_menu() arguments.
		 */
		$nav_menu = apply_filters( 'pre_wp_nav_menu', null, $args );
	
		if ( null !== $nav_menu ) {
			if ( $args->echo ) {
				echo $nav_menu;
				return;
			}
	
			return $nav_menu;
		}
	
		// Get the nav menu based on the requested menu
		$menu = wp_get_nav_menu_object( $args->menu );
	
		// Get the nav menu based on the theme_location
		if ( ! $menu && $args->theme_location && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $args->theme_location ] ) )
			$menu = wp_get_nav_menu_object( $locations[ $args->theme_location ] );
	
		// get the first menu that has items if we still can't find a menu
		if ( ! $menu && !$args->theme_location ) {
			$menus = wp_get_nav_menus();
			foreach ( $menus as $menu_maybe ) {
				if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id, array( 'update_post_term_cache' => false ) ) ) {
					$menu = $menu_maybe;
					break;
				}
			}
		}
	
		if ( empty( $args->menu ) ) {
			$args->menu = $menu;
		}
	
		// If the menu exists, get its items.
		if ( $menu && ! is_wp_error($menu) && !isset($menu_items) )
			$menu_items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
	
		/*
		 * If no menu was found:
		 *  - Fall back (if one was specified), or bail.
		 *
		 * If no menu items were found:
		 *  - Fall back, but only if no theme location was specified.
		 *  - Otherwise, bail.
		 */
		if ( ( !$menu || is_wp_error($menu) || ( isset($menu_items) && empty($menu_items) && !$args->theme_location ) )
			&& isset( $args->fallback_cb ) && $args->fallback_cb && is_callable( $args->fallback_cb ) )
				return call_user_func( $args->fallback_cb, (array) $args );
	
		if ( ! $menu || is_wp_error( $menu ) )
			return false;
	
		$nav_menu = $items = '';
	
		$show_container = false;
		if ( $args->container ) {
			/**
			 * Filter the list of HTML tags that are valid for use as menu containers.
			 *
			 * @since 3.0.0
			 *
			 * @param array $tags The acceptable HTML tags for use as menu containers.
			 *                    Default is array containing 'div' and 'nav'.
			 */
			$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
			if ( is_string( $args->container ) && in_array( $args->container, $allowed_tags ) ) {
				$show_container = true;
				$class = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
				$id = $args->container_id ? ' id="' . esc_attr( $args->container_id ) . '"' : '';
				$nav_menu .= '<'. $args->container . $id . $class . '>';
			}
		}
	
		// Set up the $menu_item variables
		_wp_menu_item_classes_by_context( $menu_items );
	
		$sorted_menu_items = $menu_items_with_children = array();
		foreach ( (array) $menu_items as $menu_item ) {
			$sorted_menu_items[ $menu_item->menu_order ] = $menu_item;
			if ( $menu_item->menu_item_parent )
				$menu_items_with_children[ $menu_item->menu_item_parent ] = true;
		}
	
		// Add the menu__item-has-children class where applicable
		if ( $menu_items_with_children ) {
			foreach ( $sorted_menu_items as &$menu_item ) {
				if ( isset( $menu_items_with_children[ $menu_item->ID ] ) )
					$menu_item->classes[] = 'menu__item-has-children';
			}
		}
	
		unset( $menu_items, $menu_item );
	
		/**
		 * Filter the sorted list of menu item objects before generating the menu's HTML.
		 *
		 * @since 3.1.0
		 *
		 * @param array  $sorted_menu_items The menu items, sorted by each menu item's menu order.
		 * @param object $args              An object containing wp_nav_menu() arguments.
		 */
		$sorted_menu_items = apply_filters( 'wp_nav_menu_objects', $sorted_menu_items, $args );
	
		$items .= walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
		unset($sorted_menu_items);
	
		// Attributes
		if ( ! empty( $args->menu_id ) ) {
			$wrap_id = $args->menu_id;
		} else {
			$wrap_id = 'menu-' . $menu->slug;
			while ( in_array( $wrap_id, $menu_id_slugs ) ) {
				if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) )
					$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
				else
					$wrap_id = $wrap_id . '-1';
			}
		}
		$menu_id_slugs[] = $wrap_id;
	
		$wrap_class = $args->menu_class ? $args->menu_class : '';
	
		/**
		 * Filter the HTML list content for navigation menus.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $items The HTML list content for the menu items.
		 * @param object $args  An object containing wp_nav_menu() arguments.
		 */
		$items = apply_filters( 'wp_nav_menu_items', $items, $args );
		/**
		 * Filter the HTML list content for a specific navigation menu.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $items The HTML list content for the menu items.
		 * @param object $args  An object containing wp_nav_menu() arguments.
		 */
		$items = apply_filters( "wp_nav_menu_{$menu->slug}_items", $items, $args );
	
		// Don't print any markup if there are no items at this point.
		if ( empty( $items ) )
			return false;
	
		$nav_menu .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
		unset( $items );
	
		if ( $show_container )
			$nav_menu .= '</' . $args->container . '>';
	
		/**
		 * Filter the HTML content for navigation menus.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $nav_menu The HTML content for the navigation menu.
		 * @param object $args     An object containing wp_nav_menu() arguments.
		 */
		$nav_menu = apply_filters( 'wp_nav_menu', $nav_menu, $args );
	
		if ( $args->echo )
			echo $nav_menu;
		else
			return $nav_menu;
	}
	
	
}

LineMenu::initialize();

endif; // endif class_exists



if ( ! class_exists( 'Walker_CookforWeb_Menu' ) ):

/**
 * Navigation Menu template functions
 *
 * @package WordPress
 * @subpackage Nav_Menus
 * @since 3.0.0
 */

/**
 * Create HTML list of nav menu items.
 *
 * @since 3.0.0
 * @uses Walker
 */
class Walker_CookforWeb_Menu extends Walker_Nav_Menu
{
	/**
	 * Start the element output.
	 *
	 * @param  string $output Passed by reference. Used to append additional content.
	 * @param  object $item   Menu item data object.
	 * @param  int $depth     Depth of menu item. May be used for padding.
	 * @param  array $args    Additional strings.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth, $args )
	{
		$output     .= '<li class="menu__item">';
		$attributes  = 'class="menu__link"';
		! empty ( $item->attr_title )
			// Avoid redundant titles
			and $item->attr_title !== $item->title
			and $attributes .= ' title="' . esc_attr( $item->attr_title ) .'"';
		! empty ( $item->url )
			and $attributes .= ' href="' . esc_attr( $item->url ) .'"';
		$attributes  = trim( $attributes );
		$title       = apply_filters( 'the_title', $item->title, $item->ID );
		$item_output = "$args->before<a $attributes>$args->link_before$title</a>"
						. "$args->link_after$args->after";
		// Since $output is called by reference we don't need to return anything.
		$output .= apply_filters(
			'walker_nav_menu_start_el'
			,   $item_output
			,   $item
			,   $depth
			,   $args
		);
	}
	/**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return void
	 */
	public function start_lvl( &$output )
	{
		$output .= '<ul class="sub-menu">';
	}
	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return void
	 */
	public function end_lvl( &$output )
	{
		$output .= '</ul>';
	}
	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return void
	 */
	function end_el( &$output )
	{
		$output .= '</li>';
	}
} // Walker_Nav_Menu

endif;