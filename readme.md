# WordPress Auto-updates 🤖

![WordPress Auto-updates](https://jeanbaptisteaudras.com/images/wp-autoupdates-banner.png)

A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.

## About 🔎

The purpose of this repository is to prepare a future Plugins & Themes automatic updates feature.

To test/contribute, just install this plugin and activate it on your WordPress installation.

The goal of this plugin is to test the UI of the feature, to help decision making. It uses WordPress Core hooks (with potentially some hacks when needed).

For full details, see the [Feature Plugin proposal published on Make/Core](https://make.wordpress.org/core/2020/02/26/feature-plugin-wp-auto-updates/).

Interested in contributing to this plugin? Feel free to join us in `#core-auto-updates` channel on Make WordPress Slack Team. We’ll host weekly meetings on Slack every Tuesdays at 18:00 UTC.

## Context ⏳

In 2018, Matt Mullenweg posted 9 projects for Core to focus on in 2019. We didn’t ship as many as hoped, but we made a lot of progress. Plugins and Themes Automatic Updates were one of those 9 projects. This project is now milestoned to WordPress 5.5 and this feature plugin is here to help move towards this achievement.

- [See also: Update on the 9 projects for 2019](https://make.wordpress.org/core/2019/12/06/update-9-projects-for-2019/):
- [Related Trac ticket for plugins auto-updates](https://core.trac.wordpress.org/ticket/48850)
- [Related Trac ticket for themes auto-updates](https://core.trac.wordpress.org/ticket/48850)

## Contributors ♥️

Thanks to everyone who contributed to this feature plugin!

- [@audrasjb](https://profiles.wordpress.org/audrasjb/)
- [@whodunitagency](https://profiles.wordpress.org/whodunitagency/)
- [@xkon](https://profiles.wordpress.org/xkon/)
- [@desrosj](https://profiles.wordpress.org/desrosj/)
- [@pedromendonca](https://profiles.wordpress.org/pedromendonca/)
- [@javiercasares](https://profiles.wordpress.org/javiercasares/)
- [@karmatosed](https://profiles.wordpress.org/karmatosed/)
- [@mapk](https://profiles.wordpress.org/mapk/)
- [@afercia](https://profiles.wordpress.org/afercia/)
- [@gmays](https://profiles.wordpress.org/gmays/)
- [@knutsp](https://profiles.wordpress.org/knutsp/)
- [@pbiron](https://profiles.wordpress.org/pbiron/)
- [@passoniate](https://profiles.wordpress.org/passoniate/)
- [@nicolaskulka](https://profiles.wordpress.org/nicolaskulka/)
- [@bookdude13](https://profiles.wordpress.org/bookdude13/)
- [@jeffpaul](https://profiles.wordpress.org/jeffpaul/)
- [@mukesh27](https://profiles.wordpress.org/mukesh27/)
- [@whyisjake](https://profiles.wordpress.org/whyisjake/)
- [@paaljoachim](https://profiles.wordpress.org/paaljoachim/)
- [@ronalfy](https://profiles.wordpress.org/ronalfy/)
- [@paaljoachim](https://profiles.wordpress.org/paaljoachim/)
- [@timothybjacobs](https://profiles.wordpress.org/timothybjacobs/)
- [@wpamitkumar](https://profiles.wordpress.org/wpamitkumar)
- [@casiepa](https://profiles.wordpress.org/casiepa)

## Documentation 📚

Work in progress.

## Screenshots 🖼

### Plugins screen - Auto-updates activated

![Plugins screen - Auto-updates activated](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-1-plugins.png)

### Plugins screen - Available updates

![Plugins screen - Available updates](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-2-plugins.png)

### Themes screen - Enable auto-updates

![Themes screen - Enable auto-updates](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-3-themes.png)

### Themes screen - Available update

![Themes screen - Available update](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-4-themes.png)

### Updates screen - Available updates

![Updates screen - Available updates](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-5-update-screen.png)

### Site health screen - Auto-updates informations for themes and plugins

![Site health screen - Auto-updates informations for themes and plugins](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-6-site-health-screen.png)

### Email notification example

![Email notification example](https://jeanbaptisteaudras.com/share/wp-auto-updates/wp-auto-updates-7-email-notification.png)

## Changelog 🗓

### 0.8.1 🍄

May 18, 2020

- Fix incorrect admin notice text when auto-updates are enabled for a plugin when JS is not enabled in the browser - [#130](https://github.com/WordPress/wp-autoupdates/pull/130)
- Prevent multiple simultaneous requests - [#131](https://github.com/WordPress/wp-autoupdates/pull/131)
- Strip plugins/themes that have been deleted from the site options just before they are updated - [#132](https://github.com/WordPress/wp-autoupdates/pull/132)
- Copy the plugin's site options to core's site options on self-deactivation - [#133](https://github.com/WordPress/wp-autoupdates/pull/133)
- fix typos and docs standards - [#134](https://github.com/WordPress/wp-autoupdates/pull/134)


### 0.8.0 🐪

May 13, 2020

- Self-deactivate the plugin after the functionality has been merged to core - [#123](https://github.com/WordPress/wp-autoupdates/pull/123)
- Remove auto-updates options when plugin is uninstalled - [#125](https://github.com/WordPress/wp-autoupdates/pull/125)
- Use ES5 in the JS, because the core build tools barf on `let` and `const` - [#127](https://github.com/WordPress/wp-autoupdates/pull/127)
- More pre-merge cleanup - [#124](https://github.com/WordPress/wp-autoupdates/pull/124)
- Add help tabs on update-core, plugins, and themes admin screens - [#121](https://github.com/WordPress/wp-autoupdates/pull/121)
- Cleanup - [#119](https://github.com/WordPress/wp-autoupdates/pull/119)
- I18n - Remove unnecessary concatenation - [#117](https://github.com/WordPress/wp-autoupdates/pull/117)

### 0.7.0 🦢

May 6, 2020

- PHPCBF fixes - [#114](https://github.com/WordPress/wp-autoupdates/pull/114)
- Remove constants from the feature plugin - [#112](https://github.com/WordPress/wp-autoupdates/pull/112)
- Various i18n fixes/optimizations - [#109](https://github.com/WordPress/wp-autoupdates/pull/109)
- Simplifies Ajax on both the JS and PHP sides - [#103](https://github.com/WordPress/wp-autoupdates/pull/103)

### 0.6.0 🦋

April 22, 2020

- Add Ajax to Plugin and Themes Screen - [#61](https://github.com/WordPress/wp-autoupdates/pull/61)
- Accessibility: Communicate AJAX enabling/disabling changes to screen readers - [#85](https://github.com/WordPress/wp-autoupdates/pull/85)
- Add Better Handling to Ajax Errors - [#90](https://github.com/WordPress/wp-autoupdates/pull/90)
- Prevent CSS from being enqueued on sub-site plugins & themes screens in multisite - [#91](https://github.com/WordPress/wp-autoupdates/pull/91)

### 0.5.1 🦒

April 16, 2020

- Add the plugin version when enqueueing styles, for cache busting - [#79](https://github.com/WordPress/wp-autoupdates/pull/79)

### 0.5.0 🦚

April 15, 2020

- Replace Disable strings with Disable auto-updates - [#78](https://github.com/WordPress/wp-autoupdates/pull/78)
- Update confirmation message wording - [#77](https://github.com/WordPress/wp-autoupdates/pull/77)
- Remove Automatic Updates column from the Network Admin > Sites > Edit > Themes screen - [#76](https://github.com/WordPress/wp-autoupdates/pull/76)
- Replace "Enable" string with "Enable auto-updates" - [#75](https://github.com/WordPress/wp-autoupdates/pull/75)
- Remove dashicons from the UI - [#74](https://github.com/WordPress/wp-autoupdates/pull/74)
- Fix documentation and comment standards - [#73](https://github.com/WordPress/wp-autoupdates/pull/73)
- Remove green and red colors on texts and links - [#70](https://github.com/WordPress/wp-autoupdates/pull/70)
- Don't display the Enable/Disable link in the Theme Details modal on a subsite in multisite - [#68](https://github.com/WordPress/wp-autoupdates/pull/68)
- Documentation: Improve DocBlocks - [#62](https://github.com/WordPress/wp-autoupdates/pull/62)
- I18n - Merge with similar string - [#60](https://github.com/WordPress/wp-autoupdates/pull/60)
- Add filters and constant to allow developers to disable plugins and themes autoupdate email notifications - [#57](https://github.com/WordPress/wp-autoupdates/pull/57)
- Switch disable link to red on Multisite Themes Screen - [#54](https://github.com/WordPress/wp-autoupdates/pull/54)
- Wrong kick off year in readme.txt - [#42](https://github.com/WordPress/wp-autoupdates/pull/60)

### 0.4.1 🍺

April 2, 2020

- Network > Sites > Edit > Themes screen doesn’t have the Autoupdates column - [#50](https://github.com/WordPress/wp-autoupdates/pull/50)

### 0.4.0 🌹

March 30, 2020

This release brings full support for Themes auto-updates.

It also changes the plugin structure to allow self deactivation when the feature gets merged into WordPress Core.

Please note: the development repository was also migrated from @audrasjb’s personal GitHub account to WordPress.org official GitHub account.

Other changes:

- Change plugin structure to ensure it can self-deactivate when the feature is merged into Core - [#37](https://github.com/WordPress/wp-autoupdates/pull/37)
- Handle both themes and plugins email notifications - [#36](https://github.com/WordPress/wp-autoupdates/pull/36)
- i18n: Merge similar translation strings - [#35](https://github.com/WordPress/wp-autoupdates/pull/35)
- Add and populate Automatic updates column, add and handle enable/disable auto-updates bulk actions to the multisite themes list table - [#33](https://github.com/WordPress/wp-autoupdates/pull/33)
- Avoid duplicate Updating… dialog - [#32](https://github.com/WordPress/wp-autoupdates/pull/32)

### 0.3.0 🦉

March 16, 2020

- Add functions to handle plugins updates notification emails - [#54](https://github.com/audrasjb/wp-autoupdates/pull/54)
- Remove update time text after manual update - [#43](https://github.com/audrasjb/wp-autoupdates/pull/43)
- Ensure "Automatic Updates" column is not added if no content would be output in the column - [#57](https://github.com/audrasjb/wp-autoupdates/pull/57)
- Specific messages for delayed or disabled cron events - [#58](https://github.com/audrasjb/wp-autoupdates/pull/58)
- Prevent mis-match between count in Auto-updates Enabled view and the number of plugins displayed for that view by applying 'all_plugins' filter before computing that count. - [#59](https://github.com/audrasjb/wp-autoupdates/pull/59)

### 0.2.1 🐜

March 11, 2020

- Prevent "PHP Notice: Undefined index: plugin_status" when adding the autoupdates_column - [#47](https://github.com/audrasjb/wp-autoupdates/pull/47)
- Add plugin_status query arg to the enable/disable links in the Automatic Updates column - [#48](https://github.com/audrasjb/wp-autoupdates/pull/48)

### 0.2.0 🐝

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

### 0.1.5 🐣

February 26, 2020

- Fix fatal error on PHP 7+
- Fix legacy notice classes
- Various tiny enhancements
- Replace required PHP version

### 0.1.4 👻

February 26, 2020

- Fix PHP warnings.

### 0.1.3 ☀️

February 25, 2020

- Replace all "autoupdate" occurrences with "auto-update" which is now the official wording.

### 0.1.2

February 23, 2020

- Add time to next update in Plugins screen.

### 0.1.1

February 19, 2020

- Fixes few PHP notices/warnings.

### 0.1.0

February 18, 2020

- Initial release
