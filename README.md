# Email Blocklist

**Keep your WordPress site clean by blocking signups and comments from temporary or disposable email domains. 100% free, no paid APIs.**

---

## Description

**Email Blocklist** helps you keep your WordPress site safe and clean by preventing registrations and comments from users with disposable, temporary, or otherwise unwanted email domains.

Spam registrations and fake accounts often rely on throwaway email addresses. With Email Blocklist, you can easily stop them at the source. The plugin lets you build and manage your own custom blocklist of domains to prevent low-quality signups, spam comments, and fake interactions.

Unlike many similar plugins, **Email Blocklist is completely free and does not rely on any paid APIs or third-party services**. Everything runs directly on your WordPress installation. No hidden costs, no subscriptions – just a lightweight solution that does one job and does it well.


### External Service Usage
This plugin uses a public GitHub repository to fetch a global blocklist:
* [blocklist.json](https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist.json) – list of blocked domains
* [blocklist-meta.json](https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist-meta.json) – metadata about the blocklist

The blocklist is downloaded during plugin activation. A daily WP-Cron task checks for updates, and the list is refreshed automatically if a newer version is available. You can also trigger a manual update from the plugin settings page.

Note: This plugin sends requests to GitHub to fetch the blocklist files. By using this plugin, data is transmitted to GitHub under [GitHub’s Terms of Service](https://docs.github.com/en/site-policy/github-terms/github-terms-of-service) and [GitHub Privacy Statement](https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement).

---

## Features

- 🚫 Block registrations from disposable, temporary, or unwanted email domains
- 💬 Prevent spam comments from users with blocked domains
- 📝 Manage your own customizable blocklist with ease
- ⚡ Lightweight, fast, and simple to use
- 💯 100% free – no hidden costs, no subscriptions
- 🔒 No reliance on external paid APIs or third-party services

---

## Installation

1. Download and install the plugin from the WordPress Plugin Directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Email Blocklist** to configure your blocklist.
4. Save your changes – unwanted signups and comments will now be blocked automatically.

---

## Frequently Asked Questions

**Q: Does this plugin rely on external APIs?**
A: No. Email Blocklist is fully self-contained and works entirely within your WordPress installation. No external lookups, no paid services required.

**Q: Is the plugin really free?**
A: Yes – 100% free, with no hidden costs, upsells, or premium versions.

**Q: Can I add my own domains to block?**
A: Absolutely. You can manage your own blocklist in the plugin settings.

**Q: Will it work with other plugins that use email fields?**
A: Yes, Email Blocklist integrates seamlessly with WordPress core registration and comments, and should work with most plugins that rely on standard email fields.

---

## License
This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
