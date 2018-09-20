Internationalization
====================

How to translate your project ?

# Translation files

This project uses Symfony translation mechanics. You can read [the official documentation](https://symfony.com/doc/3.4/translation.html) first. 

By default, the langage used is based on your project configuration, inside `app/config/config.yml`

```yaml
parameters:
    locale: fr
````

If you want you app to be available in multiple langagues, you'll have to use Symfony `{_locale}` parameter in your routes. Follow [the official routing documentation](https://symfony.com/doc/3.4/routing.html) to learn more.

# Translations

## Translate family name

Here is an example of a simple family translation

```yaml
sidus_eav_model:
    families:
        Car: # <- this is the Family Code
            data_class: MyCompany\MyBundle\Entity\Family\Car
            attributeAsLabel: name
            attributes:
                name: ~
                description: ~
                color: ~
        [...]            
```
 In the `messages.fr.yml` translation file, this results in
 
 ```yaml
 eav:
     family:
         Car: # <- this is the Family Code
             label: Voiture
         [...]
 ```

## Translate family attributes

Let's say you want to translate your family's attribute
```yaml
sidus_eav_model:
    families:
        Car:
            data_class: MyCompany\MyBundle\Entity\Family\Car
            attributeAsLabel: name
            attributes: # list of my car's attributes
                name: ~
                description: ~
                color: ~
        [...]            
```

In the `messages.fr.yml` translation file, this results in

 ```yaml
 eav:
     family:
         Car:
             label: Voiture
             attributes:
                name:
                    label: Nom
                description:
                    label: Description
                color:
                    label: Couleur
         [...]
 ```

Model vs Entities translation
static translations
context translations language/region
