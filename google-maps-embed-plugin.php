<?php
/**
 * ArrayPress - Google Maps Embed Tester
 *
 * @package     ArrayPress\Google\MapsEmbed
 * @author      David Sherlock
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @link        https://arraypress.com/
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:         ArrayPress - Google Maps Embed Tester
 * Plugin URI:          https://github.com/arraypress/google-maps-embed-plugin
 * Description:         A plugin to test and demonstrate the Google Maps Embed API integration.
 * Version:             1.0.0
 * Requires at least:   6.7.1
 * Requires PHP:        7.4
 * Author:              David Sherlock
 * Author URI:          https://arraypress.com/
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         arraypress-maps-embed
 * Domain Path:         /languages
 * Network:             false
 * Update URI:          false
 */

declare( strict_types=1 );

namespace ArrayPress\Google\MapsEmbed;

// Exit if accessed directly
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class Plugin
 *
 * Handles the WordPress plugin functionality for testing the Google Maps Embed API.
 *
 * @package ArrayPress\Google\MapsEmbed
 */
class Plugin {

	/**
	 * API Client instance
	 *
	 * @var Client|null Google Maps Embed API client
	 */
	private ?Client $client = null;

	/**
	 * Hook name for the Google Timezone Detection admin page.
	 *
	 * @var string
	 */
	const MENU_HOOK = 'google_page_arraypress-google-maps-embed';

	/**
	 * Plugin constructor. Sets up hooks and initializes client if API key exists.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		$api_key = get_option( 'google_maps_embed_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->client = new Client( $api_key );
		}
	}

	/**
	 * Load plugin text domain for translations
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'arraypress-google-maps-embed',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Enqueue admin assets for the plugin's pages
	 *
	 * @param string $hook Current admin page hook
	 */
	public function enqueue_admin_assets( string $hook ): void {
		if ( $hook !== self::MENU_HOOK ) {
			return;
		}

		wp_enqueue_style(
			'google-maps-embed-test-admin',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			[],
			'1.0.0'
		);
	}

