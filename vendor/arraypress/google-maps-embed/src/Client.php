<?php
/**
 * Google Maps Embed API Client Class
 *
 * @package     ArrayPress\Google\MapsEmbed
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\Google\MapsEmbed;

use InvalidArgumentException;
use WP_Error;

/**
 * Class Client
 *
 * A comprehensive utility class for interacting with the Google Maps Embed API.
 * This class provides methods for generating embeddable Google Maps URLs and iframes
 * with support for various modes including place, search, view, directions, and street view.
 */
class Client {

	/**
	 * Valid travel modes for directions
	 *
	 * @var array<string>
	 */
	private const VALID_MODES = [
		'driving',
		'walking',
		'bicycling',
		'transit'
	];

	/**
	 * Valid map types for view mode
	 *
	 * @var array<string>
	 */
	private const VALID_MAP_TYPES = [
		'roadmap',
		'satellite'
	];

	/**
	 * Valid units for distance measurements
	 *
	 * @var array<string>
	 */
	private const VALID_UNITS = [
		'metric',
		'imperial'
	];

	/**
	 * Valid avoid options for directions
	 *
	 * @var array<string>
	 */
	private const VALID_AVOID = [
		'tolls',
		'ferries',
		'highways'
	];

	/**
	 * Default options for map configuration
	 *
	 * @var array<string, mixed>
	 */
	private const DEFAULT_OPTIONS = [
		'zoom'     => 12,
		'maptype'  => 'roadmap',
		'language' => '',
		'region'   => '',
		'heading'  => 0,
		'pitch'    => 0,
		'fov'      => 90,
		'mode'     => 'driving',
		'avoid'    => [],
		'units'    => 'metric'
	];

	/**
	 * API key for Google Maps
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Base URL for the Maps Embed API
	 *
	 * @var string
	 */
	private const API_ENDPOINT = 'https://www.google.com/maps/embed/v1/';

	/**
	 * Current options for map configuration
	 *
	 * @var array<string, mixed>
	 */
	private array $options;

	/**
	 * Initialize the Maps Embed client
	 *
	 * @param string $api_key Google Maps API key
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
		$this->options = self::DEFAULT_OPTIONS;
	}

	/**
	 * Set travel mode for directions
	 *
	 * @param string $mode Travel mode (driving, walking, bicycling, transit)
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid mode provided
	 */
	public function set_mode( string $mode ): self {
		if ( ! in_array( $mode, self::VALID_MODES ) ) {
			throw new InvalidArgumentException( "Invalid mode. Must be one of: " . implode( ', ', self::VALID_MODES ) );
		}
		$this->options['mode'] = $mode;

		return $this;
	}

	/**
	 * Get current travel mode
	 *
	 * @return string Current travel mode
	 */
	public function get_mode(): string {
		return $this->options['mode'];
	}

	/**
	 * Set map type for view mode
	 *
	 * @param string $type Map type (roadmap, satellite)
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid type provided
	 */
	public function set_map_type( string $type ): self {
		if ( ! in_array( $type, self::VALID_MAP_TYPES ) ) {
			throw new InvalidArgumentException( "Invalid map type. Must be one of: " . implode( ', ', self::VALID_MAP_TYPES ) );
		}
		$this->options['maptype'] = $type;

		return $this;
	}

	/**
	 * Get current map type
	 *
	 * @return string Current map type
	 */
	public function get_map_type(): string {
		return $this->options['maptype'];
	}

	/**
	 * Set units for distance measurements
	 *
	 * @param string $units Units system (metric, imperial)
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid units provided
	 */
	public function set_units( string $units ): self {
		if ( ! in_array( $units, self::VALID_UNITS ) ) {
			throw new InvalidArgumentException( "Invalid units. Must be one of: " . implode( ', ', self::VALID_UNITS ) );
		}
		$this->options['units'] = $units;

		return $this;
	}

	/**
	 * Get current units setting
	 *
	 * @return string Current units system
	 */
	public function get_units(): string {
		return $this->options['units'];
	}

	/**
	 * Set avoid options for directions
	 *
	 * @param array $avoid Features to avoid (tolls, highways, ferries)
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid avoid option provided
	 */
	public function set_avoid( array $avoid ): self {
		$invalid = array_diff( $avoid, self::VALID_AVOID );
		if ( ! empty( $invalid ) ) {
			throw new InvalidArgumentException( "Invalid avoid options: " . implode( ', ', $invalid ) );
		}
		$this->options['avoid'] = $avoid;

		return $this;
	}

	/**
	 * Get current avoid options
	 *
	 * @return array Current avoid settings
	 */
	public function get_avoid(): array {
		return $this->options['avoid'];
	}

