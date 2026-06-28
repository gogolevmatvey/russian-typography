<?php
/**
 * Settings and admin UI for Russian Typography.
 *
 * @package RussianTypography
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns option names used before the public prefix was finalized.
 *
 * @return array<int, string>
 */
function russian_typography_get_legacy_option_names(): array {
	return array(
		'wp_russian_typography_scope',
		'wp_russian_typography_skip_heading_short_words',
		'wp_russian_typography_short_word_mode',
		'wp_russian_typography_soft_max_next_word_length',
		'wp_russian_typography_full_max_next_word_length',
	);
}

/**
 * Deletes legacy options without copying their values to the current settings.
 */
function russian_typography_delete_legacy_options(): void {
	foreach ( russian_typography_get_legacy_option_names() as $option ) {
		delete_option( $option );
	}
}

/**
 * Sanitizes the typography scope setting.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_scope( mixed $value ): string {
	$scope = is_string( $value ) ? sanitize_key( $value ) : '';

	if ( in_array( $scope, array( RUSSIAN_TYPOGRAPHY_SCOPE_ALL, RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE ), true ) ) {
		return $scope;
	}

	return RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE;
}

/**
 * Returns the active typography scope.
 */
function russian_typography_get_scope(): string {
	return russian_typography_sanitize_scope(
		get_option( RUSSIAN_TYPOGRAPHY_SCOPE_OPTION, RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE )
	);
}

/**
 * Sanitizes the short-word glue mode setting.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_short_word_mode( mixed $value ): string {
	$mode = is_string( $value ) ? sanitize_key( $value ) : '';

	if (
		in_array(
			$mode,
			array(
				RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT,
				RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL,
				RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART,
				RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF,
			),
			true
		)
	) {
		return $mode;
	}

	return RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT;
}

/**
 * Returns the active short-word glue mode.
 */
function russian_typography_get_short_word_mode(): string {
	return russian_typography_sanitize_short_word_mode(
		get_option( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION, RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT )
	);
}

/**
 * Sanitizes next-word length thresholds for short-word glue modes.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_max_next_word_length( mixed $value ): int {
	$length = absint( $value );

	if ( $length < 4 ) {
		return 4;
	}

	if ( $length > 20 ) {
		return 20;
	}

	return $length;
}

/**
 * Sanitizes the next-word length threshold for soft short-word glue mode.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_soft_max_next_word_length( mixed $value ): int {
	return russian_typography_sanitize_max_next_word_length( $value );
}

/**
 * Sanitizes the next-word length threshold for full short-word glue mode.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_full_max_next_word_length( mixed $value ): int {
	return russian_typography_sanitize_max_next_word_length( $value );
}

/**
 * Returns the maximum next-word length used in soft short-word glue mode.
 */
function russian_typography_get_soft_max_next_word_length(): int {
	return russian_typography_sanitize_soft_max_next_word_length(
		get_option(
			RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_OPTION,
			RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT
		)
	);
}

/**
 * Returns the maximum next-word length used in full short-word glue mode.
 */
function russian_typography_get_full_max_next_word_length(): int {
	return russian_typography_sanitize_full_max_next_word_length(
		get_option(
			RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_OPTION,
			RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_DEFAULT
		)
	);
}

/**
 * Sanitizes checkbox-like settings.
 *
 * @param mixed $value Raw option value.
 */
function russian_typography_sanitize_checkbox( mixed $value ): string {
	return '1' === (string) $value ? '1' : '0';
}

/**
 * Returns heading tags supported by the per-level typography setting.
 *
 * @return array<int, string>
 */
function russian_typography_get_heading_tags(): array {
	return array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
}

/**
 * Returns default heading levels where typography is disabled.
 *
 * @return array<int, string>
 */
function russian_typography_get_default_disabled_headings(): array {
	return array( 'h1', 'h2', 'h3' );
}

/**
 * Sanitizes the disabled heading levels setting.
 *
 * @param mixed $value Raw option value.
 * @return array<int, string>
 */
function russian_typography_sanitize_disabled_headings( mixed $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$allowed  = array_fill_keys( russian_typography_get_heading_tags(), true );
	$disabled = array();

	foreach ( $value as $tag ) {
		$tag = sanitize_key( (string) $tag );

		if ( isset( $allowed[ $tag ] ) ) {
			$disabled[ $tag ] = true;
		}
	}

	return array_keys( $disabled );
}

/**
 * Returns heading levels where typography must not touch text nodes.
 *
 * @return array<int, string>
 */
function russian_typography_get_disabled_headings(): array {
	$value = get_option( RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION, null );

	if ( null !== $value && false !== $value ) {
		return russian_typography_sanitize_disabled_headings( $value );
	}

	$legacy_value = get_option( RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION, null );

	if ( null !== $legacy_value && false !== $legacy_value ) {
		return '1' === russian_typography_sanitize_checkbox( $legacy_value )
			? russian_typography_get_default_disabled_headings()
			: array();
	}

	return russian_typography_get_default_disabled_headings();
}

