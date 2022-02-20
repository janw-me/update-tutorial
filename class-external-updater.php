<?php


if ( class_exists( '\External_Updater' ) ) {
	return; // class already exists, don't include it again.
}

/**
 * Class External_Updater
 */
class External_Updater {

	/**
	 * The plugin slug. This should be used for both the plugin folder name and the main php file.
	 * Example update-tutorial/update-tutorial.php, the slug is "update-tutorial"
	 *
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The domain name where to check for updates.
	 *
	 * @var string
	 */
	protected $update_hostname;

	/**
	 * The full url to external the readme, this readme used to check for the actual updates.
	 *
	 * @var string
	 */
	protected $readme_url;

	/**
	 * The general plugin data, only set during updates.
	 *
	 * @var array|null
	 */
	protected $plugin_data = null;

	/**
	 * Start the plugin updater.
	 *
	 * @param string $plugin_slug The plugin slug. This should be used for both the plugin folder name and the main php file.
	 *                            Example update-tutorial/update-tutorial.php, the slug is "update-tutorial".
	 * @param string $update_hostname The domain name where to check for updates. Should be the same as the "Update URI" plugin header.
	 */
	public function __construct( string $plugin_slug, string $update_hostname ) {
		$this->plugin_slug     = $plugin_slug;
		$this->update_hostname = $update_hostname;

		$this->readme_url = "https://{$update_hostname}/{$plugin_slug}/readme.txt";

		// Hook the updates to the correct url.
		add_filter( "update_plugins_{$update_hostname}", array( $this, 'plugin_updates' ), 10, 4 );
	}

	/**
	 * Get update data for the plugin. This only preps the data, updating itself is done later.
	 *
	 * @param false|array $update The plugin update data with the latest details. Default false.
	 * @param array       $plugin_data Plugin headers.
	 * @param string      $plugin_file Plugin filename.
	 * @param array       $locales Installed locales to look translations for.
	 *
	 * @return false|array
	 */
	public function plugin_updates( $update, array $plugin_data, string $plugin_file, array $locales ) {
		$this->plugin_data = $plugin_data;

		if ( "{$this->plugin_slug}/{$this->plugin_slug}.php" !== $plugin_file ) {
			return $update; // This plugin uses the same hostname, but it's not this plugin.
		}

		$readme_file = $this->get_readme();
		// Invalid call?
		if ( is_wp_error( $readme_file ) ) {
			$this->wp_die( $readme_file );
		}

		// Get data out of the readme file.
		$readme_data = $this->get_readme_data( $readme_file );
		if ( is_wp_error( $readme_data ) ) {
			$this->wp_die( $readme_data );
		}

		// Is the version found in the readme higher than the current version?
		if ( version_compare( $this->plugin_data['Version'], $readme_data['version'], '>=' ) ) {
			return $update; // remote version is the same, or even smaller.
		}

		// Append extra data for updating.
		$readme_data['id']   = $this->readme_url;
		$readme_data['slug'] = $this->plugin_slug;
		// The zip of the new version.
		$readme_data['package'] = "https://{$this->update_hostname}/{$this->plugin_slug}/{$this->plugin_slug}.{$readme_data['version']}.zip";

		return $readme_data;
	}

	/**
	 * Get the readme file.
	 *
	 * @return string|\WP_Error
	 */
	protected function get_readme() {
		// Get the readme file.
		$r = wp_remote_get( $this->readme_url );

		// Was the call successfully?
		if ( is_wp_error( $r ) ) {
			return $r;
		}

		$response_code = wp_remote_retrieve_response_code( $r );
		// Only accept 200 range.
		if ( $response_code < 200 | $response_code >= 300 ) {
			return new \WP_Error( $response_code, 'Invalid HTTP status code: ' . $response_code );
		}

		$body = wp_remote_retrieve_body( $r );

		if ( empty( $body ) ) {
			return new \WP_Error( $response_code, 'Empty readme.txt' );
		}

		return $body;
	}

	/**
	 * Parse the readme.txt file and get the data we need.
	 *
	 * @param string $raw_data The raw content of the readme.txt.
	 *
	 * @return array|\WP_Error error if no version number is found.
	 */
	protected function get_readme_data( string $raw_data ) {
		$data = array();
		preg_match( '@Stable\s?tag:\s?(.*)\n@', $raw_data, $matches );
		if ( ! empty( $matches[1] ) ) {
			$data['version'] = trim( $matches[1] );
		} else {
			return new \WP_Error( 'missing-version-nr', __( "Can't find 'Stable tag' version number." ) );
		}
		preg_match( '@Tested\s?up\s?to:\s?(.*)\n@', $raw_data, $matches );
		if ( ! empty( $matches[1] ) ) {
			$data['tested'] = trim( $matches[1] );
		}
		preg_match( '@Requires\s?PHP:\s?(.*)\n@', $raw_data, $matches );
		if ( ! empty( $matches[1] ) ) {
			$data['requires_php'] = trim( $matches[1] );
		}

		return $data;
	}

	/**
	 * Display a formatted error when needed.
	 *
	 * @param \WP_Error $error The error.
	 */
	protected function wp_die( \WP_Error $error ) {
		// translators: The plugin name.
		$title = sprintf( __( 'An error occurred while checking for updates for %s' ), "<i>{$this->plugin_data['Name']}</i>" );

		// translators: The link tags round the readme.txt.
		$readme = sprintf( __( 'The %1$sreadme.txt%2$s that errored,' ), "<a href='{$this->readme_url}'>", '</a>' );

		$text  = "<h3>{$title}</h3>";
		$text .= "<p><code>{$error->get_error_message()}</code></p>";
		$text .= '<p>' . __( 'If this error persists, contact support.' ) . '<br/>' . $readme . '</p>';

		wp_die( $text );
	}
}
