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

## Features / to-do list 🛠

- ✅ Open a Trac ticket to handle Core merge for plugins
- ✅ Open a Trac ticket to handle Core merge for themes
- ✅ Create and add feature plugin assets
- ✅ Submit Feature Plugin on WordPress.org repository
- ✅ Get the plugin featured as beta plugin on WordPress.org
- 🔲 Move the repository to WordPress.org GitHub account
- ✅ Publish the feature plugin proposal
- ✅ Open a dedicated Slack channel on Make WordPress
- ✅ Launch weekly meetings on Slack
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
- 🔲 Copy review
- 🔲 Accessibility audit
- 🔲 Security audit
- 🔲 Coding standards audit
- 🔲 Inline Docs audit

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
- [@passionate](https://profiles.wordpress.org/passionate/)
- [@nicolaskulka](https://profiles.wordpress.org/nicolaskulka/)
- [@bookdude13](https://profiles.wordpress.org/bookdude13/)

## Documentation 📚

Work in progress.

## Screenshots 🖼

### Plugins Admin screen - screenshot

![Plugins Admin screen - screenshot](https://jeanbaptisteaudras.com/images/wp-autoupdates-plugins-01.png)

### Plugins Admin screen - Toggle update single plugin - screenshot

![Plugins Admin screen - Toggle update single plugin - animated screenshot](https://jeanbaptisteaudras.com/images/wp-autoupdates-togglesingleplugin-01.gif)

### Plugins Admin screen - Buk Edit - animated screenshot

![Plugins Admin screen - Buk Edit - animated screenshot](https://jeanbaptisteaudras.com/images/wp-autoupdates-bulkeditplugins-01.gif)

### Update Core Admin Screen

![Update Core Admin screen - screenshot](https://jeanbaptisteaudras.com/images/wp-autoupdates-updatecore-01.png)

## Changelog 🗓

### Version 0.1.5 🐣
February 26, 2020
- Fix fatal error on PHP 7+
- Fix legacy notice classes
- Various tiny enhancements
- Replace required PHP version

### Version 0.1.4 👻
February 26, 2020
- Fix PHP warnings.

### Version 0.1.3 ☀️
February 25, 2020
- Replace all "autoupdate" occurrences with "auto-update" which is now the official wording.

### Version 0.1.2
February 23, 2020
- Add time to next update in Plugins screen.

### Version 0.1.1
February 19, 2020
- Fixes few PHP notices/warnings.

### Version 0.1
February 18, 2020
- Initial release