	/**
	 * Registers the Google menu and timezone detection submenu page in the WordPress admin.
	 *
	 * This method handles the creation of a shared Google menu across plugins (if it doesn't
	 * already exist) and adds the Timezone Detection tool as a submenu item. It also removes
	 * the default submenu item to prevent a blank landing page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		// Only add the main Google menu if it doesn't exist yet
		global $admin_page_hooks;

		if ( ! isset( $admin_page_hooks['arraypress-google'] ) ) {
			add_menu_page(
				__( 'Google', 'arraypress-google-address-validation' ),
				__( 'Google', 'arraypress-google-address-validation' ),
				'manage_options',
				'arraypress-google',
				null,
				'dashicons-google',
				30
			);
		}

		// Add the address validation submenu
		add_submenu_page(
			'arraypress-google',
			__( 'Maps Embed', 'arraypress-google-address-validation' ),
			__( 'Maps Embed', 'arraypress-google-address-validation' ),
			'manage_options',
			'arraypress-google-maps-embed',
			[ $this, 'render_test_page' ]
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings(): void {
		register_setting( 'maps_embed_settings', 'google_maps_embed_api_key' );
	}

	/**
	 * Render the main test page interface
	 */
	public function render_test_page(): void {
		$results = $this->process_form_submissions();
		?>
        <div class="wrap maps-embed-test">
            <h1><?php _e( 'Google Maps Embed API Test', 'arraypress-google-maps-embed' ); ?></h1>

			<?php settings_errors( 'maps_embed_test' ); ?>

			<?php if ( empty( get_option( 'google_maps_embed_api_key' ) ) ): ?>
                <div class="notice notice-warning">
                    <p><?php _e( 'Please enter your Google Maps API key to begin testing.', 'arraypress-google-maps-embed' ); ?></p>
                </div>
				<?php $this->render_settings_form(); ?>
			<?php else: ?>
                <div class="maps-embed-test-container">
                    <div class="maps-embed-test-section">
                        <h2><?php _e( 'Map Embed Generator', 'arraypress-google-maps-embed' ); ?></h2>
                        <form method="post" class="maps-embed-form">
							<?php wp_nonce_field( 'maps_embed_test' ); ?>

                            <!-- Common Settings -->
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="embed_mode"><?php _e( 'Embed Mode', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="embed_mode" id="embed_mode" class="regular-text">
                                            <option value="place" <?php selected( $results['mode'], 'place' ); ?>>
												<?php _e( 'Place', 'arraypress-google-maps-embed' ); ?>
                                            </option>
                                            <option value="search" <?php selected( $results['mode'], 'search' ); ?>>
												<?php _e( 'Search', 'arraypress-google-maps-embed' ); ?>
                                            </option>
                                            <option value="view" <?php selected( $results['mode'], 'view' ); ?>>
												<?php _e( 'View', 'arraypress-google-maps-embed' ); ?>
                                            </option>
                                            <option value="directions" <?php selected( $results['mode'], 'directions' ); ?>>
												<?php _e( 'Directions', 'arraypress-google-maps-embed' ); ?>
                                            </option>
                                            <option value="streetview" <?php selected( $results['mode'], 'streetview' ); ?>>
												<?php _e( 'Street View', 'arraypress-google-maps-embed' ); ?>
                                            </option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- Common Map Options -->
                                <tr class="embed-mode view">
                                    <th scope="row">
                                        <label for="zoom"><?php _e( 'Zoom Level', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="zoom" id="zoom" class="regular-text">
											<?php for ( $i = 0; $i <= 21; $i ++ ): ?>
                                                <option value="<?php echo $i; ?>" <?php selected( $i, 12 ); ?>>
													<?php echo $i; ?>
                                                </option>
											<?php endfor; ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="embed-mode view">
                                    <th scope="row">
                                        <label for="maptype"><?php _e( 'Map Type', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="maptype" id="maptype" class="regular-text">
                                            <option value="roadmap"><?php _e( 'Road Map', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="satellite"><?php _e( 'Satellite', 'arraypress-google-maps-embed' ); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        <label for="language"><?php _e( 'Language', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="language" id="language" class="regular-text">
                                            <option value=""><?php _e( 'Default', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="en"><?php _e( 'English', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="es"><?php _e( 'Spanish', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="fr"><?php _e( 'French', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="de"><?php _e( 'German', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="it"><?php _e( 'Italian', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="ja"><?php _e( 'Japanese', 'arraypress-google-maps-embed' ); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <!-- Place Mode Fields -->
                                <tr class="embed-mode place">
                                    <th scope="row">
                                        <label for="place_id"><?php _e( 'Place ID', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="place_id" id="place_id" class="regular-text" value="ChIJN1t_tDeuEmsRUsoyG83frY4" placeholder="<?php esc_attr_e( 'Enter Google Place ID...', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Enter a valid Google Place ID (e.g., ChIJN1t_tDeuEmsRUsoyG83frY4)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <!-- Search Mode Fields -->
                                <tr class="embed-mode search">
                                    <th scope="row">
                                        <label for="search_query"><?php _e( 'Search Query', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="search_query" id="search_query" class="regular-text" value="Coffee shops in Seattle" placeholder="<?php esc_attr_e( 'Enter search query...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>

                                <!-- View Mode Fields -->
                                <tr class="embed-mode view">
                                    <th scope="row">
                                        <label for="latitude"><?php _e( 'Latitude', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="latitude" id="latitude" class="regular-text" value="47.6062" step="any" placeholder="<?php esc_attr_e( 'Enter latitude...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>
                                <tr class="embed-mode view">
                                    <th scope="row">
                                        <label for="longitude"><?php _e( 'Longitude', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="longitude" id="longitude" class="regular-text" value="-122.3321" step="any" placeholder="<?php esc_attr_e( 'Enter longitude...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>

                                <!-- Directions Mode Fields -->
                                <tr class="embed-mode directions">
                                    <th scope="row">
                                        <label for="origin"><?php _e( 'Origin', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="origin" id="origin" class="regular-text" value="Seattle, WA" placeholder="<?php esc_attr_e( 'Enter starting point...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>
                                <tr class="embed-mode directions">
                                    <th scope="row">
                                        <label for="destination"><?php _e( 'Destination', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="destination" id="destination" class="regular-text" value="Portland, OR" placeholder="<?php esc_attr_e( 'Enter destination...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>

                                <tr class="embed-mode directions">
                                    <th scope="row">
                                        <label for="travel_mode"><?php _e( 'Travel Mode', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="travel_mode" id="travel_mode" class="regular-text">
                                            <option value="driving"><?php _e( 'Driving', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="walking"><?php _e( 'Walking', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="bicycling"><?php _e( 'Bicycling', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="transit"><?php _e( 'Transit', 'arraypress-google-maps-embed' ); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="embed-mode directions">
                                    <th scope="row">
                                        <label for="units"><?php _e( 'Units', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="units" id="units" class="regular-text">
                                            <option value="metric"><?php _e( 'Metric', 'arraypress-google-maps-embed' ); ?></option>
                                            <option value="imperial"><?php _e( 'Imperial', 'arraypress-google-maps-embed' ); ?></option>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="embed-mode directions">
                                    <th scope="row">
                                        <label><?php _e( 'Avoid', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="avoid_routes[]" value="tolls">
											<?php _e( 'Tolls', 'arraypress-google-maps-embed' ); ?>
                                        </label><br>
                                        <label>
                                            <input type="checkbox" name="avoid_routes[]" value="highways">
											<?php _e( 'Highways', 'arraypress-google-maps-embed' ); ?>
                                        </label><br>
                                        <label>
                                            <input type="checkbox" name="avoid_routes[]" value="ferries">
											<?php _e( 'Ferries', 'arraypress-google-maps-embed' ); ?>
                                        </label>
                                    </td>
                                </tr>

                                <!-- Street View Mode Fields -->
                                <tr class="embed-mode streetview">
                                    <th scope="row">
                                        <label for="sv_latitude"><?php _e( 'Latitude', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="sv_latitude" id="sv_latitude" class="regular-text" value="48.8584" step="any" placeholder="<?php esc_attr_e( 'Enter latitude...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>
                                <tr class="embed-mode streetview">
                                    <th scope="row">
                                        <label for="sv_longitude"><?php _e( 'Longitude', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="sv_longitude" id="sv_longitude" class="regular-text" value="2.2945" step="any" placeholder="<?php esc_attr_e( 'Enter longitude...', 'arraypress-google-maps-embed' ); ?>">
                                    </td>
                                </tr>

                                <tr class="embed-mode streetview">
                                    <th scope="row">
                                        <label for="heading"><?php _e( 'Heading', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="heading" id="heading" class="regular-text" value="0" min="0" max="360" step="1" placeholder="<?php esc_attr_e( '0-360 degrees', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Camera heading in degrees (0=North, 90=East, 180=South, 270=West)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr class="embed-mode streetview">
                                    <th scope="row">
                                        <label for="pitch"><?php _e( 'Pitch', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="pitch" id="pitch" class="regular-text" value="0" min="-90" max="90" step="1" placeholder="<?php esc_attr_e( '-90 to 90 degrees', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Camera pitch in degrees (-90=straight down, 0=horizontal, 90=straight up)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr class="embed-mode streetview">
                                    <th scope="row">
                                        <label for="fov"><?php _e( 'Field of View', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="fov" id="fov" class="regular-text" value="90" min="10" max="100" step="1" placeholder="<?php esc_attr_e( '10-100 degrees', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Field of view in degrees (smaller=more zoom, larger=wider angle)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <!-- Common Fields -->
                                <tr>
                                    <th scope="row">
                                        <label for="width"><?php _e( 'Width', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="width" id="width" class="regular-text" value="600" placeholder="<?php esc_attr_e( 'Enter width...', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Width in pixels or percentage (e.g., 600 or 100%)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="height"><?php _e( 'Height', 'arraypress-google-maps-embed' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="height" id="height" class="regular-text" value="450" placeholder="<?php esc_attr_e( 'Enter height...', 'arraypress-google-maps-embed' ); ?>">
                                        <p class="description">
											<?php _e( 'Height in pixels (e.g., 450)', 'arraypress-google-maps-embed' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>

							<?php submit_button( __( 'Generate Embed', 'arraypress-google-maps-embed' ), 'primary', 'submit_embed' ); ?>
                        </form>

						<?php if ( $results['embed'] ): ?>
                            <h3><?php _e( 'Generated Embed', 'arraypress-google-maps-embed' ); ?></h3>
                            <div class="embed-preview">
								<?php echo $results['embed']; ?>
                            </div>
                            <div class="embed-code">
                                <h4><?php _e( 'Embed Code', 'arraypress-google-maps-embed' ); ?></h4>
                                <textarea class="widefat" rows="4" onclick="this.select()"><?php echo esc_textarea( $results['embed'] ); ?></textarea>
                            </div>
						<?php endif; ?>
                    </div>
                </div>

                <!-- Settings -->
                <div class="maps-embed-test-section">
					<?php $this->render_settings_form(); ?>
                </div>
			<?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                // Function to show/hide fields based on selected mode
                function toggleFields() {
                    var mode = $('#embed_mode').val();
                    $('.embed-mode').hide();
                    $('.embed-mode.' + mode).show();
                }

                // Initial toggle
                toggleFields();

                // Toggle on mode change
                $('#embed_mode').on('change', toggleFields);
            });
        </script>
		<?php
	}

	/**
	 * Render the API key settings form
	 */
	private function render_settings_form(): void {
		?>
        <h2><?php _e( 'Settings', 'arraypress-google-maps-embed' ); ?></h2>
        <form method="post" class="maps-embed-form">
			<?php wp_nonce_field( 'maps_embed_api_key' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="google_maps_embed_api_key"><?php _e( 'API Key', 'arraypress-google-maps-embed' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="google_maps_embed_api_key" id="google_maps_embed_api_key" class="regular-text" value="<?php echo esc_attr( get_option( 'google_maps_embed_api_key' ) ); ?>" placeholder="<?php esc_attr_e( 'Enter your Google Maps API key...', 'arraypress-google-maps-embed' ); ?>">
                        <p class="description">
							<?php _e( 'Your Google Maps API key. Required for making API requests.', 'arraypress-google-maps-embed' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
			<?php submit_button(
				empty( get_option( 'google_maps_embed_api_key' ) )
					? __( 'Save Settings', 'arraypress-google-maps-embed' )
					: __( 'Update Settings', 'arraypress-google-maps-embed' ),
				'primary',
				'submit_api_key'
			); ?>
        </form>
		<?php
	}

	/**
	 * Process form submissions for API key and embed generation
	 *
	 * @return array Results containing generated embed code and selected mode
	 */
	private function process_form_submissions(): array {
		$results = [
			'embed' => null,
			'mode'  => null
		];

		if ( isset( $_POST['submit_api_key'] ) ) {
			check_admin_referer( 'maps_embed_api_key' );
			$api_key = sanitize_text_field( $_POST['google_maps_embed_api_key'] );
			update_option( 'google_maps_embed_api_key', $api_key );
			$this->client = new Client( $api_key );
		}

		if ( ! $this->client ) {
			return $results;
		}

		if ( isset( $_POST['submit_embed'] ) ) {
			check_admin_referer( 'maps_embed_test' );

			$mode            = sanitize_text_field( $_POST['embed_mode'] );
			$results['mode'] = $mode;

			try {
				// Set common options
				if ( ! empty( $_POST['zoom'] ) ) {
					$this->client->set_zoom( (int) $_POST['zoom'] );
				}
				if ( ! empty( $_POST['maptype'] ) ) {
					$this->client->set_map_type( $_POST['maptype'] );
				}
				if ( ! empty( $_POST['language'] ) ) {
					$this->client->set_language( $_POST['language'] );
				}

				switch ( $mode ) {
					case 'place':
						$place_id = sanitize_text_field( $_POST['place_id'] );
						$url      = $this->client->place( $place_id );
						break;

					case 'search':
						$query = sanitize_text_field( $_POST['search_query'] );
						$url   = $this->client->search( $query );
						break;

					case 'view':
						$latitude  = (float) sanitize_text_field( $_POST['latitude'] );
						$longitude = (float) sanitize_text_field( $_POST['longitude'] );
						$url       = $this->client->view( $latitude, $longitude );
						break;

					case 'directions':
						$origin      = sanitize_text_field( $_POST['origin'] );
						$destination = sanitize_text_field( $_POST['destination'] );

						if ( ! empty( $_POST['travel_mode'] ) ) {
							$this->client->set_mode( $_POST['travel_mode'] );
						}
						if ( ! empty( $_POST['avoid_routes'] ) ) {
							$this->client->set_avoid( $_POST['avoid_routes'] );
						}
						if ( ! empty( $_POST['units'] ) ) {
							$this->client->set_units( $_POST['units'] );
						}

						$url = $this->client->directions( $origin, $destination );
						break;

					case 'streetview':
						$latitude  = (float) sanitize_text_field( $_POST['sv_latitude'] );
						$longitude = (float) sanitize_text_field( $_POST['sv_longitude'] );

						if ( isset( $_POST['heading'] ) ) {
							$this->client->set_heading( (float) $_POST['heading'] );
						}
						if ( isset( $_POST['pitch'] ) ) {
							$this->client->set_pitch( (float) $_POST['pitch'] );
						}
						if ( isset( $_POST['fov'] ) ) {
							$this->client->set_fov( (float) $_POST['fov'] );
						}

						$url = $this->client->streetview( $latitude, $longitude );
						break;

					default:
						throw new Exception( __( 'Invalid embed mode', 'arraypress-google-maps-embed' ) );
				}

				if ( is_wp_error( $url ) ) {
					throw new Exception( $url->get_error_message() );
				}

				$width  = ! empty( $_POST['width'] ) ? sanitize_text_field( $_POST['width'] ) : '600';
				$height = ! empty( $_POST['height'] ) ? sanitize_text_field( $_POST['height'] ) : '450';

				$results['embed'] = $this->client->generate_iframe( $url, [
					'width'  => $width,
					'height' => $height
				] );

			} catch ( Exception $e ) {
				add_settings_error(
					'maps_embed_test',
					'embed_error',
					$e->getMessage()
				);
			}
		}

		return $results;
	}

}

new Plugin();