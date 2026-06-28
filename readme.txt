=== Russian Typography ===
Contributors: gogolevmatvey
Tags: russian, typography, nonbreaking spaces, text
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Russian typography spacing rules without changing saved post content.

== Description ==

Russian Typography applies focused Russian typography rules to rendered HTML.

The plugin can:

* glue short Russian semantic words to the related word with a non-breaking space;
* glue historical abbreviations such as `n. e.` and `do n. e.` in Russian text;
* glue numbers with common Russian units such as `5 km`, `480 year`, and `24 hours`;
* limit processing to single posts and pages;
* disable typography for selected `h1`-`h6` heading levels and for post/card titles that pass through `the_title()`;
* keep long next words breakable in full mode with a separate length threshold;
* imitate Art. Lebedev Typograf spacing locally in the `standart` mode.

The plugin does not modify saved post content in the database.
The `standart` mode imitates spaces only; it does not call the external Typograf service and does not change quotes, dashes, punctuation, or saved content.

Spacing modes:

* `soft`: conservative gluing for one-letter service words with a next-word length limit;
* `full`: semantic gluing for short prepositions, conjunctions, particles, and pronouns with a separate next-word length limit;
* `standart`: local spacing rules calibrated closer to Art. Lebedev Typograf; no external service calls and no quote, dash, punctuation, or database changes;
* `off`: disables short-word gluing while keeping abbreviation and number/unit spacing rules.

The `standart` mode also keeps common historical dates, centuries, units, percentages, digit groups, initials, and dash spacing closer to the saved Typograf fixtures used during development.

== Screenshots ==

1. Plugin settings page in the WordPress admin.

== Installation ==

1. Upload the `russian-typography` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen in WordPress.
3. Configure the modes in Settings -> Russian Typography.

== Frequently Asked Questions ==

= Does the plugin modify saved content in the database? =

No. Processing runs only when HTML is rendered.

= Can typography be disabled in headings? =

Yes. Use "Disable typography in headings" for the needed heading levels (`h1`-`h6`). Use "Disable typography in post and card titles" for post and card titles that pass through `the_title()`.

= What happens when the plugin is uninstalled? =

When removed through WordPress, the plugin deletes its saved settings from `wp_options`.

== Changelog ==

= 0.1.0 =

* Initial plugin release.
