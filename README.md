# Google Maps Embed Tester Plugin for WordPress

A WordPress plugin that provides a user interface for testing and demonstrating the Google Maps Embed API integration. This plugin allows you to easily generate and customize embedded maps using various modes and display options through the WordPress admin interface.

## Features

- Visual interface for map embedding
- Multiple embedding modes:
    - Place Mode (embed specific locations)
    - Search Mode (display search results)
    - View Mode (custom map views)
    - Directions Mode (route planning)
    - Street View Mode (360° street-level imagery)
- Comprehensive customization options including:
    - Map type selection (roadmap/satellite)
    - Zoom level control
    - Multiple language support
    - Custom dimensions
    - Travel mode options
    - Route preferences
    - Street View camera controls

## Requirements

- PHP 7.4 or later
- WordPress 6.7.1 or later
- Google Maps API key with Maps Embed API enabled

## Installation

1. Download or clone this repository
2. Place in your WordPress plugins directory
3. Run `composer install` in the plugin directory
4. Activate the plugin in WordPress
5. Add your Google Maps API key in Google > Maps Embed

## Usage

1. Navigate to Google > Maps Embed in your WordPress admin panel
2. Enter your Google Maps API key in the settings section
3. Choose an embed mode:
    - Place: Embed specific locations using Place IDs
    - Search: Display map results for a search query
    - View: Show custom map views using coordinates
    - Directions: Display routes between locations
    - Street View: Show street-level imagery
4. Configure mode-specific options
5. Set common display preferences
6. Generate and preview the embed
7. Copy the generated embed code

## Features in Detail

### Place Mode
- Embed specific locations using Google Place IDs
- Display business locations, landmarks, and points of interest
- Automatic info window support

### Search Mode
- Embed maps showing search results
- Support for business and location queries
- Dynamic result display

### View Mode
- Custom map views using specific coordinates
- Adjustable zoom levels (0-21)
- Map type selection (roadmap/satellite)

### Directions Mode
- Route visualization between locations
- Multiple travel modes:
    - Driving
    - Walking
    - Bicycling
    - Transit
- Route preferences:
    - Avoid tolls
    - Avoid highways
    - Avoid ferries
- Unit selection (metric/imperial)

### Street View Mode
- 360° street-level imagery
- Camera controls:
    - Heading (0-360°)
    - Pitch (-90° to 90°)
    - Field of view (10-100°)
- Custom starting position

### Common Features
- Multi-language support
- Custom iframe dimensions
- Responsive design options
- Preview functionality
- One-click code copying

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL v2 or later License.

## Support

- Documentation: https://github.com/arraypress/google-maps-embed-plugin
- Issue Tracker: https://github.com/arraypress/google-maps-embed-plugin/issues