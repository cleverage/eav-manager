
## Installation
Yes it's complicated, but it's also very versatile and allows you to do it your way.

### Create the EAVModelBundle
Create a dedicated bundle for your EAV model classes (or put them in any of your bundles).
If you want to use the ```generate:bundle``` command you have to do this first.

#### Create your Data Doctrine entity
Don't forget to change the "MyNameSpace" part with your own.
The table name can be changed without impacting any behavior of the system.

```php
<?php

namespace MyNameSpace\EAVModelBundle\Entity;

use CleverAge\EAVManager\EAVModelBundle\Entity\AbstractData;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="eav_data", indexes={
 *     @ORM\Index(name="family", columns={"family_code"}),
 *     @ORM\Index(name="updated_at", columns={"updated_at"}),
 *     @ORM\Index(name="created_at", columns={"created_at"})
 * })
 * @ORM\Entity(repositoryClass="CleverAge\EAVManager\EAVModelBundle\Entity\DataRepository")
 *
 * If you want to use single inheritance with your data class:
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
class Data extends AbstractData
{
}
```

#### Create your Value Doctrine entity
```php
<?php

namespace MyNameSpace\EAVModelBundle\Entity;

use CleverAge\EAVManager\EAVModelBundle\Entity\AbstractValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="eav_value", indexes={
 *     @ORM\Index(name="attribute", columns={"attribute_code"}),
 *     @ORM\Index(name="family", columns={"family_code"}),
 *     @ORM\Index(name="string_search", columns={"attribute_code", "string_value"}),
 *     @ORM\Index(name="int_search", columns={"attribute_code", "integer_value"}),
 *     @ORM\Index(name="bool_search", columns={"attribute_code", "bool_value"}),
 *     @ORM\Index(name="position", columns={"position"})
 * })
 * @ORM\Entity(repositoryClass="Sidus\EAVModelBundle\Entity\ValueRepository")
 */
class Value extends AbstractValue
{
}
```

### Update composer.json:
Install Assetic, the EAV Manager and merge the rest of the configuration:

```json
{
    "require": {
        "symfony/assetic-bundle": "^2.8",
        "cleverage/eav-manager": "~1.2.0"
    },
    "config": {
        "component-dir": "web/assets",
        "component-baseurl": "/assets"
    },
    "scripts": {
        "symfony-scripts": [
            "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
            "Sidus\\FileUploadBundle\\Composer\\ScriptHandler::symlinkJQueryFileUpload",
            "Mopa\\Bundle\\BootstrapBundle\\Composer\\ScriptHandler::postInstallSymlinkTwitterBootstrapSass",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::prepareDeploymentTarget",
            "php bin/console assetic:dump"
        ]
    }
}
```

### Update AppKernel.php
We propose the following code "architecture":

```php
<?php

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $symfonyBundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            // Not part of the standard distribution anymore:
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
        ];

        $eavBundles = CleverAge\EAVManager\EAVKernelBundleLoader::getBundles();

        $projectBundles = [
            MyNameSpace\EAVModelBundle\MyNameSpaceEAVModelBundle(), // Import you EAVModelBundle here
            // Append your project bundles here
        ];

        $devBundles = [];
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $devBundles = [
                new Symfony\Bundle\DebugBundle\DebugBundle(),
                new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
                new Sensio\Bundle\DistributionBundle\SensioDistributionBundle(),
                new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            ];
        }

        return array_merge($symfonyBundles, $eavBundles, $projectBundles, $devBundles);
    }
    // ...
}
```

### Import the base configuration
Import the base configuration (it's possible to override it afterward)
```yaml
imports:
    - { resource: '@CleverAgeEAVManagerAdminBundle/Resources/config/base_config.yml' }
```

And define your base Data & Value classes in parameters:
```yaml
parameters:
    sidus_data_class: MyNameSpace\EAVModelBundle\Entity\Data
    sidus_value_class: MyNameSpace\EAVModelBundle\Entity\Value
```
These parameters are used inside the base_config.yml to point to your own classes

Finally, configure the bundles:
```yaml
sidus_eav_model:
    data_class: '%sidus_data_class%'
    value_class: '%sidus_value_class%'
    serializer_enabled: true # Only if you want to use Symfony's serializer (strongly advised)

clever_age_eav_manager_user:
    mailer:
        company: My Company
        from_email: no-reply@my-company.org
        from_name: My Company (do not reply)
```

### Configure the Firewall and access to the EAV Manager

Replace the content of your security.yml with the following:
```yaml
imports:
    - { resource: '@CleverAgeEAVManagerUserBundle/Resources/config/default/security.yml' }
```
The default configuration considers the EAV Manager stands alone in the Symfony's app but you can configure it
differently. If you want to override this configuration, don't hesitate to copy the entire file to change it.

### Import the routing
Append this to your routing.yml:
```yaml
eavmanager_admin:
    resource: '@CleverAgeEAVManagerAdminBundle/Resources/config/routing.yml'
```

### Configure other bundles properly
In the twig section of your config.yml append the following configuration:
```yaml
twig:
    form_themes:
        - SidusDataGridBundle:Form:fields.html.twig
        - CleverAgeEAVManagerLayoutBundle:Form:form.fields.html.twig
```

Configure Assetic (at the bottom of your config.yml for example)

```yaml
assetic:
    debug: "%kernel.debug%"
    use_controller: "%kernel.debug%"
    bundles:
        - CleverAgeEAVManagerLayoutBundle
    node: "%node_path%"
    filters:
        cssrewrite: ~
        uglifyjs2:
            bin: "%node_uglifyjs_path%"
        uglifycss:
            bin: "%node_uglifycss_path%"
    assets:
        map1:
            input: "%kernel.root_dir%/../web/assets/jquery/jquery.min.map"
            output: js/jquery.min.map
        src1:
            input: "%kernel.root_dir%/../web/assets/jquery/jquery.js"
            output: js/jquery.js
```

We will use UglifyJs and UglifyCSS to minify and compile the CSS and JS necessary for the admin to run, for this we need
to make the binary path of those libraries configurable per environment.

Append this to your parameters.yml.dist:
```yaml
parameters:
    # ...
    node_path: /usr/bin/nodejs
    node_uglifyjs_path: /usr/local/bin/uglifyjs
    node_uglifycss_path: /usr/local/bin/uglifycss
```

To install these libraries, you will need npm installed on your system:
```bash
$ sudo npm install -g uglifyjs
$ sudo npm install -g uglifycss
```

You can also automatically install them locally with a package.json and a composer hook.

### Update composer
Run a global composer update:
```bash
$ composer update
```

### Update your database model
The simple way (not recommanded):
```bash
$ bin/console doctrine:schema:update --force
```

We recommand using Doctrine migrations.

### Create a super-admin user
```bash
$ bin/console eavmanager:create-user -a -p admin admin@my-company.org
```

### Build your model !
You're good to go ! Consult these documentations to help you build your application:

- Building and configuring and extending the model:
    https://github.com/VincentChalnot/SidusEAVModelBundle/blob/master/README.md

- Configuring admins:
    https://github.com/VincentChalnot/SidusAdminBundle/blob/master/README.md

- Configuring datagrids:
    https://github.com/VincentChalnot/SidusDataGridBundle/blob/master/README.md

- Managing assets:
    https://github.com/VincentChalnot/SidusFileUploadBundle/blob/master/README.md
