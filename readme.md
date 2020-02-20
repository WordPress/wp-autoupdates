# WordPress Autoupdates 🤖

![WordPress AutoUpdates](https://jeanbaptisteaudras.com/images/wp-autoupdates-banner.png)

A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.

## About 🔎

The purpose of this repository is to prepare a future Plugins & Themes automatic updates feature.

To test/contribute, just install this plugin and activate it on your WordPress installation.

The goal of this plugin is to test the UI of the feature, to help decision making. It uses WordPress Core hooks (with potentially some hacks when needed).

## Context ⏳

In 2018, Matt Mullenweg posted 9 projects for Core to focus on in 2019. We didn’t ship as many as hoped, but we made a lot of progress. Plugins and Themes Automatic Updates were one of those 9 projects. This project is now milestoned to WordPress 5.5 and this feature plugin is here to help move towards this achievement.

- [See also: Update on the 9 projects for 2019](https://make.wordpress.org/core/2019/12/06/update-9-projects-for-2019/):
- [Related Trac ticket for plugins autoupdates](https://core.trac.wordpress.org/ticket/48850)
- [Related Trac ticket for themes autoupdates](https://core.trac.wordpress.org/ticket/48850)

## Features / to-do list 🛠

- ✅ Open a Trac ticket to handle Core merge for plugins
- ✅ Open a Trac ticket to handle Core merge for themes
- ✅ Handle plugin autoupdates
- 🔲 Handle themes autoupdates
- ✅ Handle plugin autoupdates in a multisite context
- 🔲 Handle themes autoupdates in a multisite context
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
- 🔲 Publish the feature plugin proposal
- 🔲 Open a dedicated Slack channel on Make WordPress
- 🔲 Copy review
- 🔲 Accessibility audit
- 🔲 Security audit
- 🔲 Coding standards audit
- 🔲 Inline Docs audit

## Contributors 👥

- [@audrasjb](https://profiles.wordpress.org/audrasjb/)
- [@whodunitagency](https://profiles.wordpress.org/whodunitagency/)
- [@xkon](https://profiles.wordpress.org/xkon/)
- [@desrosj](https://profiles.wordpress.org/desrosj/)
- [@pedromendonca](https://profiles.wordpress.org/pedromendonca/)
- [@javiercasares](https://profiles.wordpress.org/javiercasares/)
- [@karmatosed](https://profiles.wordpress.org/karmatosed/)

## Documentation 📚

TODO.

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

### Version 0.1.1
February 19, 2020
- Fixes few PHP notices/warnings.

### Version 0.1
February 18, 2020
- Initial release