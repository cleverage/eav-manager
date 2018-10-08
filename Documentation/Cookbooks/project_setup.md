## Checkout the starter kit

The best way to kickstart a project is to use the
[cleverage/eav-manager-starter-kit](https://github.com/cleverage/eav-manager-starter-kit) as a template.

````bash
$ git clone https://github.com/cleverage/eav-manager-starter-kit.git my-project
$ cd my-project
$ rm -rf .git
````

You can also download the zip from Github.

With a standard dev environment using Docker, it should be very easy to launch the project using the 
[readme of the starter kit](https://github.com/cleverage/eav-manager-starter-kit).

Feel free to ditch the Docker setup completely and replace it with your own.

## Change the example namespace

You need to change several files right away to match your project namespace:
 - ````app/config/application/mailer.yml````
 - ````app/config/parameters/defaults.yml````
 - ````composer.json````
 
Rename everything under ````src```` that contains the ````ClientNamespace```` or ````client_```` references.
You can also reorganise your code completely to remove the bundle but keep in mind that you will need to configure
Doctrine for it to look in your custom ````Entity```` folder.

## Composer update

Don't hesitate to update your composer lock file, the Clever Data Manager will be locked to the current minor version so
it will always be safe to ````composer update````.

Beware that upgrading to another minor version can cause minor back-compatibility breaks in custom overrides but 
everything related to configuration should not introduce any new bugs.

## Organizing configuration

It's recommended to keep your configuration directory well organized because you will likely have more and more
configuration files in your project as it grows.

If you take a look at ````app/config/config.yml```` you will find all imports for every config sub-folders.

You can add additional sub-folders with this syntax:

````yaml
    - { resource: model/* }
    - { resource: model/*/* }
````

You should try to stick to only one configuration per file for the same reason you don't put multiple classes in the
same PHP file.

Standard Symfony configuration files can be found under the ````app/config/application```` folder.

## Declaring your model

Take a look at what's inside ````app/config/model````, this will give you a rough preview of what does an EAV model
configuration looks like.
The full documentation of the [EAVModelBundle can be found here](https://vincentchalnot.github.io/SidusEAVModelBundle)

Inside the ````app/config/datagrid```` you will find the Datagrid configuration corresponding to each family of your
model. If you want to use the automatic datagrid configuration resolving for your primary datagrid, you need to define
your datagrid configuration with the same code as your EAV Family but you can also create as many secondary datagrid
configurations as you need and specify the datagrid code in your list action.

@todo update admin config in starter kit
