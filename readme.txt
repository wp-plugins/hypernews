=== Hypernews ===
Contributors: EkAndreas 
Tags: news,rss,flow,monitor,publish,editorial,cms
Requires at least: 3.2
Tested up to: 3.3.1
Stable tag: 0.6

Editorial support, very fast user interface to manually select and publish RSS streams to your WordPress site/blog.

== Description ==

Editorial support, very fast user interface to manually select and publish RSS streams to your WordPress site/blog.

Add links with the Link-management, a sub menu under Hypernews.

Your Hypernews ui will now fetch and list your feeds. Keep track of which items is read and mark them as favorites or hide them. Or you can make a note on the item row.

If you want to edit them in Wordpress click on the post type links at each item row. The content will be saved as draft posts for later edit.

[youtube http://www.youtube.com/watch?v=eh_2ZQY3O0A] (old version of Hypernews)

Hypernews is tested with/without network installation and seems to work fine.

Primary language is Swedish so please support me with correct language updates if you find this plugin useful!

Please contact us at Twitter account @EkAndreas with questions and request of features!

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

Add RSS-source to Links.

Choose your settings in the Hypernews settings page.

Max Chars is how many chars to show in each item description.

"Publish to" is possible places to put the content. You can choose custom post types if you have any.

Now reload the entry page and start working with your RSS-streams as news to your site/blog.

Please contact us at Twitter account @EkAndreas with questions and request of features!

To use the data in the post metabox for developers: Post meta name is 'hypernews_metabox' with an array of data.

== Screenshots ==

1. Download the plugin as usual from the official WordPress repository. Activate the Hypernews plugin!
2. Reload the page at Hypernews start.
3. If you would like to save one RSS-item into a post type then click the link in the Published column at the row.
4. Your new news item will be saved in draft mode only.
5. Click the icon to get to the edit page or post.

== Changelog ==

= 0.6 =
* New look

= 0.5 = 
* Links moved from db to plugin-options in WP. All your Links will be blank after update! Please write them down before upgrade!
* MaxChars and RemoveChars moved to Links.
* Post types moved to Links
* Settings are removed and replaced by settings in each Link-setting.
* Browser Lists added (to manually open browser windows in case of missing RSS-feeds)
* Some text and menu updates

= 0.4 =
* Testing feeds in link dialog -function added
* If no Hypernews-metadata then hide metabox in edit page
* Sort column fixed
* Default sort changed to published descending
* Actions gathered in dropdown as standard, channel moved
* Checkbox to mass-mark hide items
* Fetching message with ajax image

= 0.3.7 =
* Title-url missing, now working!
* Metabox per post type with meta data, source and link
* Javascript removed
* Remove after... -setting added to remove text after n chars
* Strikethrough instead of yellow marker

= 0.3.6 =
* Trim on searchwords to void user errors

= 0.3.5 =
* Compare search word in UTF-8 for swedish chars

= 0.3.4 =
* Search words is now case insensitive

= 0.3.3 =
* SQL-statement corrected

= 0.3.2 =
* Just text-changes in readme and plugin-main, no functional changes.

= 0.3.1 =
* Minor database-sql correction

= 0.3 =
--NOTE THAT YOUR HYPERNEWS DATA WILL BE REPLACED!--
* Links moved into Hypernews
* Channels added to show only "my" items
* Searchwords added to every link to enable filter
* Fetching more smart and cache the result
* Shows a red bullet to the Hypernews entry for easier notice

= 0.2.5 =
* Fix to language files

= 0.2.4 =
* Max age added to settings, deletes rss-items older than nnn hours.
* Reload issue fixed.

= 0.2.3 =
* Fixed issue when using network wordpress site, settings.php renamed hypernews_settings.php

= 0.2.2 =
* Getting the localization files to work

= 0.2.1 =
* First beta version
