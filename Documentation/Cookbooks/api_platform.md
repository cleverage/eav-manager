ApiPlatform setup
#################

### Installation

Require Api Platform in your composer.json:

````yaml
{
    # ...
    "require": {
        # ...
        "api-platform/api-platform": "2.1.*"
    }
}
````

Add the bundle to your kernel:

````php
<?php
        $projectBundles = [
            // ...
            new ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
        ];
````

Add global configuration:

````yaml
api_platform:
    # Do this if you want keep your api resources under a custom folder
    # This is optional but we use this setup in this guide
    mapping:
        paths:
            - '%kernel.root_dir%/config/api'
            
    # You can setup many more configuration options
    # https://api-platform.com/docs/core/configuration/
````

The best way to expose EAV entities as Api Platform resources is to have a specific PHP class for each EAV family you
want to expose. Check this cookbook for how to do this: 
[SidusEAVModelBundle: Custom classes](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/12-custom_classes.html)

Configure your entities, with a different file for each entity under the ````app/config/api```` directory:

````yaml
# app/config/api/Author.yml
resources:
    # Use your custom PHP data classes
    App\Entity\Author: ~
````

### Filtering

@todo Document usage of ApiPlatform/EAV/Filters.
See CleverAge\EAVApiPlatformBundle\Filter\* classes.

Everything else is completely standard ApiPlatform configuration, there is nothing specific to the EAV model.
