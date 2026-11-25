=== Restrictions for BuddyPress ===
Contributors: seventhqueen
Tags: buddypress, restrict content, membership, access control, pmpro
Requires at least: 5.0
Tested up to: 7.2
Stable tag: 1.5.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restrict BuddyPress profiles, groups, activity, and messages by login status, membership level, or profile field.

== Description ==

**Restrictions for BuddyPress** gives you full control over who can see and access your BuddyPress community pages. Hide profiles, groups, activity, and messages from visitors, non-members, or specific membership levels.

Perfect for **membership sites**, **private communities**, **paid networks**, and **gated BuddyPress installations**.

= What You Can Restrict =

* **Members Directory** — Hide the full members list from visitors or non-members
* **Member Profiles** — Control who can view individual BuddyPress profiles
* **Groups Directory** — Restrict access to the groups listing page
* **Single Group Pages** — Lock individual group pages behind membership
* **Site-Wide Activity** — Hide the activity stream from unauthorized users
* **Private Messages** — Restrict messaging to specific membership levels

= Membership Integration =

Seamlessly integrates with **Paid Memberships Pro (PMPro)**:

* Apply restrictions based on your existing membership levels
* Show upgrade prompts to non-members trying to access restricted content
* Mix and match — some areas free, some members-only

= Free Access by Profile Field =

Grant free access to specific BuddyPress members based on a **profile field value** — no membership plugin required. Ideal for:

* Giving VIP access to users with a specific role or profile attribute
* Creating tiered access without a full membership setup
* Whitelisting beta testers or early adopters

= BuddyBoss Compatible =

Fully compatible with **BuddyBoss Platform**, so your restrictions work whether you're running classic BuddyPress or BuddyBoss.

= How It Works =

1. Install and activate the plugin
2. Go to **Settings → BP Restrict**
3. Choose which BuddyPress areas to restrict
4. Select restriction type: visitors, all non-members, or specific membership levels
5. Set a custom redirect URL or restriction message
6. Done — your community is protected

= Use Cases =

* **Private communities** — Only logged-in members can browse profiles and groups
* **Paid membership sites** — Gate BuddyPress features behind PMPro membership levels
* **Corporate intranets** — Restrict the social layer to employees only
* **Online courses** — Let students access the community, hide it from everyone else
* **Dating sites** — Require membership to view profiles or send messages

== Installation ==

= From WordPress Dashboard =

1. Go to **Plugins → Add New**
2. Search for **"Restrictions for BuddyPress"**
3. Click **Install Now**, then **Activate**
4. Configure under **Settings → BP Restrict**

= Manual Upload =

1. Download `bp-restrict.zip`
2. Go to **Plugins → Add New → Upload Plugin**
3. Select the zip file and click **Install Now**
4. Activate and configure under **Settings → BP Restrict**

== Frequently Asked Questions ==

= Does this plugin work without Paid Memberships Pro? =

Yes! You can restrict BuddyPress areas based on login status (visitors vs. logged-in users) or based on BuddyPress profile field values — no membership plugin required. PMPro integration is optional.

= Does it work with BuddyBoss? =

Yes, Restrictions for BuddyPress is fully compatible with BuddyBoss Platform.

= Can I show a custom message to restricted users? =

Yes. You can set a custom restriction message or redirect users to a specific page (like a registration or upgrade page).

= Can I restrict only certain membership levels? =

Yes. With Paid Memberships Pro active, you can choose exactly which membership levels have access to each BuddyPress area.

= Will restricted pages still appear in menus and search? =

The plugin restricts access to BuddyPress pages. Menu visibility depends on your theme or menu settings. We recommend using a menu visibility plugin alongside this one for the best experience.

= Is it compatible with the latest WordPress and PHP versions? =

Yes. Tested up to WordPress 7.2 and requires PHP 7.4 or higher.

== Screenshots ==

1. General BuddyPress restriction settings — control access to profiles, groups, activity, and messages
2. Paid Memberships Pro integration — apply restrictions based on membership levels

== Changelog ==

= 1.5.6 =
* Compatibility: WordPress 7.2
* Fix: no PHP warning on sites where the restriction settings were never saved.

= 1.5.5 =
* **Security fix:** Fixed unauthenticated PHP code injection via the request URI in restriction condition evaluation. Request data is no longer part of evaluated code; logged-in-only restrictions are skipped for guests.

= 1.5.4 =
* Fix PHP 8+ fatal caused by Redux extension property type mismatch with BuddyBoss theme Redux extensions

= 1.5.3 =
* **Security fix:** Updated bundled Redux Framework to v4.5.10, removing vulnerable Appsero tracking library (CVE-2025-12391)
* Updated "Tested up to" WordPress 6.9

= 1.5.2 =
* Fix Redux framework error

= 1.5.1 =
* Update Redux framework init call to work with older versions used in other plugins

= 1.5.0 =
* Update Redux framework. BuddyBoss compatibility.

= 1.4.1 =
* Fixed a PHP notice in latest versions

= 1.4.0 =
* Fixed "View message" restriction message showing even if you have access

= 1.3.0 =
* Updated options framework panel

= 1.2.0 =
* PMPro viewing messages restriction now generates a notice to upgrade account

= 1.1.1 =
* Extra checks added for free access with Paid Memberships Pro

= 1.1 =
* Added the ability to give free access to BuddyPress members based on specific profile field and value

= 1.0.1 =
* Redirect BuddyPress new message to messages page

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.5.6 =
WordPress 7.2 compatibility and a PHP warning fix.

= 1.5.5 =
Critical security update — fixes an unauthenticated remote code execution vulnerability. Please update immediately.

= 1.5.4 =
Fixes a PHP 8+ fatal error with BuddyBoss theme Redux extensions.

= 1.5.3 =
Security update — addresses CVE-2025-12391. Please update immediately.
