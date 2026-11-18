# [NEWSPAPER_NAME] News – Odia Newspaper WordPress Package (v0.2.0)

Two parts – install **both**:

| Folder | What it is | Install as |
|---|---|---|
| `newspaper-name-news/` | The theme (design, homepage, article page, ads, Customizer) | Theme |
| `newspaper-core/` | The plugin (Breaking/Flash, e-Paper, Galleries, Videos, Districts, Live Updates, roles, Quick Publish, forms, demo) | Plugin |

Content lives in the plugin, so you can change the theme later without losing news, e-Papers or districts.

---

## 1. Installation (10 minutes)

1. Install WordPress 6.6+ on PHP 8.1+ (locally with LocalWP, or on your hosting).
2. **Settings → General**: set *Site Language* = ଓଡ଼ିଆ (Odia) if you want WordPress admin in Odia, *Timezone* = Kolkata.
3. **Settings → Permalinks**: choose *Post name* → Save.
4. Create two zips from this package: `newspaper-core.zip` (the `newspaper-core` folder) and `newspaper-name-news.zip` (the `newspaper-name-news` folder).
   *(Or copy the folders directly into `wp-content/plugins/` and `wp-content/themes/`.)*
5. **Plugins → Add New → Upload Plugin** → `newspaper-core.zip` → **Activate**. (This creates the 30 Odisha districts and newsroom roles.)
6. **Appearance → Themes → Add New → Upload Theme** → `newspaper-name-news.zip` → **Activate**.
7. **Tools → Newspaper Demo → Import demo content** – creates categories, sample Odia stories, breaking items, an e-Paper, all legal pages and menus. Visit your homepage.
8. **Appearance → Customize**: upload logo/favicon (Site Identity), then open **Newspaper Options** to set colours, ticker, sections, social links, ads, footer/RNI, analytics.
9. Put Odia fonts in `newspaper-name-news/assets/fonts/` (see the README there). Optional but recommended.

When you go live: **Tools → Newspaper Demo → Remove demo content**.

## 2. Daily newsroom guide

### Publish a normal story
**Posts → Add New** → headline, text, featured image (+caption) → right side: **Category**, **District**, **News details** (sub-headline, dateline e.g. ଭୁବନେଶ୍ୱର, reporter, photo credit, *Sponsored*) → **Publish**.
Tick *Stick to the top of the blog* to make it the homepage lead story.

### Breaking / Flash in 30 seconds
**Breaking News → ⚡ Quick Publish** (also on the Dashboard):
headline → optional one line → optional photo (phones open the camera) → tick *Breaking ticker* and/or *FLASH* → **Publish now**.
It appears in the red ticker immediately and hides itself after the chosen hours. FLASH also shows a big banner on the homepage.

### Live Updates
Edit a post → tick **Live Updates post** in *News details* → **Update** → a **Live updates** box appears. Type an update → **Post update**. Readers see newest-on-top entries refreshing every 60 seconds.

### Upload today's e-Paper
**e-Paper → Add New**
1. Edition date (defaults to today) and edition name (ଭୁବନେଶ୍ୱର / କଟକ / ବ୍ରହ୍ମପୁର …).
2. **Add / upload pages** → drag all page images (`page-01.jpg`, `page-02.jpg` …) into the window → **Use these pages**. They are sorted by filename automatically; drag thumbnails to reorder.
3. Optional: **Select / upload PDF**.
4. **Publish**. The front page becomes the cover on the homepage and the Dashboard shows ✅.

Readers: the **e-Paper** page has a date picker and recent editions; each edition opens a viewer with thumbnails, zoom (+/−, double-tap, Ctrl+wheel), drag to pan, swipe, keyboard arrows and full screen.

**Settings → Newspaper Core**: restrict e-Paper to logged-in users, turn PDF download on/off, WebP conversion, default breaking expiry.

### Homepage districts menu
The demo menu already has a **ଜିଲ୍ଲା** item. To build it yourself: Appearance → Menus → Screen Options → enable *CSS Classes* → add a Custom Link "ଜିଲ୍ଲା" with URL `#` and CSS class `np-districts`. All 30 districts appear as a mega-menu.

## 3. Roles
| Role | Can do |
|---|---|
| Administrator | Everything |
| Editor | Publish anything incl. e-Paper |
| Reporter | Write + upload photos; *Submit for review* only |
| e-Paper Uploader | Only e-Paper editions and media |

Add users under **Users → Add New**; set *Designation* in the profile (shown on the reporter page).

## 4. Customizer → Newspaper Options
Colours & Fonts · Top Bar (panjika line, e-Paper URL) · Breaking Ticker & Flash (on/off, label, speed, count) · Homepage Sections (order: `hero,epaper,latest,state,national,districts,sports,entertainment,business,editorial,gallery,videos` – delete a key to hide it) · Social Links · **Ad Manager** (header, sidebar, between-sections, in-article after paragraph 3, sticky mobile; each with on/off, device, AdSense code *or* image+link) · Footer & Legal (about, address, RNI) · Analytics & Engagement (GA4, AdSense, OneSignal, dark mode, WhatsApp/Telegram banner).

## 5. Recommended plugins
- **Rank Math** or **Yoast SEO** (+ their News Sitemap module) – the theme hides its own OG tags automatically when they're active.
- **LiteSpeed Cache** (on LiteSpeed hosting) or WP Super Cache. View counts and live updates are AJAX/REST-based, so full-page caching is safe.
- **WP Mail SMTP** – so contact/advertise forms actually deliver.
- **Wordfence** or **Solid Security**; **UpdraftPlus** for backups.
- **OneSignal Push** (or just paste the App ID in Customizer).
- Optional: **MailPoet** (newsletter), **WP-Polls** (polls), **Official AMP plugin** (AMP).

## 6. Status of this version
**Working now:** everything above – theme, Odia dates/numerals, sticky menu + district mega-menu, mobile hamburger + bottom bar, ticker (pause/speed), Flash banner, configurable/reorderable homepage, Most Read (today/7 days), load-more, article page (kicker, byline, dateline, Odia times, reading time, views, A-/A+, WhatsApp-first share + sticky mobile share bar, related, next-story infinite scroll, in-article ad), NewsArticle schema + OG/Twitter, author pages, CPTs, 30 districts, Breaking expiry, Quick Publish, Live Updates, e-Paper uploader + viewer + date archive + protected PDF, roles, dashboard widget, ad manager, forms, dark mode, demo import, translation .pot files.

**Next phase:** e-Paper *Clip & Share*, PDF flipbook view, dedicated gallery template with lightbox, built-in poll/newsletter widgets, AMP template, block-editor homepage blocks.

**Note on e-Paper privacy:** with "logged-in only", the viewer and PDF are protected, but image files in `/wp-content/uploads/` remain reachable if someone has the exact URL. For strict paywalls, also block direct access to uploads at the server level (ask your host).
