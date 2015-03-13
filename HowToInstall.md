## Summary ##

  * Requirements: software and other requirements
  * Quick guide: with few info, for advanced users
  * Explained guide: step by step on how to install and setup a web server and MyImouto
  * Troubleshooting: some errors you might see


---


## Requirements ##

  * **MyImouto requires PHP 5.4+**. It was developed under PHP 5.4.7 and MySQL v5.5.27.
  * MyImouto **can't be ran under a subfolder** (e.g. `http://myhost.com/myimouto`). It must be in the root (`http://myimouto.com`), a subdomain (`http://booru.myhost.com`), or a port (`http://myhost.com:3000`). The "explained guide" shows how to run MyImouto under a port.
  * If running under Apache, the Rewrite mod must be activated. Also, to serve gzipped assets (css and js files), the Headers mod is needed.
  * Must have PHP libraries are GD2 (for image processing), PDO (database) and cURL (for both Image search and Search external data features).
  * Recommended libraries are Imagick and Memcached.
  * A **date.timezone** must be defined in the **php.ini**. The system will set Europe/Berlin as timezone by default, more info on this in the HowToUse wiki.


---


## Quick guide (advanced) ##

  * Download the source.
  * Extract the _myimouto_ folder at your desired location.
  * Point the document root of your web server to the /public folder.
  * Rename config/config.php.example and config/database.yml.example, remove the ".example" part.
  * Set your database configuration in _config/database.yml_.
  * Set your MyImouto configuration in _config/config.php_ (read the _config/default\_config.php_ file to see the available options). For the system for work correctly only the **server\_host** and **url\_base** options are the most important.
  * Create the database for the booru.
  * If you're not accessing the site locally, list the IP address you'll connect from in the 'safe\_ips' array in _install/config.php_.
  * Go to your site to complete the installation.
  * Delete the install folder.
  * If you have problems, read the Troubleshooting section [here](#Troubleshooting.md) or report it in the issues section.


---


## Explained guide ##

### Basics ###

What you need:
  * The MyImouto source.
  * A web server. This guide uses <a href='http://www.apachefriends.org/en/xampp.html'>XAMPP</a>, with which you can use MyImouto locally.

Download and install XAMPP in your desired directory. Once installation is done, open the XAMPP control panel and make sure Apache and MySql are running, then go to http://127.0.0.1/, and if the page loads, XAMPP was installed successfuly.

If you'd like XAMPP to start automatically, check the Svc box for both Apache and MySql in the control panel.

Now extract the booru source in your desired directory, anywhere where you want it to stay.

### Configurations ###

Rename both config/config.php.example and config/database.yml.example files, by removing the ".example" part.

**Database**

Open config/database.yml and set your database username and a password. By default, XAMPP sets "root" as username and nothing as password.

**System**

Now you need to list the IP address you're connecting from in _install/config.php_ under the "safe\_ips" array (read Troubleshooting for more info), according to the following:

  * If you're connecting locally (same computer where server is), you don't have to do anything.
  * If you're connecting through LAN, list your local IP (usually 192.168.1.XXX). To check your local IP, on Windows run cmd and type `ipconfig`, on Linux type `ifconfig` in console.
  * If you're connecting remotely you need to list your public IP, to know it google "my ip".

**Booru**

The configuration regarding the booru is in the _config/default\_config.php_ file. For convenience, you are requested to customize it in the _config/config.php_ file, instead of directly modifying the default\_config file.
The most imporant settings are _server\_host_ and _url\_base_. Edit them reflecting your settings.

### Php.ini ###

You now need to do some edits to PHP's configuration file, located in _xampp/php/php.ini_.

These are the recommended minimum values for some directives. So open php.ini, search for these directives and edit them:

  * _upload\_max\_filesize_ = 5M - Max filesize PHP will accept on uploaded files.
  * _post\_max\_size_ = 6M - Max POST requests size. This should be a little bigger than _upload\_max\_filesize_.
  * _memory\_limit_ = 128M - The bigger images you'll accept, the more memory they'll need to be processed.
  * _date.timezone_, make sure it has a value. You can go [here](http://www.php.net/manual/en/timezones.php) to check for your timezone.
  * Make sure the following PHP extensions are enabled by removing the leading semicolon, if any:
    * extension=php\_fileinfo.dll
    * extension=php\_pdo\_mysql.dll
    * extension=php\_curl.dll

### Server configuration ###

MyImouto's document root isn't just the myimouto folder, but it's the myimouto/public folder, so when you go to `http://yourbooru.com/` your web server will load the index.php file that is inside the myimouto/public folder. This also means that you aren't supposed to access the system by going to `http://yourbooru.com/public`.

There are many ways to achieve this. A rather simple one is to run the site under a port.

Let's enable port 3000. To enable it on Apache go to _xampp/apache/conf_ and open the _httpd.conf_ file. Look for the "Listen 80" line, and below it enter "Listen 3000".

We have to let Apache know about the directory where we put the system. In the same file, around line 220, you will see this:

```
#
# This should be changed to whatever you set DocumentRoot to.
#
<Directory "X:/xampp/htdocs">
  ...
</Directory>
```

Where "X" is the drive where you installed Xampp.
Below `</Directory>` enter this:

```
<Directory "drive:/path/to/myimouto">
    AllowOverride All
    Require all granted
</Directory>
```

Change the Directory path according to your settings.

Now we will point the port we opened before to the myimouto/public folder. Go to _xampp/apache/conf/extra_ and open the _httpd-vhosts.conf_ file and enter these lines:

```
<VirtualHost *:3000>
  DocumentRoot "drive:/path/to/myimouto/public"
</VirtualHost>
```

DocumentRoot is the same path as above except that you add the `/public` directory.

Now restart Apache so these and PHP changes take effect.

### Database creation ###

Create a database named according to your settings. You can do so through PHPMyAdmin (http://127.0.0.1/phpmyadmin). Preferably use utf8\_general\_ci as collation.

Once the database is created, you can finally go to http://127.0.0.1:3000 (or your chosen URL) to complete the installation. After installation is completed, you may delete the install folder.


---


### Install updates and fixes ###

Check the downloads section from time to time for updates for the system.

Also check the Issues section for errors found in the current release, and the fixed files in some cases.


---


### Troubleshooting ###

#### Access denied for xxx.xxx.xxx.xxx ####

If this is all you see when you go to your booru to complete the installation, the problem is that the IP address you're connecting from isn't listed under the allowed IP addresses.

You simply need to allow the IP address you see in the notice. Go to _install/config.php_ and look for:

```
  'safe_ips' = [
    '127.0.0.1',
    '::1'
  ]
```

Enter the IP address you got in the notice in a new line, like this (notice the new comma after "::1"):

```
  'safe_ips' = [
    '127.0.0.1',
    '::1',
    'xxx.xxx.xxx.xxx'
  ]
```

Now refresh the page.

#### Parse error: syntax error, unexpected '[' in ... ####

If you see this error, it means you're running PHP 5.3 or lower. You have to upgrade to PHP 5.4 or higher.