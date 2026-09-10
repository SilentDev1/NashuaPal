<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Walker for the mobile menu drawer. Adds:
 *   - a chevron button to top-level items that have children, so users can
 *     toggle the submenu without leaving the drawer
 *   - is-toggle wrapper class on parent <li> items (used by the JS to show
 *     the submenu when the chevron is tapped)
 *
 * Falls back to the standard Walker_Nav_Menu rendering for everything else.
 */
class NPAL_Mobile_Menu_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class=\"sub-menu pal-mobile-menu__sub\">\n";
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$has_children = ! empty( $args->walker->has_children );

		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		if ( $has_children ) {
			$classes[] = 'pal-mobile-menu__has-children';
		}
		$class_names = join( ' ', array_filter( array_map( 'sanitize_html_class', $classes ) ) );

		$output .= '<li class="' . esc_attr( $class_names ) . '">';

		$attrs  = '';
		if ( ! empty( $item->attr_title ) ) { $attrs .= ' title="' . esc_attr( $item->attr_title ) . '"'; }
		if ( ! empty( $item->target ) )     { $attrs .= ' target="' . esc_attr( $item->target ) . '"'; }
		if ( ! empty( $item->xfn ) )        { $attrs .= ' rel="' . esc_attr( $item->xfn ) . '"'; }
		if ( ! empty( $item->url ) )        { $attrs .= ' href="' . esc_url( $item->url ) . '"'; }

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		// Resolve menu-item icon from (in order of priority):
		//   1. `_pal_menu_icon` post meta — set via the "Icon" input added
		//      to each menu item in Appearance → Menus (1.8.47+)
		//   2. `pal-mi-<icon-name>` CSS class on the menu item — legacy
		//      fallback (1.8.45) for power users who prefer the CSS-class
		//      route or who imported menus with classes already set.
		$icon_value = '';
		if ( class_exists( 'NPAL_Menu_Icons' ) ) {
			$icon_value = NPAL_Menu_Icons::get_for_item( $item->ID );
		}
		if ( '' === $icon_value ) {
			foreach ( $classes as $cls ) {
				if ( 0 === strpos( $cls, 'pal-mi-' ) ) {
					$icon_value = substr( $cls, 7 );
					// Translate `fas-fa-bell` → `fas fa-bell` so render_or_fa() routes as FA.
					if ( 0 === strpos( $icon_value, 'fas-fa-' ) || 0 === strpos( $icon_value, 'far-fa-' ) || 0 === strpos( $icon_value, 'fab-fa-' ) ) {
						$icon_value = preg_replace( '/-/', ' ', $icon_value, 1 );
						$icon_value = preg_replace( '/-/', ' ', $icon_value, 1 );
					}
					break;
				}
			}
		}
		$icon_html = '';
		if ( $icon_value ) {
			$icon_html = '<span class="pal-mobile-menu__item-icon" aria-hidden="true">' . NPAL_Icons::render_or_fa( $icon_value ) . '</span>';
		}

		$output .= '<a' . $attrs . '>' . $icon_html . esc_html( $title ) . '</a>';

		if ( $has_children && 0 === $depth ) {
			$output .= '<button type="button" class="pal-mobile-menu__chevron" data-pal-mobile-submenu-toggle aria-expanded="false" aria-label="' . esc_attr__( 'Expand submenu', 'nashua-pal-core' ) . '">';
			$output .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
			$output .= '</button>';
		}
	}
}