/**
 * Returns true when typography should be skipped inside the given heading tag.
 *
 * @param string $tag Heading tag name.
 */
function russian_typography_is_heading_typography_disabled( string $tag ): bool {
	return in_array( strtolower( $tag ), russian_typography_get_disabled_headings(), true );
}

/**
 * Returns true when typography should be skipped for the_title() output.
 */
function russian_typography_disable_title_typography(): bool {
	$value = get_option( RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION, null );

	if ( null !== $value && false !== $value ) {
		return '1' === russian_typography_sanitize_checkbox( $value );
	}

	$legacy_value = get_option( RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION, null );

	if ( null !== $legacy_value && false !== $legacy_value ) {
		return '1' === russian_typography_sanitize_checkbox( $legacy_value );
	}

	return true;
}

/**
 * Returns true when short service words should not be glued inside headings.
 */
function russian_typography_skip_short_words_in_headings(): bool {
	return '1' === russian_typography_sanitize_checkbox(
		get_option( RUSSIAN_TYPOGRAPHY_SKIP_HEADING_SHORT_WORDS_OPTION, '1' )
	);
}

/**
 * Registers plugin settings.
 */
function russian_typography_register_settings(): void {
	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_SCOPE_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'russian_typography_sanitize_scope',
			'default'           => RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE,
		)
	);

	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'russian_typography_sanitize_disabled_headings',
			'default'           => russian_typography_get_default_disabled_headings(),
		)
	);

	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'russian_typography_sanitize_checkbox',
			'default'           => '1',
		)
	);

	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'russian_typography_sanitize_short_word_mode',
			'default'           => RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT,
		)
	);

	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_OPTION,
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'russian_typography_sanitize_soft_max_next_word_length',
			'default'           => RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_DEFAULT,
		)
	);

	register_setting(
		'russian_typography',
		RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_OPTION,
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'russian_typography_sanitize_full_max_next_word_length',
			'default'           => RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_DEFAULT,
		)
	);
}

/**
 * Adds the settings page to the WordPress admin.
 */
function russian_typography_add_settings_page(): void {
	add_options_page(
		__( 'Russian Typography', 'russian-typography' ),
		__( 'Russian Typography', 'russian-typography' ),
		'manage_options',
		'russian-typography',
		'russian_typography_render_settings_page'
	);
}

/**
 * Renders the settings page.
 */
