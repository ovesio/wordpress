== Ovesio – Automated AI Translation ==
Tested up to: 6.8
Requires at least: 6.2
Requires PHP: 7.2
License: GPLv2 or later
Stable tag: 1.3.3
Contributors: ovesio, awebro
Tags: multilingual, translate, translation, language, localization

Automatically translate your WordPress into 27+ languages with Ovesio's [AI Translation ](https://ovesio.com/) Engine.

== Description ==

### Scale To International Markets In Hours With Multilingual AI ###

* 100% no-code setup and use
* Best AI translation quality on the market
* Built-in quality assurance through Ovesio's [AI Inspector](https://ovesio.com/blog/what-is-the-ovesio-ai-inspector)

https://www.youtube.com/watch?v=qLORdkCpINA

Translate all your WooCommerce products into 27+ languages with only one click.

Supercharge your content with multilingual AI.

Translate posts, pages, and products with one click, powered by Ovesio.

== What this plugin does ==
– Adds Translate buttons under Posts, Pages, Categories, Tags (and WooCommerce products if you use WooCommerce).
– Sends the text to Ovesio for translation / content generation.
– Creates the translated item(s) automatically and links it with the original (via Polylang).
– Keeps a Requests List so you can see what was sent, when, and the status.

== What you need (dependencies) ==
– WordPress 6.3 or newer
– PHP 7.4+ (8.0+ recommended)
– Polylang (free or Pro) – required for languages
– WooCommerce (optional) – only if you want to translate products & product taxonomies
– An Ovesio account + API key

== Install & activate ==

Upload the ovesio folder (extracted from ovesio-vX.X.X.zip) to /wp-content/plugins/, or install it via Plugins <span aria-hidden="true" class="wp-exclude-emoji">→</span> Add New <span aria-hidden="true" class="wp-exclude-emoji">→</span> Upload.

Activate it in Plugins.

After activation the plugin creates its own table in the database – nothing you need to do.

== First-time setup ==
(Settings <span aria-hidden="true" class="wp-exclude-emoji">→</span> Ovesio)

=== API tab ===
– API URL – leave default unless Ovesio told you otherwise.
– API Key – paste the key from your Ovesio dashboard.

Click Save Changes.

=== The Translation Process ===
– Content language – choose the source language (or "System" to use Polylang's default).
– Workflow – pick your Ovesio workflow ID (if you use one).
– Translate to – tick the languages you want to generate.
– Post status – choose if new content should be Publish, Draft, etc.

Click Save Changes again.

== How to use it ==

Go to Posts, Pages, Categories, Tags (or Products if WooCommerce).

Hover an item – you'll see Translate (with flags) and Translate All.

Click a flag to translate into that language, or Translate All to send to every selected language.

That's it. Once Ovesio finishes, the translated item appears automatically.

= Check the status =
– Ovesio <span aria-hidden="true" class="wp-exclude-emoji">→</span> Requests List shows every request, its status (Pending / Completed), date, and a link to the Ovesio job.

== What the service is and what it is used for ==
This plugin helps you to translate your website content using Ovesio.com services. You can use it to translate pages, posts, taxonomies, categories and WooCommerce products.

== What data is sent and when ==
This plugin only sends data to ovesio.com when you manually trigger a translation by pressing the translate link present in these sections: post/page/taxonomy/category/product.
Your API keys are stored securely in your WordPress database and are never shared with the plugin developer.
The default domain used for sending data is https://api.ovesio.com/v1/ but in some cases ovesio.com can provide you with a different subdomain of the same domain name in order to be used for translation service.
We do not collect sensitive data or personal information.

== Ovesio terms of service and privacy policy ==
Please check Ovesio.com terms and conditions here: https://ovesio.com/information/terms
and the privacy policy here: https://ovesio.com/information/privacy

== Troubleshooting ==
– Nothing happens when I click Translate: Check you are logged in as a user who can edit posts. Make sure the API key is valid.
– Callback not working / 404: Re-save permalinks. Ensure the Security Hash in the URL matches the one in settings.
– Translations go to the wrong status: Change Post status in Translation settings.
– Polylang errors: Confirm Polylang is active and languages are set.

== Frequently Asked Questions ==

= Do I need to edit code =
No. Everything is done from the WordPress admin.

= Can I pick which fields are translated? =
The plugin sends title, content, excerpt and handles taxonomies; advanced control may require adjusting the workflow in Ovesio.

= Does it work without Polylang? =
No, Polylang is required to assign languages and link translations.

= WooCommerce support? =
Yes. Products and product categories/tags are supported if WooCommerce is active.

== Support & feedback ==
– Open an issue on GitHub (if you host the code there)

== Screenshots ==

1. Ovesio AI's Dashboard
2. Ovesio AI's Translations List
3. Ovesio WP Plugin API Settings
4. Ovesio WP Plugin Translation Settings
5. Ovesio WP Plugin Requests List
6. Ovesio WP Plugin Example of new buttons on hover

== Changelog ==
= 1.3.3 =
Readme file updated

= 1.3.1 =

Lang flags fix.

Composer updated.

Log removed.

= 1.3.0 =

New: Bulk "Translate All" option for entire categories.

Improved handling of long content (optimized chunking for API limits).

= 1.1.0 =

Initial WooCommerce product translation support.

= 1.0.0 =

Initial release – Posts, Pages, Categories, Tags translation with Polylang integration.

== Upgrade Notice ==
= 1.3.3=
Lang flags fix.
Composer updated.
Log removed.

Enjoy faster multilingual publishing with Ovesio! 🚀