## Configuring MyImouto ##

There are 2 types of configuration: The booru's configuration and the framework's configuration.

### Booru's configuration ###

The configuration resides in the file located at config/default\_config.php. To know all the possible options available in this booru, you have to read that file. However, if you want to customize the configuration, you are requested to do so in the config/config.php file. **You don't have to touch the default\_config.php file except for reading**.

By setting your custom configuration in config.php you will make your configuration portable for future updates which might come with an edited default\_config.php file.

You will find MyImouto-specific options after Moebooru's configuration.

### Framework's configuration ###

This is found in the config/application.php file. There's not much to do here, except for a couple of options:

#### Date.Timezone ####

As the installation guide says, you are requested to set a value for date.timezone in your php.ini file, because PHP will trigger an error if you don't, and this will cause malfunction in the system. However, if you aren't able to edit the php.ini file, the framework can set a timezone for you.

In the mentioned file, you will see this by the end:

```
$config->date->timezone = 'Europe/Berlin';
```

The framework will automatically set the timezone to Europe/Berlin. I doubt that's your timezone, so you will want to change it to your actual timezone (check the available timezones [here](http://www.php.net/manual/en/timezones.php)).

If you did set a timezone in your php.ini, you can safely remove that line.

#### Enabling Memcached ####

You can ask the framework to use Memcached for storing cache, instead of using the filesystem. Open config/application.php, and enter this in the initConfig($config) method:

```
$config->cache_store = 'mem_cached_store';
```

That will stablish a connection to localhost:11211, which are the default settings. If you need to specify the server and/or port:

```
$config->cache_store = [ 'mem_cached_store', ['some.server', 4456] ];
```


---


## Assets ##

As of version 1.0.3, a basic Asset system was introduced to the framework. This will make customizing Moebooru's original CSS and Javascript an easier task.

A general guide on how to use the Asset system can be found [here](http://code.google.com/p/php-on-rails/wiki/TheAssetsSystem).

If you want to add your own assets (to customize CSS for example), read the guide below to avoid getting your changes replaced in future patches for the booru. Every patch that comes with new base assets will delete the default assets.

### Customizing assets ###

There are two ways to do this. One is by _changing_ the default assets to your customized ones, and the other is to _add_ your assets. This small guide will cover the second method.

In order to add them easily and keep your config between upgrades, a couple of options were added to MyImouto's config (config/config.php) under the name of _asset\_javascripts_ and _asset\_stylesheets_. If you check them, you can see they're arrays containing the default assets manifest files, 2 javascripts and 1 stylsheet. You can add your own manifest files to the list.

Let's say you want to add a new stylsheet named "my\_css":

You need to create and compress this file first. For convenience, you'd like to have a folder where you can place your custom assets, to avoid the folders used by the system. Let's place them in `lib/custom_assets`. So create the folder, then declare the new assets directory in your application's config:

```
# config/application.php
...
    protected function initConfig($config)
    {
        ...
        
        // Enter the new custom directory, notice the backslash leading Rails
        $config->assets->paths = [ \Rails::root() . '/lib/custom_assets' ];
    }
...
```

Now create the manifest file "my\_css.css" inside your custom folder, add everything you want to it, then compress it (you can find out how to do so in the assets guide linked above).

Next, add the new asset in your booru configuration file:

```
# config/config.php
class LocalConfig extends DefaultConfig
{
    ...
    
    public $asset_stylesheets = [
        'application',
        'my_css'
    ];
}
```

Your new custom CSS will be used from now on.