function russian_typography_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$scope                     = russian_typography_get_scope();
	$disabled_headings         = russian_typography_get_disabled_headings();
	$disable_title_typography  = russian_typography_disable_title_typography();
	$short_word_mode           = russian_typography_get_short_word_mode();
	$soft_max_next_word_length = russian_typography_get_soft_max_next_word_length();
	$full_max_next_word_length = russian_typography_get_full_max_next_word_length();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Russian Typography', 'russian-typography' ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'russian_typography' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php echo esc_html__( 'Processing scope', 'russian-typography' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<?php echo esc_html__( 'Typography processing scope', 'russian-typography' ); ?>
							</legend>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-scope-single"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SCOPE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE ); ?>"
										aria-describedby="russian-typography-scope-single-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SCOPE_SINGLE, $scope ); ?>
									>
									<?php echo esc_html__( 'Single posts and pages only', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-scope-single-description" class="description">
								<?php echo esc_html__( 'Processes main content, title, and comments on single posts and pages. Cards, archives, and the main feed are not changed.', 'russian-typography' ); ?>
							</p>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-scope-all"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SCOPE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SCOPE_ALL ); ?>"
										aria-describedby="russian-typography-scope-all-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SCOPE_ALL, $scope ); ?>
									>
									<?php echo esc_html__( 'Entire frontend', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-scope-all-description" class="description">
								<?php echo esc_html__( 'Processes posts, pages, cards, archives, titles, excerpts, and comments across the frontend.', 'russian-typography' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Disable typography in headings', 'russian-typography' ); ?></th>
					<td>
						<fieldset aria-describedby="russian-typography-disabled-headings-description">
							<legend class="screen-reader-text">
								<?php echo esc_html__( 'Disable typography in headings', 'russian-typography' ); ?>
							</legend>

							<input type="hidden" name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION ); ?>[]" value="">

							<?php foreach ( russian_typography_get_heading_tags() as $heading_tag ) : ?>
								<p>
									<label for="russian-typography-disabled-heading-<?php echo esc_attr( $heading_tag ); ?>">
										<input
											type="checkbox"
											id="russian-typography-disabled-heading-<?php echo esc_attr( $heading_tag ); ?>"
											name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_DISABLED_HEADINGS_OPTION ); ?>[]"
											value="<?php echo esc_attr( $heading_tag ); ?>"
											<?php checked( in_array( $heading_tag, $disabled_headings, true ) ); ?>
										>
										<?php echo esc_html( $heading_tag ); ?>
									</label>
								</p>
							<?php endforeach; ?>

							<p id="russian-typography-disabled-headings-description" class="description">
								<?php echo wp_kses_post( __( 'Selected heading levels are skipped completely. For example, if h2 is selected, text inside <code>&lt;h2&gt;...&lt;/h2&gt;</code> is not processed.', 'russian-typography' ) ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Post and card titles', 'russian-typography' ); ?></th>
					<td>
						<input type="hidden" name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION ); ?>" value="0">
						<label for="russian-typography-disable-title-typography">
							<input
								type="checkbox"
								id="russian-typography-disable-title-typography"
								name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_DISABLE_TITLE_TYPOGRAPHY_OPTION ); ?>"
								value="1"
								aria-describedby="russian-typography-disable-title-typography-description"
								<?php checked( $disable_title_typography ); ?>
							>
							<?php echo esc_html__( 'Disable typography in post and card titles', 'russian-typography' ); ?>
						</label>
						<p id="russian-typography-disable-title-typography-description" class="description">
							<?php echo esc_html__( 'Applies to the_title(): single post H1, cards, archives, and related content.', 'russian-typography' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Short-word gluing', 'russian-typography' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<?php echo esc_html__( 'Short-word gluing mode', 'russian-typography' ); ?>
							</legend>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-short-word-mode-soft"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT ); ?>"
										aria-describedby="russian-typography-short-word-mode-soft-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_SOFT, $short_word_mode ); ?>
									>
									<?php echo esc_html__( 'Soft', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-short-word-mode-soft-description" class="description">
								<?php echo esc_html__( 'Glues only one-letter Russian service words. On narrow screens, this mode leaves more natural line-break opportunities.', 'russian-typography' ); ?>
							</p>

							<p>
								<label for="russian-typography-soft-max-next-word-length">
									<?php echo esc_html__( 'Maximum next-word length in soft mode', 'russian-typography' ); ?>
								</label>
								<input
									type="number"
									id="russian-typography-soft-max-next-word-length"
									name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SOFT_MAX_NEXT_WORD_LENGTH_OPTION ); ?>"
									value="<?php echo esc_attr( (string) $soft_max_next_word_length ); ?>"
									min="4"
									max="20"
									step="1"
									class="small-text"
									aria-describedby="russian-typography-soft-max-next-word-length-description"
								>
							</p>
							<p id="russian-typography-soft-max-next-word-length-description" class="description">
								<?php echo esc_html__( 'If the next word is longer than this value, the space stays regular. This helps avoid ragged lines around 320 px width.', 'russian-typography' ); ?>
							</p>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-short-word-mode-full"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL ); ?>"
										aria-describedby="russian-typography-short-word-mode-full-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_FULL, $short_word_mode ); ?>
									>
									<?php echo esc_html__( 'Full', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-short-word-mode-full-description" class="description">
								<?php echo esc_html__( 'Glues short semantic words such as prepositions, conjunctions, particles, and short pronouns to the related word. Long next words stay breakable to keep narrow screens readable.', 'russian-typography' ); ?>
							</p>

							<p>
								<label for="russian-typography-full-max-next-word-length">
									<?php echo esc_html__( 'Maximum next-word length in full mode', 'russian-typography' ); ?>
								</label>
								<input
									type="number"
									id="russian-typography-full-max-next-word-length"
									name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_FULL_MAX_NEXT_WORD_LENGTH_OPTION ); ?>"
									value="<?php echo esc_attr( (string) $full_max_next_word_length ); ?>"
									min="4"
									max="20"
									step="1"
									class="small-text"
									aria-describedby="russian-typography-full-max-next-word-length-description"
								>
							</p>
							<p id="russian-typography-full-max-next-word-length-description" class="description">
								<?php echo esc_html__( 'If the next word is longer than this value, the space stays regular. This keeps semantic gluing from creating too-long non-breaking groups.', 'russian-typography' ); ?>
							</p>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-short-word-mode-standart"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART ); ?>"
										aria-describedby="russian-typography-short-word-mode-standart-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_STANDART, $short_word_mode ); ?>
									>
									<?php echo esc_html__( 'Standart', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-short-word-mode-standart-description" class="description">
								<?php echo esc_html__( 'Spaces closer to Art. Lebedev Typograf. This mode imitates spacing only and does not change quotes, dashes, or punctuation.', 'russian-typography' ); ?>
							</p>

							<p>
								<label>
									<input
										type="radio"
										id="russian-typography-short-word-mode-off"
										name="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OPTION ); ?>"
										value="<?php echo esc_attr( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF ); ?>"
										aria-describedby="russian-typography-short-word-mode-off-description"
										<?php checked( RUSSIAN_TYPOGRAPHY_SHORT_WORD_MODE_OFF, $short_word_mode ); ?>
									>
									<?php echo esc_html__( 'Off', 'russian-typography' ); ?>
								</label>
							</p>
							<p id="russian-typography-short-word-mode-off-description" class="description">
								<?php echo esc_html__( 'Does not glue short words. Historical abbreviations and numbers with units still remain glued.', 'russian-typography' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Save Changes', 'russian-typography' ) ); ?>
		</form>
	</div>
	<?php
}
