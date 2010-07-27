=== oEmbed for BuddyPress ===
Contributors: r-a-y
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KU38JAZ2DW8TW
Tags: buddypress, oembed, embed
Requires at least: WP 2.9 / WPMU 2.9.1.1 & BuddyPress 1.2.1
Tested up to: WP 3.0 & BuddyPress 1.2.5.2
Stable tag: 0.52

The easiest way to share your favorite content from sites like YouTube, Flickr, Hulu and more on your BuddyPress network. 

== Description ==

The easiest way to share your favorite content from sites like YouTube, Flickr, Hulu and more on your BuddyPress network.

oEmbed for BuddyPress utilizes [Wordpress' own oEmbed class](http://codex.wordpress.org/Embeds), so by default, you can share content from the following sites:

* YouTube
* Blip.tv
* Vimeo
* DailyMotion
* Flickr
* Smugmug
* Hulu
* Viddler
* Qik
* Revision3
* Photobucket
* Scribd
* Wordpress.tv
* PollDaddy
* FunnyOrDie

How do you use the plugin?  Simple!  Input **any** URL from one of the listed sites above into an activity update or forum post in BuddyPress.

When the update is posted, the URL automagically transforms into the embedded content.


== Installation ==

1. Download, install and activate the plugin.
1. That's it!
1. Read the FAQ for other configuration settings.


== Frequently Asked Questions ==

#### What is oEmbed ####

[oEmbed](http://www.oembed.com/) is a simple API that allows a website to display embedded content (such as photos or videos) when a user posts a link to that resource.  It was designed to avoid copying and pasting HTML from the media you wish to embed.


#### So what does this plugin do?" ####

This plugin utilizes [Wordpress' own oEmbed class](http://codex.wordpress.org/Embeds) allowing you to input **any** URL from one of the default-supported sites into an activity update or forum post in BuddyPress.

When the update is posted, the URL automagically transforms into the embedded content.  There is no GUI.


#### How do I extend support for other websites? ####

You can use *any* Wordpress plugin that extends support for oEmbed or Wordpress' embed providers list.

In particular, check out:
* [Embedly](http://wordpress.org/extend/plugins/embedly/) - add support for [106 sites](http://api.embed.ly/) (and counting)
* [oohEmbed](http://wordpress.org/extend/plugins/oohembed/) - add support for [29 sites](http://oohembed.com/#configuration)
* Embeddable! - my own plugin to add support to various sites and file extensions (coming soon)

With a bit of code and PHP knowledge, you can also manually add an embed provider yourself!  For more information, read the following articles:
http://codex.wordpress.org/Embeds#Adding_Support_For_An_oEmbed-Enabled_Site
http://codex.wordpress.org/Embeds#Adding_Support_For_A_Non-oEmbed_Site

I get many requests to add support for "so-and-so" website.  Including all the plugins I have released and the many hours I have spent on support and research, only six people have made a donation (that doesn't even equate to 1% of total downloads!).

For this reason, I am **not** accepting requests to add support for other websites.  However, if you donate towards the plugin, I will consider and implement your request.  If you are planning on donating for this sole purpose, please [contact me](http://buddypress.org/community/members/r-a-y) on the BuddyPress forums *before* you do!


#### Where's the admin settings page? ####

oEmbed for BuddyPress works transparently in the background.

There is no admin settings page.  This is intentional (at least for now).  All settings can be defined in wp-config.php

By default, the plugin allows embedding in activity updates, activity replies, and forum posts.

Group descriptions and profile fields are disabled by default.


#### Then how do I enable / disable oEmbed for certain BuddyPress components? ####

As of v0.6, the following lines can be added to wp-config.php to disable / enable oEmbed components:

*Turn off* oEmbed for the following components:

`define( 'BP_OEMBED_DISABLE_ACTIVITY', true );`

`define( 'BP_OEMBED_DISABLE_ACTIVITY_REPLIES', true );`

`define( 'BP_OEMBED_DISABLE_FORUM_POSTS', true );`


*Turn on* oEmbed for the following components:

`define( 'BP_OEMBED_DISABLE_GROUP_DESCRIPTION', false );`

`define( 'BP_OEMBED_DISABLE_XPROFILE', false );`


== Internal configuration ==

= Resizing embed items =

Instead of using the default embed width and height given by oEmbed, you can manually set the width for certain components.

Add the following lines in wp-config.php and adjust to your liking:

`define( 'BP_OEMBED_ACTIVITY_STREAM_WIDTH', '200' );`
	
`define( 'BP_OEMBED_ACTIVITY_PERMALINK_WIDTH', '400' );`
	
`define( 'BP_OEMBED_FORUM_POST_WIDTH', '300' );`

`define( 'BP_OEMBED_GROUP_DESC_WIDTH', '200' );`

`define( 'BP_OEMBED_XPROFILE_WIDTH', '200' );`


= Whitelist carat =

If you prefix the "^" character in front of a link, the URL will not be parsed by the plugin.

eg. `^http://www.youtube.com/watch?v=Lz1EOYtxUu0` would return as a hyperlink and not the embedded content.

You can change the "^" character to anything you want by adding the following line to wp-config.php:

`define( 'BP_OEMBED_WHITELIST_CARAT', '%' );`


= Filters =

For theme and plugin developers, there are a bunch of filters you can use to override certain content.

* **bp_oembed_whitelist_url** - used to output links when the "^" character is prefixed

* **embed_oembed_html** - used for oEmbed content

* **embed_handler_html** - used for non-oEmbed content

Look at the source code for more details.


= Blacklist =

This is a legacy feature from v0.5 that allows you to blacklist certain websites from being parsed by the plugin.  I'm leaving this feature in as it's an easy way to block sites you may not want your userbase to use.  View bp-oembed-legacy.php for more details.


== Known issues ==

* Hyperlinks with single quotes get mangled (if you're a regex expert, I could use your help!)

eg. `<a href='http://buddypress.org'>BuddyPress</a>`

* Hyperlinking an embeddable link and inputting the same link in plain text will show the embedded item three times (two times if using anchor text) (not many people will do this)

* Using a custom resize option for forum posts and group description will conflict with one another when on a group forum topic.  Forum post size will take precedence when this occurs.


== Special thanks ==

* [Viper007Bond](http://www.viper007bond.com/) - for creating the WP_oEmbed class
* [BuddyPress.org](http://buddypress.org) - the reason why we can all deploy a social network on Wordpress


== Donate! ==

I'm a regular on the buddypress.org forums.  I spend a lot of my free time helping people - pro bono!

There are a couple of ways you can choose to support me:

* [Fund my work soundtrack!](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KU38JAZ2DW8TW)  Music helps me help you!  A dollar lets me buy a new tune off Amazon MP3, Amie Street or emusic.com!  Or if you're feeling generous, you can help me buy a whole CD!  If you choose to donate, let me know what songs or which CD you want me to listen to! :)
* Rate this plugin
* Spread the gospel of BuddyPress


== Changelog ==

= 0.6 =
* Changed oEmbed caching logic for performance reasons (thanks apeatling)
* Added support for WP non-oEmbed handlers (more info can be found [here](http://codex.wordpress.org/Function_Reference/wp_embed_register_handler))
* Added partial support for resizing embed items based on component (more info can be found in the "Other Notes" section)
* Added partial support for group descriptions and profile fields (disabled by default)
* Added support to whitelist an embed link so it stays as a link (more info can be found in the "Other Notes" section)
* Added support to remove oEmbed cache when a forum post is deleted
* Added a CSS class around embedded item for theme designers
* Added transparent background to flash content by default
* Fixed fatal error if the activity, forum or group components were disabled (thanks dcservices for reporting)
* Fixed AJAX bug where if an embed link returned javascript, the activity stream on an AJAX request would break; now returns URL on AJAX request if content is javascript (requires server to support HTTP_X_REQUESTED_WITH)
* Fixed embed bug where both an @mention + an embed link was used in the same update, the embed link would break (thanks sicksight for reporting)

= 0.52 =
* No more plugin folder renaming! (thanks apeatling)

= 0.51 =
* Added rename plugin folder instructions (*IMPORTANT*)
* Fixed "cannot modify header information" bug (thanks geoffm33 for reporting)
* Moved default, whitelist items out of config to plugin base
* Added BuddyPress domain to whitelist
* Added instructions to extend oEmbed provider list to readme.txt

= 0.5 =
* First version!