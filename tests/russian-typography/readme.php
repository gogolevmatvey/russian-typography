<?php
/**
 * Standalone tests for the Russian Typography WordPress.org readme.
 *
 * @package RussianTypography
 */

declare(strict_types=1);

$russian_typography_root_dir   = dirname( __DIR__, 2 );
$russian_typography_plugin_dir = is_file( $russian_typography_root_dir . '/readme.txt' )
	? $russian_typography_root_dir
	: $russian_typography_root_dir . '/www/wordpress/wp-content/plugins/russian-typography';
$russian_typography_assets_dir = $russian_typography_root_dir . '/wordpress-org-assets/russian-typography';
$russian_typography_readme     = $russian_typography_plugin_dir . '/readme.txt';
$russian_typography_settings   = $russian_typography_plugin_dir . '/includes/settings.php';
$russian_typography_failures   = array();

/**
 * Records a failed assertion.
 *
 * @param string $message Failure message.
 */
function russian_typography_readme_fail( string $message ): void {
	global $russian_typography_failures;

	$russian_typography_failures[] = $message;
}

/**
 * Asserts that a file exists.
 *
 * @param string $path File path.
 */
function russian_typography_assert_file_exists( string $path ): void {
	if ( is_file( $path ) ) {
		return;
	}

	russian_typography_readme_fail( sprintf( 'Missing file: %s', $path ) );
}

/**
 * Asserts that readme text contains a required fragment.
 *
 * @param string $readme   Readme text.
 * @param string $fragment Required fragment.
 */
function russian_typography_assert_readme_contains( string $readme, string $fragment ): void {
	if ( false !== strpos( $readme, $fragment ) ) {
		return;
	}

	russian_typography_readme_fail( sprintf( 'readme.txt must contain: %s', $fragment ) );
}

/**
 * Asserts that readme text does not contain an obsolete fragment.
 *
 * @param string $readme   Readme text.
 * @param string $fragment Obsolete fragment.
 */
function russian_typography_assert_readme_not_contains( string $readme, string $fragment ): void {
	if ( false === strpos( $readme, $fragment ) ) {
		return;
	}

	russian_typography_readme_fail( sprintf( 'readme.txt must not contain obsolete text: %s', $fragment ) );
}

russian_typography_assert_file_exists( $russian_typography_readme );
russian_typography_assert_file_exists( $russian_typography_settings );

$russian_typography_readme_text = is_file( $russian_typography_readme )
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	? (string) file_get_contents( $russian_typography_readme )
	: '';
$russian_typography_settings_text = is_file( $russian_typography_settings )
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	? (string) file_get_contents( $russian_typography_settings )
	: '';

foreach (
	array(
		'=== Russian Typography ===',
		'Contributors: gogolevmatvey',
		'Tags: russian, typography, nonbreaking spaces, text',
		'Requires at least: 7.0',
		'Tested up to: 7.0',
		'Requires PHP: 8.0',
		'Stable tag: 0.1.0',
		'License: GPLv2 or later',
		'License URI: https://www.gnu.org/licenses/gpl-2.0.html',
		'== Description ==',
		'== Screenshots ==',
		'== Installation ==',
		'== Frequently Asked Questions ==',
		'== Changelog ==',
		'Can typography be disabled in headings?',
		'Disable typography in headings',
		'Disable typography in post and card titles',
	) as $russian_typography_required_fragment
) {
	russian_typography_assert_readme_contains( $russian_typography_readme_text, $russian_typography_required_fragment );
}

foreach (
	array(
		'Disable typography in headings',
		'Selected heading levels are skipped completely.',
		'Post and card titles',
		'Disable typography in post and card titles',
		'Applies to the_title(): single post H1, cards, archives, and related content.',
	) as $russian_typography_required_settings_fragment
) {
	russian_typography_assert_readme_contains( $russian_typography_settings_text, $russian_typography_required_settings_fragment );
}

$russian_typography_standart_mode_position = strpos( $russian_typography_settings_text, 'russian-typography-short-word-mode-standart' );
$russian_typography_soft_mode_position     = strpos( $russian_typography_settings_text, 'russian-typography-short-word-mode-soft' );
if (
	false === $russian_typography_standart_mode_position ||
	false === $russian_typography_soft_mode_position ||
	$russian_typography_standart_mode_position > $russian_typography_soft_mode_position
) {
	russian_typography_readme_fail( 'Standart short-word gluing mode must be listed before Soft mode.' );
}

foreach (
	array(
		'Can short-word gluing be disabled in headings?',
		'Do not glue short words in headings',
		'skip short-word gluing inside headings',
	) as $russian_typography_obsolete_fragment
) {
	russian_typography_assert_readme_not_contains( $russian_typography_readme_text, $russian_typography_obsolete_fragment );
}

foreach (
	array(
		'Отключить типографику в заголовках',
		'Если выбран h2',
		'Заголовки записей и карточек',
		'Применяется к the_title()',
	) as $russian_typography_obsolete_settings_fragment
) {
	russian_typography_assert_readme_not_contains( $russian_typography_settings_text, $russian_typography_obsolete_settings_fragment );
}

if ( is_dir( $russian_typography_assets_dir ) ) {
	foreach (
		array(
			'banner-1544x500.png',
			'banner-772x250.png',
			'icon-256x256.png',
			'screenshot-1.png',
			'settings-screenshot.png',
		) as $russian_typography_asset
	) {
		russian_typography_assert_file_exists( $russian_typography_assets_dir . '/' . $russian_typography_asset );
	}
}

if ( array() !== $russian_typography_failures ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
	fwrite( STDERR, implode( "\n", $russian_typography_failures ) . "\n" );
	exit( 1 );
}

echo "Russian Typography readme tests passed.\n";
