=== WordPress Auto-updates ===
Contributors: wordpressdotorg, audrasjb, whodunitagency, desrosj, xkon, karmatosed
Requires at least: 5.3
Tested up to: 5.4
Requires PHP: 5.6.20
Tested up to: 5.4
Stable tag: 0.1.4
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

To test/contribute, just install this plugin and activate it on your WordPress installation.

**Features / to-do list** 🛠

- ✅ Open a Trac ticket to handle Core merge for plugins
- ✅ Open a Trac ticket to handle Core merge for themes
- ✅ Handle plugin auto-updates
- 🔲 Handle themes auto-updates
- ✅ Handle plugin auto-updates in a multisite context
- 🔲 Handle themes auto-updates in a multisite context
- 🔲 Email notifications for plugins
- 🔲 Email notifications for themes
- 🔲 Validate design for plugins screen
- 🔲 Validate design for themes screen
- 🔲 Validate design for update-core screen
- 🔲 Documentation
- ✅ Create and add feature plugin assets
- ✅ Submit Feature Plugin on WordPress.org repository
- ✅ Get the plugin featured as beta plugin on WordPress.org
- 🔲 Move the repository to WordPress.org GitHub account
- ✅ Publish the feature plugin proposal
- 🔲 Open a dedicated Slack channel on Make WordPress
- 🔲 Copy review
- 🔲 Accessibility audit
- 🔲 Security audit
- 🔲 Coding standards audit
- 🔲 Inline Docs audit

== Changelog ==

= Version 0.1.4 =
February 26, 2020
- Fix PHP warnings.

= Version 0.1.3 =
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
