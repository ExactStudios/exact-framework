# Changelog

## 1.0.0 – 2026-10-21

### Breaking

- `ExactStudios\Updates` and `ExactStudios\JSON` are now `ExactStudios\Framework`.
- Requires PHP 8.0 or newer.

### Fixed

- Migrations store the version that ran, skip files outside the updates directory, and can run from cron and WP-CLI.
- JSON output prints only valid JavaScript identifiers, through `wp_print_inline_script_tag()`.
- Server and env values are read from the superglobal. False HTML attributes are omitted.
- Textarea, bool, and float sanitizers no longer fatal on PHP 8.
- A dependency handle drops only a leading `_pre_` marker.
- Install hooks register when the object is constructed. A new site activates only when the plugin is network-active.
