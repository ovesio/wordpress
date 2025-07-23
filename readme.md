# Ovesio for WordPress

A simple way to auto-translate your WordPress content with Ovesio’s AI. No coding, no fuss.

---

## 1. What this plugin does
- Adds **Translate** buttons under Posts, Pages, Categories, Tags (and WooCommerce products if you use WooCommerce).
- Sends the text to **Ovesio** for translation / content generation.
- Creates the translated item automatically and links it with the original (via Polylang).
- Keeps a **Requests List** so you can see what was sent, when, and the status.

---

## 2. What you need (dependencies)
- **WordPress** 6.3 or newer
- **PHP** 7.4+ (8.0+ recommended)
- **Polylang** (free or Pro) – required for languages
- **WooCommerce** (optional) – only if you want to translate products & product taxonomies
- An **Ovesio account + API key**

---

## 3. Install & activate
1. Upload the **`ovesio`** folder (extracted from `ovesio-vX.X.X.zip`) to `/wp-content/plugins/`, or install it via **Plugins → Add New → Upload**.
2. Activate it in **Plugins**.
3. After activation the plugin creates its own table in the database – nothing you need to do.

---

## 4. First-time setup (Settings → Ovesio)

### API tab
- **API URL** – leave default unless Ovesio told you otherwise.
- **API Key** – paste the key from your Ovesio dashboard.

Click **Save Changes**.

### Translation tab
- **Content language** – choose the source language (or “System” to use Polylang’s default).
- **Workflow** – pick your Ovesio workflow ID (if you use one).
- **Translate to** – tick the languages you want to generate.
- **Post status** – choose if new content should be **Publish**, **Draft**, etc.

Click **Save Changes** again.

---

## 5. How to use it
1. Go to **Posts**, **Pages**, **Categories**, **Tags** (or **Products** if WooCommerce).
2. Hover an item – you’ll see **Translate** (with flags) and **Translate All**.
3. Click a flag to translate into that language, or **Translate All** to send to every selected language.
4. That’s it. Once Ovesio finishes, the translated item appears automatically.

### Check the status
- **Ovesio → Requests List** shows every request, its status (Pending / Completed), date, and a link to the Ovesio job.

---

## 6. Troubleshooting
- **Nothing happens when I click Translate**: Check you are logged in as a user who can edit posts. Make sure the API key is valid.
- **Callback not working / 404**: Re-save permalinks. Ensure the **Security Hash** in the URL matches the one in settings.
- **Translations go to the wrong status**: Change **Post status** in Translation settings.
- **Polylang errors**: Confirm Polylang is active and languages are set.

---

## 7. FAQ
**Do I need to edit code?**
No. Everything is done from the WordPress admin.

**Can I pick which fields are translated?**
The plugin sends title, content, excerpt and handles taxonomies; advanced control may require adjusting the workflow in Ovesio.

**Does it work without Polylang?**
No, Polylang is required to assign languages and link translations.

**WooCommerce support?**
Yes. Products and product categories/tags are supported if WooCommerce is active.

---

## 8. Support & feedback
- Open an issue on GitHub (if you host the code there)

---

Enjoy faster multilingual publishing with Ovesio! 🚀
