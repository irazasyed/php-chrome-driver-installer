<?php

namespace Irazasyed\PHP;

use RuntimeException;
use ZipArchive;

class ChromeDriverInstaller {
	/**
	 * URL to the latest stable release version.
	 *
	 * @var string
	 */
	protected const LATEST_VERSION_URL = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE';

	/**
	 * URL to the latest release version for a major Chrome version.
	 *
	 * @var string
	 */
	protected const VERSION_URL = 'https://chromedriver.storage.googleapis.com/LATEST_RELEASE_%d';

	/**
	 * URL to the ChromeDriver download.
	 *
	 * @var string
	 */
	protected const DOWNLOAD_URL = 'https://chromedriver.storage.googleapis.com/%s/chromedriver_%s.zip';

	/**
	 * Download slugs for the available operating systems.
	 *
	 * @var array
	 */
	protected const SLUGS = [
		'linux'     => 'linux64',
		'mac'       => 'mac64',
		'mac-intel' => 'mac64',
		'mac-arm'   => 'mac_arm64',
		'win'       => 'win32',
	];

	public function __construct(
		protected string $directory = __DIR__ . '/bin/',
		private bool $sslNoVerify = false,
		private ?string $proxy = null
	) {
	}

	// create a static method to get the instance of the class
	public static function new(): static {
		return new static();
	}

	public function setDirectory(string $directory): static {
		$this->directory = $directory;

		return $this;
	}

	public function getDirectory(): string {
		return $this->directory;
	}

	/**
	 * Set Proxy
	 *
	 * @param string|null $proxy
	 *
	 * @return $this
	 */
	public function setProxy( ?string $proxy ): static {
		$this->proxy = $proxy;

		return $this;
	}

	/**
	 * Set SSL No Verify Option.
	 *
	 * @param bool $sslNoVerify
	 *
	 * @return $this
	 */
	public function setSSLNoVerify( bool $sslNoVerify ): static {
		$this->sslNoVerify = $sslNoVerify;

		return $this;
	}

	public function getChromeDriverBinary(): string {
		$os = OperatingSystem::id();

		$binary = $this->directory . 'chromedriver-' . $os;

		if ( ! file_exists( $binary ) ) {
			$this->install();
		}

		return $binary;
	}

	public function install( ?string $version = null, bool $all = false ): void {
		$version = $this->version( $version );

		$currentOS = OperatingSystem::id();

		if ( ! is_dir( $this->directory ) &&
		     ! mkdir( $concurrentDirectory = $this->directory, 0755,
			     true ) &&
		     ! is_dir( $concurrentDirectory ) ) {
			throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $concurrentDirectory ) );
		}

		foreach ( self::SLUGS as $os => $slug ) {
			if ( $all || ( $os === $currentOS ) ) {
				$archive = $this->download( $version, $slug );

				$binary = $this->extract( $archive );

				$this->rename( $binary, $os );
			}
		}

		$message = 'ChromeDriver %s successfully installed for version %s.';

		echo sprintf( $message, $all ? 'binaries' : 'binary', $version ) . PHP_EOL;
	}

	/**
	 * Get the desired ChromeDriver version.
	 *
	 * @param string|null $version
	 *
	 * @return string
	 */
	protected function version( ?string $version = null ): string {
		if ( ! $version ) {
			return $this->latestVersion();
		}

		return trim( $this->getUrl(
			sprintf( self::VERSION_URL, $version )
		) );
	}

	/**
	 * Get the latest stable ChromeDriver version.
	 *
	 * @return string
	 */
	protected function latestVersion(): string {
		$streamOptions = [];

		if ( $this->sslNoVerify ) {
			$streamOptions = [
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			];
		}

		if ( $this->proxy ) {
			$streamOptions['http'] = [ 'proxy' => $this->proxy, 'request_fulluri' => true ];
		}

		return trim( file_get_contents( self::LATEST_VERSION_URL, false, stream_context_create( $streamOptions ) ) );
	}

	/**
	 * Download the ChromeDriver archive.
	 *
	 * @param string $version
	 * @param string $slug
	 *
	 * @return string
	 */
	protected function download( string $version, string $slug ): string {
		$url = sprintf( self::DOWNLOAD_URL, $version, $slug );

		file_put_contents(
			$archive = $this->directory . 'chromedriver.zip',
			$this->getUrl( $url )
		);

		return $archive;
	}

	/**
	 * Extract the ChromeDriver binary from the archive and delete the archive.
	 *
	 * @param string $archive
	 *
	 * @return string
	 */
	protected function extract( string $archive ): string {
		$zip = new ZipArchive;

		if ( $zip->open( $archive ) === true ) {
			$zip->extractTo( $this->directory );

			$binary = $zip->getNameIndex( 0 );

			$zip->close();

			unlink( $archive );

			return $binary;
		}

		throw new RuntimeException( 'Failed to extract archive.' );
	}

	/**
	 * Rename the ChromeDriver binary and make it executable.
	 *
	 * @param string $binary
	 * @param string $os
	 *
	 * @return void
	 */
	protected function rename( string $binary, string $os ): void {
		$newName = str_replace( 'chromedriver', 'chromedriver-' . $os, $binary );

		rename( $this->directory . $binary, $this->directory . $newName );

		chmod( $this->directory . $newName, 0755 );
	}

	/**
	 * Get the contents of a URL using the 'proxy' and 'ssl-no-verify' options.
	 *
	 * @param string $url
	 *
	 * @return string|bool
	 */
	protected function getUrl( string $url ): string|bool {
		$contextOptions = [];

		if ( $this->proxy ) {
			$contextOptions['http'] = [ 'proxy' => $this->proxy, 'request_fulluri' => true ];
		}

		if ( $this->sslNoVerify ) {
			$contextOptions['ssl'] = [ 'verify_peer' => false ];
		}

		$streamContext = stream_context_create( $contextOptions );

		return file_get_contents( $url, false, $streamContext );
	}
}
