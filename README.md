# Recruit Connect WP Plugin

<img src="admin/images/logo.png" alt="Recruit Connect Logo" width="200" height="auto" style="display: block; margin: 20px 0;">

## 📝 Description
Recruit Connect WP is a WordPress plugin that enables seamless integration with the Recruit Connect platform. It allows you to import and display job vacancies from an XML feed, manage applications, and customize the display of vacancy information.

## ✨ Features

### 🔄 XML Import
- Automated vacancy import from XML feed
- Configurable import frequency (hourly, twice daily, daily, every 4 hours)
- Retry mechanism for failed imports
- Logging system for import tracking
- Automatic cleanup of outdated vacancies

### 📋 Vacancy Management
- Custom post type for vacancies
- Detailed vacancy information storage
- Meta fields for all vacancy details
- Protected from manual editing to maintain data integrity

### 🎨 Display Options
- Gutenberg blocks for vacancy fields
- Customizable vacancy overview pages
- Detailed single vacancy templates
- Responsive grid and list layouts
- Search and filter functionality

### 📬 Application System
- Built-in application form
- Support for both vacancy-specific and open applications
- Customizable required fields
- File upload for CV (PDF, DOC, DOCX)
- AJAX form submission
- Custom thank you messages
- Application data storage in WordPress
- External submission to configured endpoint
- Retry mechanism for failed submissions
- Admin notifications for submission issues

### 🔍 Search & Filter
- Advanced search functionality
- Multiple filter options:
    - Education level
    - Job type
    - Location
    - Salary range
    - Category
- Real-time AJAX filtering
- URL parameter support for sharing filtered results

### ⚙️ Admin Interface
- Comprehensive dashboard
- Statistics overview
- Import logs
- Settings management:
    - General configuration
    - Application form settings
    - Synchronization options
    - Detail page customization
    - Log viewer

### 🔌 Shortcodes

Display vacancies list:
```php
[recruit_connect_vacancies limit="10" category="" education="" jobtype="" layout="grid"]
```
Display search form:
```php
[recruit_connect_search show_category="true" show_education="true" show_jobtype="true" show_salary="true"]
```
Display application form:
```php
[recruit_connect_application_form]
```
Display vacancy overview:
```php
[recruit_connect_vacancy_overview]
```
### 🧩 Blocks
- **Vacancy Field Block**
    - Display individual vacancy fields
    - Customizable appearance
    - Dynamic preview in editor

## 🚀 Installation

1. Upload the plugin files to `/wp-content/plugins/recruit-connect-wp`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under 'Recruit Connect' in the admin menu

## ⚙️ Configuration

### Required Settings
1. XML Feed URL (General Settings)
2. Application Destination URL (General Settings)
3. Required Fields for Application Form
4. Synchronization Frequency

### Optional Settings
- Thank You Message Customization
- Search Components Selection
- Detail Page Field Configuration
- Field Order Customization

## 💻 Technical Requirements

| Requirement | Version |
|------------|---------|
| WordPress  | 6.0+    |
| PHP        | 7.4+    |
| MySQL      | 5.6+    |

Additional requirements:
- Write permissions for file uploads
- Active cron system for scheduled tasks

## 🔌 API Integration

### External Submission Endpoint
The application form submits data to the configured external endpoint with the following structure:

```json
{
  "name": "Applicant Name",
  "email": "email@example.com",
  "phone": "phone_number",
  "motivation": "motivation_text",
  "cv_url": "url_to_uploaded_cv",
  "vacancy_id": "vacancy_id_or_null_for_open_applications",
  "application_type": "vacancy|open"
}
```
### Retry Mechanism
| Attempt | Delay (minutes) |
|---------|----------------|
| 1       | 5             |
| 2       | 15            |
| 3       | 30            |
| 4       | 60            |
| 5       | 120           |

> Admin notification is sent after final retry failure

## 🎨 Customization

### Templates
Override plugin templates by copying them to your theme:

```plaintext
your-theme/recruit-connect/
├── single-vacancy.php
├── vacancy-overview.php
├── application-form.php
└── search-form.php
```
### Styling
Custom CSS classes for all elements:
```css
.recruit-connect-vacancy-field    /* Vacancy field block */
.recruit-connect-application-form /* Application form container */
.recruit-connect-vacancy-overview /* Vacancy overview container */
.vacancy-card                    /* Individual vacancy card */
.vacancy-filters                 /* Filter sidebar */
```
### Filters and Actions

Modify vacancy data before import:
```php
add_filter('recruit_connect_vacancy_data', 'your_function', 10, 1);
```

Custom validation for applications:
```php
add_filter('recruit_connect_application_validation', 'your_function', 10, 2);
```

Actions before/after application submission:
```php
add_action('recruit_connect_before_application_submit', 'your_function', 10, 1);
add_action('recruit_connect_after_application_submit', 'your_function', 10, 2);
```

## 🆘 Support

For support inquiries:
1. Check the plugin documentation
2. Use the support form in the WordPress admin
3. Contact support@nubos.nl

## 📄 License
GPL-2.0+

## 👥 Credits
Developed by [Nubos B.V.](https://www.nubos.nl)

---

<details>
<summary>📝 Changelog</summary>

### 1.0.0
- Initial release
- XML import functionality
- Vacancy management
- Application system
- Search and filter capabilities
- Admin interface
- Customization options

</details>

find . -type f -not -path "*/node_modules/*" \( -name "*.js" -o -name "*.css" -o -name "*.scss" -o -name "*.php" \) -exec echo "=== {} ===" \; -exec cat {} \; > output.txt
