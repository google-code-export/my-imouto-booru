## Summary ##

  * [Introduction to MyImouto](#Introduction.md)
  * [MyImouto features](#MyImouto_features.md)
    * [Import](#Import.md)
    * [Automatic tags and source](#Automatic_tags_and_source.md)
    * [External posts data search](#External_posts_data_search.md)
    * [Image search](#Image_search_(Moebooru).md)
    * [Job tasks](#Job_tasks_(Moebooru).md)
    * [Notes](#Notes.md)
    * [Browse mode](#Browse_mode_(Moebooru).md)
  * [Other features](#Other_features.md)
    * [Header menu links](#Header_menu_links.md)
    * [Tag list in /post left sidebar behaviour](#Tag_list_in_/post_left_sidebar_behaviour.md)
    * [The framework](#The_framework.md)


---


## Introduction ##

MyImouto is a port to PHP of Moebooru (which is a Danbooru 1 fork), the system used in [Yande.re](https://yande.re). MyImouto uses a custom framework that is based on Ruby on Rails, so this system could actually be Moebooru for PHP: you can copy and paste code from Moebooru, convert it to PHP (with some little modification of course) and it'll work.

So my work here is transcribing the system logic from Moebooru to PHP, and the framework. HTML, CSS, JS, etc. was taken from Moebooru.

Please note that new updates are made to the system from time to time, some of them bring huge changes to the core. So if you're editing the code, keep in mind your changes may be invalid in a future version.

Some features from original Moebooru are still missing, such as:

  * IP bans don't support IP CIDR
  * Post frames
  * Local image search


---


# MyImouto features #

MyImouto brings some changes and custom features.

Relevant-ish features:
  * Supports GIF images.
  * **`*`**Added an option in /pool/show to keep pretty filenames for the zip files, instead of pool order filenames.
  * **`*`**Added an option to delete tags manually in the Tags index page.
  * Changed artists index: only artists are listed, and their aliases, if any, now appear in the Aliases column. To list aliases, search for "aliases\_only".
  * **`*`**You can press the E/R keys in /post/show to jump directly to the edit/reply forms.
  * Added **Fix tag count** option in Admin controller, to fix tags count manually.
  * Added **Purge tags** option in Admin controller, to manually delete all tags with count of 0. This and "fix tag count" options are useful if you're not running Job Tasks.
  * Note formatting is done using Markdown.
  * Improved notes (translations) interactivity. More info below.
  * **`*`**Posts tags and source can be taken automatically from file names, see below.
  * Added **Import** option under the Posts menu, for admins only. See below.
  * You can search for data from other servers with the Search external data feature. See below.

Other features/changes:
  * Added a different color to the "directlink" bar for blacklisted posts, so it's easier to know which posts are the blacklisted ones when you unhide them.
  * Clicking on the "Respond" link in /post/show will additionally focus the reply textarea.
  * **`*`**Small-sized posts with no sample can be zoomed-in in Browse mode.
  * Privileged members can be invited to become Contributors.

**`*`**_Can be enabled/disabled in the configuration file._

For the full list of options, read the _/config/default\_config.php_ file, the "`MyImouto-specific configuration`" part.

## Import ##

If you need to upload a large number of images, you can mass-create posts with Import. Import will process all the images you place inside the _/public/data/import_ folder.

You can automatically add all the posts you'll import to a pool by selecting a pool from the list (only active pools will appear) or by entering a name to create a new one in the Pool text field.

Import also supports **mass-tagging**. For example, place all files that you want tagged as "rozen\_maiden" inside a folder called like that, and they will be automatically tagged. Subfolders are also supported (for instance: /public/data/import/rozen\_maiden/hina\_ichigo/).

You can even put many tags in one folder name, separating them by spaces (e.g. import/rozen\_maiden hina\_ichigo/) and the posts inside will be tagged with all the tags.

## Automatic tags and source ##

Tags, as well as source, can be taken off the file names, when uploading and importing. By default, all images from moe|oreno|yande.re are automatically detected. The code to get the tags and source are in app/models/post/filename\_parsing\_methods.php; you can edit it to detect any kind of filename you want.

## External posts data search ##

You can search for data for a post from other servers and update your database with such information. Mods and admins have the ability to use this feature.

To use it, view any post and click on the "Search external data" link, under the Options menu. You'll be linked to the page where the data will be searched.

Wait for the data to be found and a list of results will appear. You'll have the following features:

  * Compare different data from other servers with yours (tags, source, dimensions and file size, etc).
  * Results with data that differ from your current post data will appear in red.
  * You will have a form to update your post data right there.
  * In results, clicking on a tag name will add it to your Tags textarea. Click on the "add all" link to add all tags of that result.
  * Likewise, clicking on a results' Source will change your post's Source text field.
  * Click on the thumbnail of the result to go to its page (opens a new tab).
  * You can merge tags from all results by clicking on the "Merge all tags" button.
  * To prevent tags from a result to be added with the "Merge all tags" button, click on the result's header (where the icon and name of the server are) to hide it. Hidden results' tags won't be added.
  * Click the "Update" button to update your post with the new data.
  * For results found on Yande.re, if a post has PNG version it will state so. In this case, the filesize you'll see is the PNG version's.

This feature uses the /post/similar feature to grab the data. Please read below to know how it works.

## Image search (Moebooru) ##

Image search allows users to search for an image in other image boards. It uses on the image search server [iqdb.org](http://iqdb.org), so the image boards you can search in depends on iqdb.org. It supports a good number of image boards though.

By default, MyImouto is configured to search in Danbooru, Yande.re and Konachan. Read on to find out how to add more image boards.

#### How to add image boards ####

You simply need to go to iqdb.org to check the address of the image board you want to add (e.g. zerochan.net, gelbooru.com) and add it to your config file as the "image\_service\_list" option. See the following example:

```
# This is the default value found in /config/default_config.php:

public $image_service_list = [
    "danbooru.donmai.us" => "http://www.iqdb.org/index.xml",
    "yande.re"           => "http://www.iqdb.org/index.xml",
    "konachan.com"       => "http://www.iqdb.org/index.xml"
];


# In /config/config.php, first copy/paste the default list (if you want to keep it),
# then add any new image board you want.
# Let's add Gelbooru and Zerochan to our list:

class Moebooru_Config extends Moebooru_DefaultConfig
{
    # ...
    # Here you might have customized other options
    # ...
    
    public $image_service_list = [
        "danbooru.donmai.us" => "http://www.iqdb.org/index.xml",
        "yande.re"           => "http://www.iqdb.org/index.xml",
        "konachan.com"       => "http://www.iqdb.org/index.xml",
        "gelbooru.com"       => "http://www.iqdb.org/index.xml",
        "zerochan.net"       => "http://www.iqdb.org/index.xml"
    ];
}
```

This way we added Gelbooru and Zerochan to our image boards list.

Additionally, you will want to grab the Favicon from the image boards you added and place them inside your /public folder under the name of "favicon-[imgboard-url.com].ico". For example, the favicon for Zerochan will have to be named "favicon-zerochan.net.ico". The favicon for Gelbooru comes with Moebooru, thus with MyImouto as well, and it's already inside the /public folder.

Image search should also search among the local server's images (local image search), but this isn't supported for now.

## Job tasks (Moebooru) ##

Danbooru/Moebooru have the ability to run heavy or time-consuming tasks in background, such as maintenance tasks, run batch uploads, etc. These tasks are ran and report their status, which can be checked by going to /job\_task. MyImouto also supports Job tasks.

### How they work ###

The code that is executed for each task is a method in the JobTask model. Some tasks need to store data, hence each task also have a row in the `job_tasks` table.

The scripts that run the job tasks are in /script/daemons. These scripts initialize the system and run the tasks, and depending on them, all or only some tasks will be ran.

With MyImouto you have to set a cron (Linux) or a schedule task (Windows) to call PHP with the -f parameter to run the script that will run the job tasks, like this:

```
php -f "/path/to/myimouto/script/daemons/job_task_processor.php"
C:/xampp/php/php.exe -f "E:/path/to/myimouto/script/daemons/job_task_processor.php"
```

I'd say setting it to run every 30 minutes is enough.

Remember that you can decide which tasks will be executed by listing them in the **$active\_job\_tasks** option in the config.

## Notes ##

Post notes, or translations, were improved (as asked in [issue 103](https://code.google.com/p/my-imouto-booru/issues/detail?id=103)). The old way to create notes was by clicking the "Add translation" link in the Options panel when viewing a post, then dragging and resizing the note, then click on it, add the text and then click the `Save` button. This is shortened now:

  * Creating a note can be done by `Shift+Click+Drag` over the image, so the note is created at the desired location, with the desired size.
  * You can start typing the text for the note right away after creating it, as the textarea for the text will appear automatically.
  * Press `Ctrl+Enter` to save the note, instead of clicking the `Save` button.
  * Press the `Escape` key to cancel any edits, instead of clicking the `Cancel` button.

So now, clicking the "Add translation" link will now show a notice with info on how to create the note. You can make the link to create a note instead, like before, by setting the _disable\_old\_note\_creation_ option to `false` in your configuration. Note, however, that the new functionality will still be available.

### Markdown for Notes ###

As of v1.0.6, instead of allowing HTML like in the original version, Notes in MyImouto use [Markdown](http://michelf.ca/projects/php-markdown/concepts/) for formatting. However, Danbooru's `<td>` tag can still be used.

## Browse mode (Moebooru) ##

Browse mode (or Post browser) is one of the coolest features in Moebooru. You can access it through /post/browse (if nothing shows, press Enter twice in the bottom text field to show posts).

You can browse posts in a cooler way with this, and you got all options for posts there (edit, flag, delete, etc).

Users can configure in their panel to view posts with the Post browser, instead of normal browsing through /post/show.

For more information on Post browser: https://yande.re/forum/show/12748


---


## Other features ##

### Header menu links ###

The header menu links are actually drop down menus. Hold left click on any of the links and drag down the mouse, or click on the small square and a dropdown menu will appear with extra links.

### Tag list in /post left sidebar behaviour ###

The tag list in the sidebar in the posts index only shows tags of types circle, copyright, artist and character of posts that were uploaded in the last 24 hours by default (this can be configured in config/default\_config.php > $post\_index\_tags\_limit).
If no posts were created within the time limit, or any of those posts don't contain any of the mentioned type of tags, the sidebar will show empty. If you suddenly see your sidebar is empty, this may be the reason.

### The framework ###

The framework MyImouto uses tries as much as it is able to, to work like Ruby on Rails 3. It's pretty cool and it works nicely, and can be used to create other websites.

However, since it was created by me, the framework might not be prepared for all kind of circumstances a website could go through. It seems to be stable enough though.