	/**
	 * Set zoom level for map
	 *
	 * @param int $level Zoom level (0-21)
	 *                   0: World view
	 *                   5: Continent/Region
	 *                   10: City
	 *                   15: Streets
	 *                   20: Buildings
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid zoom level provided
	 */
	public function set_zoom( int $level ): self {
		if ( $level < 0 || $level > 21 ) {
			throw new InvalidArgumentException( "Invalid zoom level. Must be between 0 and 21." );
		}
		$this->options['zoom'] = $level;

		return $this;
	}

	/**
	 * Get current zoom level
	 *
	 * @return int Current zoom level
	 */
	public function get_zoom(): int {
		return $this->options['zoom'];
	}

	/**
	 * Set heading for street view
	 *
	 * @param float $degrees Heading in degrees (0-360)
	 *                       0: North
	 *                       90: East
	 *                       180: South
	 *                       270: West
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid heading provided
	 */
	public function set_heading( float $degrees ): self {
		if ( $degrees < 0 || $degrees > 360 ) {
			throw new InvalidArgumentException( "Invalid heading. Must be between 0 and 360 degrees." );
		}
		$this->options['heading'] = $degrees;

		return $this;
	}

	/**
	 * Get current heading
	 *
	 * @return float Current heading in degrees
	 */
	public function get_heading(): float {
		return $this->options['heading'];
	}

	/**
	 * Set pitch for street view
	 *
	 * @param float $degrees Pitch in degrees (-90 to 90)
	 *                       -90: Straight down
	 *                       0: Horizontal
	 *                       90: Straight up
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid pitch provided
	 */
	public function set_pitch( float $degrees ): self {
		if ( $degrees < - 90 || $degrees > 90 ) {
			throw new InvalidArgumentException( "Invalid pitch. Must be between -90 and 90 degrees." );
		}
		$this->options['pitch'] = $degrees;

		return $this;
	}

	/**
	 * Get current pitch
	 *
	 * @return float Current pitch in degrees
	 */
	public function get_pitch(): float {
		return $this->options['pitch'];
	}

	/**
	 * Set field of view for street view
	 *
	 * @param float $degrees Field of view in degrees (10-100)
	 *                       Lower values = more zoom
	 *                       Higher values = wider angle
	 *
	 * @return self
	 * @throws InvalidArgumentException If invalid FOV provided
	 */
	public function set_fov( float $degrees ): self {
		if ( $degrees < 10 || $degrees > 100 ) {
			throw new InvalidArgumentException( "Invalid field of view. Must be between 10 and 100 degrees." );
		}
		$this->options['fov'] = $degrees;

		return $this;
	}

	/**
	 * Get current field of view
	 *
	 * @return float Current FOV in degrees
	 */
	public function get_fov(): float {
		return $this->options['fov'];
	}

	/**
	 * Set language for map labels and controls
	 *
	 * @param string $language Language code (e.g., 'en', 'es', 'fr')
	 *                         See: https://developers.google.com/maps/faq#languagesupport
	 *
	 * @return self
	 */
	public function set_language( string $language ): self {
		$this->options['language'] = $language;

		return $this;
	}

	/**
	 * Get current language setting
	 *
	 * @return string Current language code
	 */
	public function get_language(): string {
		return $this->options['language'];
	}

	/**
	 * Set region bias for the map
	 *
	 * @param string $region Region code (e.g., 'US', 'GB')
	 *                       See: https://developers.google.com/maps/coverage
	 *
	 * @return self
	 */
	public function set_region( string $region ): self {
		$this->options['region'] = $region;

		return $this;
	}

	/**
	 * Get current region setting
	 *
	 * @return string Current region code
	 */
	public function get_region(): string {
		return $this->options['region'];
	}

	/**
	 * Get all current options
	 *
	 * @return array<string, mixed> Current options
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * Reset all options to their default values
	 *
	 * @return self
	 */
	public function reset_options(): self {
		$this->options = self::DEFAULT_OPTIONS;

		return $this;
	}

	/**
	 * Get current API key
	 *
	 * @return string Current API key
	 */
	public function get_api_key(): string {
		return $this->api_key;
	}

	/**
	 * Set new API key
	 *
	 * @param string $api_key The API key to use
	 *
	 * @return self
	 */
	public function set_api_key( string $api_key ): self {
		$this->api_key = $api_key;

		return $this;
	}

