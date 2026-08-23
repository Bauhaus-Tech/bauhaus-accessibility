<?php
/**
 * Tests for WordPress translation package boundaries.
 *
 * @package Bauhaus_Accessibility
 */

namespace Bauhaus_Accessibility\Tests\Core;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * Ensures language packs, rather than plugin files, deliver WordPress translations.
 */
class TranslationPackagingTest extends TestCase {

	/**
	 * WordPress translation artifacts stay out of the plugin package.
	 *
	 * The Brazilian Portuguese source remains in the repository documentation so
	 * it can be imported through the official translation platform.
	 *
	 * @return void
	 */
	public function test_plugin_does_not_bundle_translations_and_keeps_pt_br_source_in_docs(): void {
		$plugin_root        = dirname( __DIR__, 2 );
		$translation_source = $plugin_root . '/docs/translations/bauhaus-accessibility-pt_BR.po';

		$this->assertDirectoryDoesNotExist( $plugin_root . '/languages' );
		$this->assertDirectoryDoesNotExist( $plugin_root . '/assets/locales' );
		$this->assertFileExists( $translation_source );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a repository-local PO file; no HTTP request is appropriate.
		$translation_contents = file_get_contents( $translation_source );
		$this->assertNotFalse( $translation_contents );
		$this->assertStringContainsString( "\"Language: pt_BR\\n\"", $translation_contents );
		$this->assertStringContainsString( '"PO-Revision-Date: ', $translation_contents );
		$this->assertStringContainsString( '"Last-Translator: ', $translation_contents );
		$this->assertStringContainsString( "\"Language-Team: Portuguese (Brazil)\\n\"", $translation_contents );
		$this->assertStringContainsString(
			"msgid \"Accessibility\"\nmsgstr \"Acessibilidade\"",
			$translation_contents
		);
		$this->assertSame(
			array( 'docs/translations/bauhaus-accessibility-pt_BR.po' ),
			$this->translation_artifact_paths( $plugin_root )
		);
	}

	/**
	 * The installable archive omits documentation and translation artifacts.
	 *
	 * @return void
	 */
	public function test_build_zip_excludes_translation_sources_and_non_runtime_files(): void {
		$plugin_root    = dirname( __DIR__, 2 );
		$test_directory = sys_get_temp_dir() . '/bauhaus-package-' . uniqid( '', true );
		$build_path     = $test_directory . '/build';
		$zip_path       = $test_directory . '/bauhaus-accessibility.zip';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- The integration test creates an isolated temporary directory.
		$this->assertTrue( mkdir( $test_directory, 0700 ) );

		try {
			$command = sprintf(
				'BAUHAUS_BUILD_DIR=%1$s BAUHAUS_ZIP_PATH=%2$s bash %3$s',
				escapeshellarg( $build_path ),
				escapeshellarg( $zip_path ),
				escapeshellarg( $plugin_root . '/bin/build-zip.sh' )
			);

			$command_output = array();
			$exit_code      = 0;
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec -- The integration test runs the project-local build script.
			exec( $command, $command_output, $exit_code );

			$this->assertSame( 0, $exit_code, implode( "\n", $command_output ) );
			$this->assertFileExists( $zip_path );

			$archive = new ZipArchive();
			$this->assertTrue( true === $archive->open( $zip_path ) );

			$entries = array();
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- ZipArchive exposes this property with a fixed name.
			for ( $index = 0; $index < $archive->numFiles; $index++ ) {
				$entries[] = $archive->getNameIndex( $index );
			}
			$archive->close();

				$this->assertFalse( $this->archive_contains_path( $entries, 'bauhaus-accessibility/languages/' ) );
				$this->assertFalse( $this->archive_contains_path( $entries, 'bauhaus-accessibility/assets/locales/' ) );
			$this->assertFalse( $this->archive_contains_path( $entries, 'bauhaus-accessibility/docs/translations/' ) );
			$this->assertNotContains( 'bauhaus-accessibility/translations.md', $entries );
			$this->assertNotContains( 'bauhaus-accessibility/MANUAL_TESTS.md', $entries );
			$this->assertFalse( $this->archive_contains_path( $entries, 'bauhaus-accessibility/team/' ) );
			$this->assertFalse( $this->archive_contains_hidden_root_entry( $entries ) );
			$this->assertSame( array(), $this->archive_translation_artifact_paths( $entries ) );
		} finally {
			$this->remove_directory( $test_directory );
		}
	}

