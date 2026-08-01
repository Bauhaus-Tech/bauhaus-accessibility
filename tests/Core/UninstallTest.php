<?php
/**
 * Tests for uninstall.php behavior.
 *
 * Verifies that the uninstall handler cleans up the correct option
 * and stays in sync with Plugin::OPTION_NAME.
 */

namespace Bauhaus_Acessibilidade\Tests\Core;

use Bauhaus_Acessibilidade\Core\Plugin;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class UninstallTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\tearDown();
		\Brain\Monkey\setUp();
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * uninstall.php must call delete_option with the same key as Plugin::OPTION_NAME.
	 */
	public function test_uninstall_deletes_correct_option(): void {
		$deleted_option = null;

		Functions\expect( 'delete_option' )
			->once()
			->andReturnUsing(
				function ( string $option ) use ( &$deleted_option ): bool {
					$deleted_option = $option;
					return true;
				}
			);

		// Simulate WordPress uninstall context.
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WP core constant
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
		}
		// phpcs:enable

		// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		require dirname( __DIR__, 2 ) . '/uninstall.php';
		// phpcs:enable

		$this->assertSame(
			Plugin::OPTION_NAME,
			$deleted_option,
			'uninstall.php must delete the option defined in Plugin::OPTION_NAME'
		);
	}

	/**
	 * Plugin::OPTION_NAME must match the literal used in uninstall.php.
	 *
	 * This is a belt-and-suspenders check: even if the test above mocks
	 * delete_option, the constant itself must hold the expected value.
	 */
	public function test_option_name_constant_matches_uninstall_key(): void {
		$this->assertSame(
			'bauhaus_acessibilidade_settings',
			Plugin::OPTION_NAME,
			'Plugin::OPTION_NAME must be bauhaus_acessibilidade_settings'
		);
	}
}