	/**
	 * Generate embed URL for a place
	 *
	 * @param string $place_id Google Place ID
	 * @param array  $options  Additional options for the embed
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	public function place( string $place_id, array $options = [] ) {
		$params = array_merge(
			[ 'q' => 'place_id:' . $place_id ],
			$this->get_common_options(),
			$options
		);

		return $this->generate_url( 'place', $params );
	}

	/**
	 * Generate embed URL for a search query
	 *
	 * @param string $query   Search query
	 * @param array  $options Additional options for the embed
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	public function search( string $query, array $options = [] ) {
		$params = array_merge(
			[ 'q' => $query ],
			$this->get_common_options(),
			$options
		);

		return $this->generate_url( 'search', $params );
	}

	/**
	 * Generate embed URL for a specific view
	 *
	 * @param float $latitude  Latitude coordinate
	 * @param float $longitude Longitude coordinate
	 * @param array $options   Additional options for the embed
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	public function view( float $latitude, float $longitude, array $options = [] ) {
		$params = array_merge(
			[
				'center'  => "{$latitude},{$longitude}",
				'zoom'    => $this->options['zoom'],
				'maptype' => $this->options['maptype']
			],
			$this->get_common_options(),
			$options
		);

		return $this->generate_url( 'view', $params );
	}

	/**
	 * Generate embed URL for directions
	 *
	 * @param string $origin      Starting location
	 * @param string $destination Ending location
	 * @param array  $options     Additional options for the embed
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	public function directions( string $origin, string $destination, array $options = [] ) {
		$params = [
			'origin'      => $origin,
			'destination' => $destination,
			'mode'        => $this->options['mode']
		];

		if ( ! empty( $this->options['avoid'] ) ) {
			$params['avoid'] = implode( '|', $this->options['avoid'] );
		}

		if ( ! empty( $this->options['units'] ) ) {
			$params['units'] = $this->options['units'];
		}

		$params = array_merge(
			$params,
			$this->get_common_options(),
			$options
		);

		return $this->generate_url( 'directions', $params );
	}

	/**
	 * Generate embed URL for street view
	 *
	 * @param float $latitude  Latitude coordinate
	 * @param float $longitude Longitude coordinate
	 * @param array $options   Additional options for the embed
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	public function streetview( float $latitude, float $longitude, array $options = [] ) {
		$params = [
			'location' => "{$latitude},{$longitude}"
		];

		// Add non-zero camera parameters
		if ( $this->options['heading'] !== 0 ) {
			$params['heading'] = $this->options['heading'];
		}
		if ( $this->options['pitch'] !== 0 ) {
			$params['pitch'] = $this->options['pitch'];
		}
		if ( $this->options['fov'] !== 90 ) {
			$params['fov'] = $this->options['fov'];
		}

		$params = array_merge(
			$params,
			$this->get_common_options(),
			$options
		);

		return $this->generate_url( 'streetview', $params );
	}

	/**
	 * Generate the complete iframe HTML
	 *
	 * @param string $url   The embed URL
	 * @param array  $attrs Additional iframe attributes
	 *
	 * @return string Complete iframe HTML
	 */
	public function generate_iframe( string $url, array $attrs = [] ): string {
		$default_attrs = [
			'width'           => '600',
			'height'          => '450',
			'frameborder'     => '0',
			'style'           => 'border:0',
			'allowfullscreen' => true,
			'loading'         => 'lazy',
			'referrerpolicy'  => 'no-referrer-when-downgrade'
		];

		$merged_attrs = array_merge( $default_attrs, $attrs );
		$attr_string  = '';

		foreach ( $merged_attrs as $key => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$attr_string .= " $key";
				}
			} else {
				$attr_string .= " $key=\"" . esc_attr( $value ) . "\"";
			}
		}

		return sprintf(
			'<iframe src="%s"%s></iframe>',
			esc_url( $url ),
			$attr_string
		);
	}

	/**
	 * Get common options that apply to all modes
	 *
	 * @return array<string, string> Common options
	 */
	private function get_common_options(): array {
		$common = [];
		if ( ! empty( $this->options['language'] ) ) {
			$common['language'] = $this->options['language'];
		}
		if ( ! empty( $this->options['region'] ) ) {
			$common['region'] = $this->options['region'];
		}

		return $common;
	}

	/**
	 * Generate the API URL
	 *
	 * @param string $mode   The embed mode
	 * @param array  $params URL parameters
	 *
	 * @return string|WP_Error URL for the embed or WP_Error on failure
	 */
	private function generate_url( string $mode, array $params = [] ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error(
				'missing_api_key',
				__( 'Google Maps API key is required', 'arraypress' )
			);
		}

		$params['key'] = $this->api_key;
		$url           = self::API_ENDPOINT . $mode;

		return add_query_arg( $params, $url );
	}

	/**
	 * Validate required parameters
	 *
	 * @param array $params   Parameters to validate
	 * @param array $required Required parameter keys
	 *
	 * @return bool|WP_Error True if valid, WP_Error if missing required params
	 */
	private function validate_params( array $params, array $required ) {
		$missing = array_diff( $required, array_keys( $params ) );

		if ( ! empty( $missing ) ) {
			return new WP_Error(
				'missing_params',
				sprintf(
					__( 'Missing required parameters: %s', 'arraypress' ),
					implode( ', ', $missing )
				)
			);
		}

		return true;
	}

}