	/**
	 * Lists translation artifacts committed to the plugin repository.
	 *
	 * @param string $plugin_root Absolute repository path.
	 * @return array<int, string> Paths relative to the repository root.
	 */
	private function translation_artifact_paths( string $plugin_root ): array {
		$artifacts = array();
		$entries   = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $plugin_root, FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $entries as $entry ) {
			if ( ! $entry->isFile() ) {
				continue;
			}

			$relative_path = ltrim( substr( $entry->getPathname(), strlen( $plugin_root ) ), DIRECTORY_SEPARATOR );
			if ( $this->is_ignored_repository_path( $relative_path ) || ! $this->is_translation_artifact( $relative_path ) ) {
				continue;
			}

			$artifacts[] = str_replace( DIRECTORY_SEPARATOR, '/', $relative_path );
		}

		sort( $artifacts );

		return $artifacts;
	}

	/**
	 * Lists translation artifacts included in an installable archive.
	 *
	 * @param array<int, string|false> $entries ZIP entry names.
	 * @return array<int, string>
	 */
	private function archive_translation_artifact_paths( array $entries ): array {
		$artifacts = array();

		foreach ( $entries as $entry ) {
			if ( is_string( $entry ) && $this->is_translation_artifact( $entry ) ) {
				$artifacts[] = $entry;
			}
		}

		sort( $artifacts );

		return $artifacts;
	}

	/**
	 * Identifies repository paths owned by development tooling.
	 *
	 * @param string $relative_path Repository-relative path.
	 * @return bool
	 */
	private function is_ignored_repository_path( string $relative_path ): bool {
		return str_starts_with( $relative_path, '.git/' ) || str_starts_with( $relative_path, 'vendor/' );
	}

	/**
	 * Checks whether a path names a WordPress translation artifact.
	 *
	 * @param string $path Repository- or archive-relative path.
	 * @return bool
	 */
	private function is_translation_artifact( string $path ): bool {
		return 1 === preg_match( '/(?:\\.(?:po|pot|mo)|\\.l10n\\.php)$/i', $path );
	}

	/**
	 * Checks whether a ZIP entry is at or below a path.
	 *
	 * @param array<int, string|false> $entries ZIP entry names.
	 * @param string                   $path Path to look for.
	 * @return bool
	 */
	private function archive_contains_path( array $entries, string $path ): bool {
		foreach ( $entries as $entry ) {
			if ( is_string( $entry ) && str_starts_with( $entry, $path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether the archive contains a hidden file or directory at its root.
	 *
	 * @param array<int, string|false> $entries ZIP entry names.
	 * @return bool
	 */
	private function archive_contains_hidden_root_entry( array $entries ): bool {
		foreach ( $entries as $entry ) {
			if ( is_string( $entry ) && preg_match( '#^bauhaus-accessibility/\\.#', $entry ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes a test-only temporary directory after an archive assertion.
	 *
	 * @param string $directory Temporary directory path.
	 * @return void
	 */
	private function remove_directory( string $directory ): void {
		if ( ! is_dir( $directory ) ) {
			return;
		}

		$entries = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $entries as $entry ) {
			if ( $entry->isDir() ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Removes only the test-created temporary directory.
				rmdir( $entry->getPathname() );
			} else {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Removes only a test-created temporary file.
				unlink( $entry->getPathname() );
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Removes only the test-created temporary directory.
		rmdir( $directory );
	}
}
