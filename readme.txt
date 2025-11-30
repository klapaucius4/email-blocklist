=== Email Blocklist ===
Contributors: klapaucius4
Tags: disposable emails, temporary emails, validate email, spam prevention, user registration, wordpress security, fake accounts, block domains, antispam
Requires at least: 5.2
Tested up to: 6.8
Stable tag: 1.2.1
License: GPLv2 or later

Keep your WordPress site clean by blocking signups and comments from temporary or disposable email domains. 100% free, no paid APIs.

== Description ==
Email Blocklist helps you keep your WordPress site safe and clean by preventing registrations and comments from users with disposable, temporary, or otherwise unwanted email domains.

Spam registrations and fake accounts often rely on throwaway email addresses. With Email Blocklist, you can easily stop them at the source. The plugin lets you build and manage your own custom blocklist of domains to prevent low-quality signups, spam comments, and fake interactions.

Unlike many similar plugins, Email Blocklist is completely free and does not rely on any paid APIs or third-party services. Everything runs directly on your WordPress installation. No hidden costs, no subscriptions – just a lightweight solution that does one job and does it well.

== External Service Usage ==
This plugin uses a public GitHub repository to fetch a global blocklist. 
One JSON file contains the list of blocked domains (https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist.json), 
and another holds basic metadata (https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist-meta.json).

The blocklist is downloaded during plugin activation. A daily WP-Cron task checks for updates, 
and the list is refreshed automatically if a newer version is available. 
You can also trigger a manual update from the plugin settings page.

Note: This plugin sends requests to GitHub to fetch the blocklist files. 
By using this plugin, data is transmitted to GitHub under GitHub's Terms of Service (https://docs.github.com/en/site-policy/github-terms/github-terms-of-service) 
and GitHub Privacy Statement (https://docs.github.com/en/site-policy/privacy-policies/github-privacy-statement).

Email Blocklist also lets you scan existing users and flag accounts using suspicious or blocked email domains as potential spam, highlighting them in the user list so you can easily filter or remove them if needed.

== Installation ==
1. Download and install the plugin from the WordPress Plugin Directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to Settings → Email Blocklist to configure your blocklist.
4. Save your changes – unwanted signups and comments will now be blocked automatically.

== Screenshots ==
1. Settings to manage your local blocklist & allowlist
2. Settings to manage global blocklist and other options
3. Example of what you see when you enter a disallowed email address during registration
4. Message shown when a disallowed email address is entered during WooCommerce registration

== Frequently Asked Questions ==
= Does this plugin rely on external APIs? =
No. Email Blocklist is fully self-contained and works entirely within your WordPress installation. No external lookups, no paid services required.

= Is the plugin really free? =
Yes – 100% free, with no hidden costs, upsells, or premium versions.

= Can I add my own domains to block? =
Absolutely. You can manage your own blocklist in the plugin settings.

= Will it work with other plugins that use email fields? =
Yes, Email Blocklist integrates seamlessly with WordPress core registration and comments, and should work with most plugins that rely on standard email fields.

== Changelog ==
= 1.2.1 =
* Fix error with clearing user meta data after plugin uninstall
* Add info about scan existing users into readme files
* Optimize the method of scanning existing users

= 1.2.0 =
* Add feature to scan existing users for potential spam accounts
* Modify plugin metadata and plugin header
* Add over 60k domains to the global blocklist

= 1.1.3 =
* Fix typo in composer.json
* Update the global blocklist
* Remove the unnecessary ‘languages’ folder

= 1.1.2 =
* Implement escaping functions for all other displayed data
* Add prefix to scheduled WP Cron event hook name
* Updated readme files to document use of external service (global blocklist at GitHub)

= 1.1.1 =
* Add proper escaping of outputs
* Change the prefixes for declarations, globals and stored data from 'eb' to 'embl'

= 1.1.0 =
* Change the 'global blocklist update time setting' from universal to local
* Implement automatic global blocklist updates via CRON (daily)
* Add safeguards against global blocklist fetch failures

= 1.0.0 =
First version