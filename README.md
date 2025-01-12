# Recruit Connect WP

A WordPress plugin for importing and managing job vacancies from Recruit Connect.

## Description

Recruit Connect WP integrates your WordPress website with Nubos B.V.'s Recruit Connect platform, allowing you to:
- Import job vacancies from XML feed
- Display vacancies with customizable layouts
- Handle job applications
- Manage vacancy details and application forms

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

1. Upload the `recruit-connect-wp` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Recruit Connect' in your admin menu to configure the plugin

## Usage

### Shortcodes
`[recruit_connect_vacancies limit="10" layout="grid"]`
`[recruit_connect_search] [recruit_connect_vacancy id="123"]`

### Templates

You can override the plugin templates by copying them from:
`/wp-content/plugins/recruit-connect-wp/templates/`
to:
`/wp-content/themes/your-theme/recruit-connect/`

## Support

For support, please contact support@nubos.nl or visit https://www.nubos.nl/en/recruit-connect

find . -type f -not -path "*/node_modules/*" \( -name "*.js" -o -name "*.css" -o -name "*.scss" -o -name "*.php" \) -exec echo "=== {} ===" \; -exec cat {} \; > output.txt
