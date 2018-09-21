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

> Translating attributes alsow impact filters in the datagrid

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

Doing so result in translating all attributes named `descritpion` or `color` on the whole project, whatever family they are in.

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

Tranlating navigation bar follow this structure.

```yaml
admin:
    <menu_key>:
        title: My Translation
```

Some elements are available in the navigation bar by default, such as `Process`, `Media` or `Group`:

```yaml
admin:
    process:
        title: Processus
    user_groups:
        title: Utilisateurs
    media:
        title: Medias
```

Other entries depends on your `sidus_admin` configuration, inside `app/config/admin/*.yml` files. For instance, you have a file named  `app/config/admin/car.yml` containing

```yaml
sidus_admin:
    configurations:
        car: # <- key to translate 
            controller: '%eav.controller%'
            entity: '%sidus_data_class%'
            [...]
```

To translate this entry, you can add the follow snippets in you translation file :

```yaml
admin:
    [...]
    car:
        title: Voiture
    [...]
```

## Translating validation messages

> EAV validation and attributes validation rules are two differents things.

Theses messages are displeyed when you forget to fill in a `required` field for example. You can customize the following validations types :
* `required`
* `unique`
* `mutiple`
* `collection`
* `global_unique`

You have three ways to translate validation messages:

* On family / attribute level
```yaml
eav:
    family:
        <family_code>:
            attribute:
                <attribute_code>:
                    validation:
                        <type>: <error_msg>
```
* On attribute level
```yaml
eav:
    attribute:
        <attribute_code>:
            validation:
                <type>: <error_msg>
```
* on global level
```yaml
eav:
    validation:
        <type>: <error_msg>
```

> The more specific translation overrides generic ones.  family / attribute > attribute > global

### Family > Attribute level

This translation will only apply to error messages for attribute `description` of family `CAR`.

```yaml
eav:
    family:
        Car:
            attribute:
                description:
                    validation:
                        required: 'Description requise'
                        unique: 'La description doit être unique'
```

If you do not fill in the field `description`, the field will be highlited in red and show the message `Description requise`.

### Attribute level

This translation will apply to error messages for **every attribute** named `description` in the whole project.

```yaml
eav:
    attribute:
        description:
            validation:
                required: 'Description requise'
                unique: 'La description doit être unique'
```

### Global level

This translation will apply to error messages for *every family* and **every attribute** where `required` is set to `true`.

```yaml
eav:
    validation:
        required: 'Ce champ est requis'
```

