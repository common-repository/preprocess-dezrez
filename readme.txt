=== Process Dezrezone XML for upload ===
Contributors: Fullworks
Tags: dezrez
Requires at least: 3.6
Requires PHP: 5.6
Tested up to: 4.8.2
Stable tag: 1.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A pre processor for the DezrezOne XML API to enable easy import into WordPress.

== Description ==

**This plugin is no longer supported and shortly will request removal from the WP repo**

This plugin is designed to interface to the DezrezOne XML API. You will need a valid API key and Agent ID. The plugin will setup an hourly WP Cron job.

The plugin builds a single XML file from multiple calls to the Dezrezone API, that you can use then to upload to your custom property posts, using an XML uploader of your choice.

The plugin should auto-recover from a problem ( e.g. timeout at Dezrez or your server ) as it will reconcile the cache of properties and rebuild any missing ones.

To get a working solution a degree of technical understanding is expected.

The Pro version is designed to work seamlessly with WP All Import to give you fully automated uploads to your Estate Agency Website.

== Installation ==

This section describes how to install the plugin and get it working.

**Through Dashboard**

1. Log in to your WordPress admin panel and go to Plugins -> Add New
1. Type widget for preprocess-dezrez in the search box and click on search button.
1. Find the plugin.
1. Then click on Install Now after that activate the plugin.

**Installing Via FTP**

1. Download the plugin to your hardisk.
2. Unzip.
3. Upload the preprocess-dezrez folder into your plugins directory.
4. Log in to your WordPress admin panel and click the Plugins menu.
5. Then activate the plugin.

** Setup **

1. visit the settings page and add your DezrezOne API key and agent ID


== Frequently Asked Questions ==

= I installed the plugin but nothing happens =

Have you set up a server cron job? See installation instructions. If you have check the cron logs. Additionally you can turn on WordPres debugging
e.g.
`define('WP_DEBUG', true);
 define('WP_DEBUG_LOG', true);`
to get more detailed logs

= It appears very slow =

The speed is dependant on the speed of Dezrezone servers. If you have lots of properties it can take a while for the first load or two,
 in testing we found around 30 seconds per property is normal, but once the local cache is built it will take much less time, as only updated properties will be processed.

= Not all my properties downloaded =

If your cron job fails, e.g. a timeout at Dezrez or a timeout on your server, the job will not complete.
You can check what properties a have been downloaded by looking at the files in `path_to_content/uploads/preprocess-dezrez/cache`.

When your cron job runs next time, any files missing from cache will automatically be processed, which means that you should get all your properties, even though it may take several attempts initially if you are loading many properties and images.

= I need to force a property to be re-downloaded =

Simply delete the relevant file(s) in `path_to_content/uploads/preprocess-dezrez/cache` and the next run will re-process them.

= Where do I find the XML file to upload =

The file will be called `path_to_content/uploads/preprocess-dezrez/upload.xml`

= What do I do with the XML file when I have got it =

That is out of scope of this plugin.  There are XML to WordPress plugins to help you with that. If you need help with this consider the Pro - Business plan.

= I need help or support setting this up =
Consider one of our paid plans, install the free plugin and upgrade from the dashboard.


== Pro Plans ==

The Pro version of this plugin come with many additional features that allow you to make processing properties from DezrezOne seamless, with tight integration to WP All Import.

There area variety of plans depending on your needs, if you are an experienced developer the the Developer plan is for you. If you area business and need more help, there is the Business plan.

Upgrading is easy, there are upgrade options within your settings pages.

== Changelog ==
= 1.1.2 =
* security patch

= 1.1.1 =
* bug fix reset notification

= 1.1 =
* handle images correctly
* WP Cron processing

= 1.0 =
* First release


