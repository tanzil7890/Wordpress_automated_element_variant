=== Element Variants ===
Contributors: Mohammad Tanzil Idrisi
Tags: personalization, content, element, variant, user experience
Requires at least: 5.2
Tested up to: 6.3
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create personalized content variants for different users by selecting page elements and creating variations.

== Description ==

Element Variants allows you to create personalized content for different users by visually selecting elements on your pages and creating variations based on user roles, login status, and more.

Similar to Outhad AI's variant creation system, this plugin makes it easy to personalize your WordPress site without coding.

= Key Features =

* **Visual Element Selection**: Click on any element on your pages to select it
* **Variant Creation**: Create custom content variants for the selected elements
* **Conditional Display**: Show variants based on user roles, logged-in status, or specific user IDs
* **User-Friendly Interface**: No coding required - everything is managed through visual editors
* **Seamless Integration**: Works with most WordPress themes and plugins

= Use Cases =

* Personalize content for different user roles (e.g., members vs. non-members)
* Display different messaging to logged-in vs. logged-out users
* Create customized experiences for specific users
* Test different content variations

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/element-variants` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Element Variants menu in your admin panel to configure the plugin.
4. Visit any page on your site with the editor enabled to start creating variants.

== Frequently Asked Questions ==

= How do I create my first variant? =

1. Go to Element Variants > Settings and ensure the Frontend Editor is enabled.
2. Click on "Open Frontend Editor" to visit your site with the editor active.
3. Click "Select Element" in the editor panel, then click on any element on your page.
4. Create your variant content and set conditions for when it should display.
5. Save your variant and it will automatically be applied when the conditions are met.

= Will variants work for all users or just administrators? =

You can control which user roles can see variants in the Element Variants settings. By default, administrators and editors can see variants.

= Can I create multiple conditions for a variant? =

Yes, you can add multiple conditions to a variant. The variant will only be displayed when all conditions are met.

= Does this plugin modify my database? =

Yes, the plugin creates two custom tables in your database to store variants and their conditions. All data is removed when you uninstall the plugin.

== Screenshots ==

1. Frontend editor for selecting elements
2. Creating a variant with custom content
3. Managing variants in the admin panel
4. Settings page

Here's an overview of what the plugin does:

* **Visual Element Selection**: Users can enable the frontend editor and visually select any element on a page by clicking on it.

* **Variant Creation**: After selecting an element, users can create custom HTML content that will replace the original element content.

* **Conditional Display**: Variants can be configured to display only for specific user roles, logged-in users, or specific user IDs.

* **Admin Management**: A complete admin interface allows for creating, editing, and managing all variants.

* **Settings**: Users can control which user roles can see variants and enable/disable the frontend editor.

== Key Components ==

* **Frontend Editor**: A visual interface that appears at the bottom-right of the page, allowing users to select elements and create variants.

* **Element Selection**: Javascript that highlights and detects elements when the user hovers over them, generating a CSS selector.

* **Variant Manager**: PHP classes for storing and retrieving variants from the database.

* **Conditional Logic**: System to determine when a variant should be displayed based on user conditions.

* **Admin Interface**: Complete admin pages for variant management and plugin settings.

== How to Use the Plugin ==

1. Install and activate the plugin in WordPress
2. Go to "Element Variants" in the WordPress admin menu
3. Click "Open Frontend Editor" to go to your site with the editor enabled
4. Click "Select Element" in the editor and click on any element on the page
5. Create your variant content and set conditions
6. Save the variant, and it will be applied when the conditions are met

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release 