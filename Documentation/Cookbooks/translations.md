Internationalization
====================

How to translate your project ?

> Note: Internationalization and Contextualization are two differents things. 
>
> Internationalization is used for translating UI while Contextualization is used when storing data in different languages.
> 
> For example, someone uses your app in english (internationalization), while storing a product description in french (Contextualization).
>
> Read more about data contextualization on [the official documentation](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/09-context.html)
 
# Translation files

This project uses Symfony translation mechanics. You can read [the official documentation](https://symfony.com/doc/3.4/translation.html) first. 

By default, the langage used is based on your project configuration, inside `app/config/config.yml`

```yaml
parameters:
    locale: fr
````

If you want you app to be available in multiple langagues, you'll have to use Symfony `{_locale}` parameter in your routes. Follow [the official routing documentation](https://symfony.com/doc/3.4/routing.html) to learn more.

# Translations

## Translating family name

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

## Translating family attributes

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

# Translating attributes

You can always choose to translate attributes on a global level. In the `messages.fr.yml`, add an `attributes` key.

```yaml
eav:
    family:
    [...]
    attribute:
        name:
            label: Nom
        description:
            label: Description
        color:
            label: Couleur    
```

Doing so result in translating all attributes named `descritpion` or `color` on the whole project.

> Note : family translation overrides global translation.

```yaml
family:
     Car:
         label: Voiture
         attributes:
            description:
                label: Description
    [...]
    attribute:
        [...]
        description:
            label: Commentaire
        [...]
```

In this case, `decription` will be translated as `Description` when it's related to the `Car` family but will be translated as `Commentaires` for every other families.

## Translating base attributes

Base attributes, such as `CreatedAt` or `UpdatedAt` can also be translated. In your translation file, simply add theses lines (at yaml root level, no indentation needed)

 ```yaml
"Created at": "Créé le"
"Updated at": "Mis à jour le"
```

## Translating navigation bar menu


## Translating error and validation messages


Model vs Entities translation
static translations
context translations language/region
