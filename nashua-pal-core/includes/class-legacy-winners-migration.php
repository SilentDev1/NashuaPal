<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * One-time migration: bulk-insert historical Night of Champions winners (2011–2024)
 * who only have a name + year + category (no photo or bio).
 *
 * Runs once via admin_init, keyed by option 'npal_legacy_winners_migrated'.
 *
 * @package NashuaPalCore
 * @since   1.55.24
 */
final class NPAL_Legacy_Winners_Migration {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_run' ) );
	}

	public function maybe_run() {
		if ( get_option( 'npal_legacy_winners_migrated' ) ) { return; }
		$this->run();
		update_option( 'npal_legacy_winners_migrated', 1 );
	}

	/**
	 * All historical winners data.
	 * Format: category_slug => array of [ year, name, award_title (optional) ]
	 */
	private function get_data() {
		return array(

			/* ── Football Player of the Year ─────────────────────── */
			'football-player-of-the-year' => array(
				array( '2024', 'William Osborn', 'Football Player of the Year' ),
				array( '2023', 'Isaac Bernard', 'Football Player of the Year' ),
				array( '2022', 'Josiah Spleen', 'Football Player of the Year' ),
				array( '2021', 'Jack Krulikowski', 'Football Player of the Year' ),
				array( '2019', 'Bryson Borromeo', 'Football Player of the Year' ),
				array( '2018', 'Reagan Brunet', 'Football Player of the Year' ),
				array( '2017', 'Max Ackerman', 'Football Player of the Year' ),
				array( '2016', 'Robbie Long', 'Football Player of the Year' ),
				array( '2015', 'Austin Ouellette', 'Football Player of the Year' ),
				array( '2014', 'Matt Hallahan', 'Football Player of the Year' ),
				array( '2013', 'Nevan Roy', 'Football Player of the Year' ),
				array( '2012', 'Greg Stanton', 'Football Player of the Year' ),
				array( '2011', 'Alex White', 'Football Player of the Year' ),
			),

			/* ── Jr. PAL of the Year ─────────────────────────────── */
			'jr-pal-of-the-year' => array(
				array( '2024', 'Jhon Almonte Vidal', 'Jr. PAL of the Year' ),
				array( '2023', 'Fatima Garcia', 'Jr. PAL of the Year' ),
				array( '2022', 'Kane Kyer', 'Jr. PAL of the Year' ),
				array( '2021', 'Junely Grullon', 'Jr. PAL of the Year' ),
				array( '2019', 'Yahir Alvarado', 'Jr. PAL of the Year' ),
				array( '2018', 'Adriana Flores', 'Jr. PAL of the Year' ),
				array( '2017', 'Marilyn Guijarro', 'Jr. PAL of the Year' ),
				array( '2016', 'Junely Grullon', 'Jr. PAL of the Year' ),
				array( '2015', 'Alejandra Ibarra', 'Jr. PAL of the Year' ),
				array( '2014', 'Alejandra Ibarra', 'Jr. PAL of the Year' ),
				array( '2013', 'Seth Moore', 'Jr. PAL of the Year' ),
				array( '2012', 'Andrew Nunes', 'Jr. PAL of the Year' ),
			),

			/* ── PAL Female Cross Country Runner of the Year ─────── */
			'pal-female-cross-country-runner-of-the-year' => array(
				array( '2024', 'Zoe Coler', 'Female Cross Country Runner of the Year' ),
				array( '2023', 'Rosalie Neveu', 'Female Cross Country Runner of the Year' ),
				array( '2022', 'Laura White', 'Female Cross Country Runner of the Year' ),
				array( '2021', 'Laura White', 'Female Cross Country Runner of the Year' ),
				array( '2019', 'Lauren Long', 'Female Cross Country Runner of the Year' ),
				array( '2018', 'Jhanelle Thomas', 'Female Cross Country Runner of the Year' ),
				array( '2017', 'Cali Coffin', 'Female Cross Country Runner of the Year' ),
				array( '2016', 'Shannon Earley', 'Female Cross Country Runner of the Year' ),
				array( '2015', 'Grace Jones', 'Female Cross Country Runner of the Year' ),
				array( '2014', 'Lauren Robinson', 'Female Cross Country Runner of the Year' ),
				array( '2013', 'Lauren Robinson', 'Female Cross Country Runner of the Year' ),
				array( '2012', 'Tylee Thurber', 'Female Cross Country Runner of the Year' ),
				array( '2011', 'Olivia Koutsos', 'Female Cross Country Runner of the Year' ),
			),

			/* ── Track & Field Runner of the Year ────────────────── */
			'track-field-runner-of-the-year' => array(
				array( '2024', 'Jaylyn Paul', 'Track & Field Runner of the Year' ),
			),

			/* ── PAL Cheerleader of the Year ─────────────────────── */
			'pal-cheerleader-of-the-year' => array(
				array( '2024', 'Naomi Frotten', 'Cheerleader of the Year' ),
				array( '2023', 'Kylie Ansaldi', 'Cheerleader of the Year' ),
				array( '2022', 'Liz Forcier', 'Cheerleader of the Year' ),
				array( '2021', 'Kylee Flynn', 'Cheerleader of the Year' ),
				array( '2019', 'Ashley Oliver', 'Cheerleader of the Year' ),
				array( '2018', 'Adrianna Milano', 'Cheerleader of the Year' ),
				array( '2017', 'Emily Wall', 'Cheerleader of the Year' ),
				array( '2016', 'Alyiah Brown', 'Cheerleader of the Year' ),
				array( '2015', 'Paige Brooks', 'Cheerleader of the Year' ),
				array( '2014', 'Hannah Anderson', 'Cheerleader of the Year' ),
				array( '2013', 'Kelsi Cora', 'Cheerleader of the Year' ),
				array( '2012', 'Lauren Grabeau', 'Cheerleader of the Year' ),
				array( '2011', 'Elisabeth Carroll', 'Cheerleader of the Year' ),
			),

			/* ── PAL Basketball Player of the Year ───────────────── */
			'pal-basketball-player-of-the-year' => array(
				array( '2022', 'Shwarlin "Swirl" Eli Arriola, Jr', 'Basketball Player of the Year' ),
				array( '2021', 'Nick Reyes', 'Basketball Player of the Year' ),
				array( '2019', 'Jayden Espinal', 'Basketball Player of the Year' ),
				array( '2018', 'Brian Montminy', 'Basketball Player of the Year' ),
				array( '2017', 'Ben Cabral', 'Basketball Player of the Year' ),
				array( '2016', 'Brian Montminy', 'Basketball Player of the Year' ),
			),

			/* ── PAL Male Cross Country Runner ───────────────────── */
			'pal-male-cross-country-runner' => array(
				array( '2024', 'Tristan Plumb', 'Male Cross Country Runner of the Year' ),
				array( '2023', 'Evan Jean', 'Male Cross Country Runner of the Year' ),
				array( '2022', 'Ethan Fischer', 'Male Cross Country Runner of the Year' ),
				array( '2021', 'Matt Giardina', 'Male Cross Country Runner of the Year' ),
				array( '2019', 'Matthew Morris', 'Male Cross Country Runner of the Year' ),
				array( '2018', 'Paul Balletto', 'Male Cross Country Runner of the Year' ),
				array( '2017', 'Garrett Earley', 'Male Cross Country Runner of the Year' ),
				array( '2016', 'Carson Bottomley', 'Male Cross Country Runner of the Year' ),
				array( '2015', 'Forest MacKenzie', 'Male Cross Country Runner of the Year' ),
				array( '2014', 'Aiden van Batenburg', 'Male Cross Country Runner of the Year' ),
				array( '2013', 'Noah Bellomo', 'Male Cross Country Runner of the Year' ),
				array( '2012', 'Ryan Vermette', 'Male Cross Country Runner of the Year' ),
				array( '2011', 'Ethan Michaud', 'Male Cross Country Runner of the Year' ),
			),

			/* ── PAL Boxer of the Year ───────────────────────────── */
			'pal-boxer-of-the-year' => array(
				array( '2023', 'Jayden Maldonado', 'Boxer of the Year' ),
				array( '2022', 'Christian Moura', 'Boxer of the Year' ),
				array( '2021', 'Jayden Maldonado', 'Boxer of the Year' ),
				array( '2019', 'Jesse Batanda', 'Boxer of the Year' ),
				array( '2018', 'Mike Fontanez', 'Boxer of the Year' ),
				array( '2017', 'Joe Valdez', 'Boxer of the Year' ),
				array( '2016', 'Marco Uribe', 'Boxer of the Year' ),
				array( '2015', 'Keegan Murphy', 'Boxer of the Year' ),
				array( '2014', 'Matt Fontanez', 'Boxer of the Year' ),
				array( '2013', 'Bryce Landry', 'Boxer of the Year' ),
				array( '2012', 'Ronny Angeles', 'Boxer of the Year' ),
				array( '2011', 'Brian Allen', 'Boxer of the Year' ),
			),

			/* ── PAL Street Hockey Player of the Year ────────────── */
			'pal-street-hockey-player-of-the-year' => array(
				array( '2024', 'Jack Russin-Morgan', 'Street Hockey Player of the Year' ),
			),

			/* ── PAL Coach of the Year ───────────────────────────── */
			'pal-coach-of-the-year' => array(
				array( '2024', 'Johanna Lisle Newbold', 'Coach of the Year — Track & Field' ),
				array( '2023', 'Nadia Calden', 'Coach of the Year — Cheerleading' ),
				array( '2022', 'Mike Dunhom', 'Coach of the Year — Cross Country' ),
				array( '2021', 'Ben Desfosses', 'Coach of the Year — Football' ),
				array( '2019', 'Judy Biore', 'Coach of the Year — Cross Country' ),
				array( '2018', 'Jeff Kreick', 'Coach of the Year — Basketball' ),
				array( '2017', 'Mohammed Brooks', 'Coach of the Year — Boxing' ),
				array( '2016', 'Sergi Filonow', 'Coach of the Year — Football' ),
				array( '2015', 'Eric Fowler', 'Coach of the Year — Football' ),
				array( '2014', 'Tom Cassetty', 'Coach of the Year — Cross Country' ),
				array( '2013', 'Maureen MacKay', 'Coach of the Year — Cheerleading' ),
				array( '2012', 'Karen Long', 'Coach of the Year — Cross Country' ),
				array( '2012', 'Brendan Keegan', 'Coach of the Year — Football' ),
				array( '2011', 'Frank Chimento', 'Coach of the Year — Boxing' ),
			),

			/* ── PAL Volunteer of the Year ───────────────────────── */
			'pal-volunteer-of-the-year' => array(
				array( '2024', 'Brianne Healey', 'Volunteer of the Year' ),
				array( '2023', 'Officer (ret.) Kevin Pucillo', 'Volunteer of the Year' ),
				array( '2022', 'Don McCarty', 'Volunteer of the Year' ),
				array( '2021', 'Lt. Carlos Camacho', 'Volunteer of the Year — PAL YSH' ),
				array( '2019', 'Gwen Krailo', 'Volunteer of the Year — PAL Board' ),
				array( '2018', 'Pastor Dave Smith', 'Volunteer of the Year — PAL YSH' ),
				array( '2017', 'Kalin Hamilton', 'Volunteer of the Year — PAL Force' ),
				array( '2016', 'Laurie Galbo', 'Volunteer of the Year — PAL YSH' ),
				array( '2015', 'Stacey Geesey', 'Volunteer of the Year — PAL YSH' ),
				array( '2014', 'Nick Dahl', 'Volunteer of the Year — Athletic Director & Board Member' ),
				array( '2013', 'Jessica Coughlin', 'Volunteer of the Year — PAL YSH' ),
				array( '2013', 'Brad Kreick', 'Volunteer of the Year — Corporate Partner' ),
				array( '2012', 'Kevin McIntyre', 'Volunteer of the Year — Cross Country' ),
				array( '2011', 'Ken Hamill', 'Volunteer of the Year — PAL Golf Classic' ),
			),
		);
	}

	private function run() {
		$data = $this->get_data();

		foreach ( $data as $cat_slug => $winners ) {
			foreach ( $winners as $entry ) {
				$year        = $entry[0];
				$name        = $entry[1];
				$award_title = isset( $entry[2] ) ? $entry[2] : '';

				// Skip if a post with this name + year + category already exists.
				if ( $this->exists( $name, $year, $cat_slug ) ) {
					continue;
				}

				$post_id = wp_insert_post( array(
					'post_type'   => NPAL_CPT_Champions_Winner::POST_TYPE,
					'post_title'  => $name . ' — ' . $year,
					'post_status' => 'publish',
					'meta_input'  => array(
						'_pal_champion_name'        => $name,
						'_pal_champion_year'        => $year,
						'_pal_champion_award_title' => $award_title,
						'_pal_champion_featured'    => '0',
						'_pal_champion_order'       => '50',
					),
				), true );

				if ( is_wp_error( $post_id ) ) { continue; }

				// Assign category taxonomy.
				wp_set_object_terms( $post_id, $cat_slug, NPAL_CPT_Champions_Winner::TAXONOMY );
			}
		}
	}

	/**
	 * Check if a winner already exists by name + year + category.
	 */
	private function exists( $name, $year, $cat_slug ) {
		$query = new WP_Query( array(
			'post_type'      => NPAL_CPT_Champions_Winner::POST_TYPE,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_pal_champion_name', 'value' => $name ),
				array( 'key' => '_pal_champion_year', 'value' => $year ),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => NPAL_CPT_Champions_Winner::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $cat_slug,
				),
			),
		) );
		return $query->found_posts > 0;
	}
}

NPAL_Legacy_Winners_Migration::instance();
