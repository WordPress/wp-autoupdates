=== WordPress Auto-updates ===
Contributors: wordpressdotorg, audrasjb, whodunitagency, pbiron, xkon, karmatosed, mapk
Requires at least: 5.3
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 0.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.

== Description ==

The purpose of this plugin is to prepare a future Plugins & Themes automatic updates feature.

**This is a Beta Test Plugin, you shouldn’t use it in production.**

**Please note that the code of this plugin is not meant to land in WordPress Core "as is", it will be completely reworked for core merge. This plugin is meant to discuss auto-updates principles and user interface.**

In 2018, Matt Mullenweg posted 9 projects for Core to focus on in 2019. We didn’t ship as many as hoped, but we made a lot of progress. Plugins and Themes Automatic Updates were one of those 9 projects. This project is now milestoned to WordPress 5.5 and this feature plugin is here to help move towards this achievement.

For full details, see the [Feature Plugin proposal published on Make/Core](https://make.wordpress.org/core/2020/02/26/feature-plugin-wp-auto-updates/).

- [See also: Update on the 9 projects for 2019](https://make.wordpress.org/core/2019/12/06/update-9-projects-for-2019/):
- [Related Trac ticket for plugins auto-updates](https://core.trac.wordpress.org/ticket/48850)
- [Related Trac ticket for themes auto-updates](https://core.trac.wordpress.org/ticket/48850)

[This project is currently developed on GitHub](https://github.com/audrasjb/wp-autoupdates)

Interested in contributing to this plugin? Feel free to join us in `#core-auto-updates` channel on Make WordPress Slack Team. We’ll host weekly meetings on Slack every Tuesdays at 18:00 UTC.

== Changelog ==

= 0.3.0 =
March 16, 2020
- Add functions to handle plugins updates notification emails - [#54](https://github.com/audrasjb/wp-autoupdates/pull/54)
- Remove update time text after manual update - [#43](https://github.com/audrasjb/wp-autoupdates/pull/43)
- Ensure "Automatic Updates" column is not added if no content would be output in the column - [#57](https://github.com/audrasjb/wp-autoupdates/pull/57)
- Specific messages for delayed or disabled cron events - [#58](https://github.com/audrasjb/wp-autoupdates/pull/58)
- Prevent mis-match between count in Auto-updates Enabled view and the number of plugins displayed for that view by applying 'all_plugins' filter before computing that count. - [#59](https://github.com/audrasjb/wp-autoupdates/pull/59)

= 0.2.1 =
March 11, 2020
- Prevent "PHP Notice: Undefined index: plugin_status" when adding the autoupdates_column - [#47](https://github.com/audrasjb/wp-autoupdates/pull/47)
- Add plugin_status query arg to the enable/disable links in the Automatic Updates column - [#48](https://github.com/audrasjb/wp-autoupdates/pull/48)

= 0.2 =
March 6, 2020
- Remove auto-updates column from mustuse and dropins screens - [#39](https://github.com/audrasjb/wp-autoupdates/pull/39)
- Ensure the the enable/disable bulk actions appear in the dropdown and are handled in multisite - [#38](https://github.com/audrasjb/wp-autoupdates/pull/38)
- Remove dashicon from "Enable" text in plugins auto-updates column - [#36](https://github.com/audrasjb/wp-autoupdates/pull/36)
- Replace "Automatic Updates" with "Auto-updates" in filters - [#35](https://github.com/audrasjb/wp-autoupdates/pull/35)
- Display only filters with at least one available plugin - [#33](https://github.com/audrasjb/wp-autoupdates/pull/33)
- Remove setting from site option when deleting plugin - [#32](https://github.com/audrasjb/wp-autoupdates/pull/32)
- Populate site health with plugins auto-updates informations - [#24](https://github.com/audrasjb/wp-autoupdates/pull/24)
- In multisite, only add the "Automatic Updates" column on the plugins-network screen - [#21](https://github.com/audrasjb/wp-autoupdates/pull/21)
- Add auto-update-enabled and auto-update-disabled views on the plugins screen - [#18](https://github.com/audrasjb/wp-autoupdates/pull/18)

= 0.1.5 =
February 26, 2020
- Fix fatal error on PHP 7+
- Fix legacy notice classes
- Various tiny enhancements
- Replace required PHP version

= 0.1.4 =
February 26, 2020
- Fix PHP warnings.

= 0.1.3 =
February 25, 2020
- Replace all "autoupdate" occurrences with "auto-update" which is now the official wording.

= 0.1.2 =
February 23, 2020
- Add time to next update in Plugins screen.

= 0.1.1 =
February 19, 2020
* Fixes few PHP notices/warnings.

= 0.1 =
February 18, 2020
* Initial release
