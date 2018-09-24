Custom attribute type
=====================

This will guide you through the steps of creating a custom attribute type. It takes three steps

1. Declare your attribute type as a service
2. Create a new Form Type
3. Create a custom template

### Use case

In this example, we will create a new type called "Size" wich allow to select the size of a T-shirt. Il will be displayed as a select button, with choices like "S", "M", "L", "XL" etc.

## Service declaration

First, you have to declare your new type as a service.

```yaml
services:
    cleverage.attribute_type.size: # 1 - service identifier
            class: '%sidus_eav_model.attribute_type.default.class%'
            arguments:
                - size # 2 - attribute code
                - stringValue # 3 - database type
                - MyNamespace\MyBundle\Form\Type\SizeType # 4 - Form type
            tags:
                - { name: sidus.attribute_type }
```

> Note : do not forget to add the tag `sidus.attribute_type` or it won't work !

Let's take a look at the required arguments : 

1. Service identifier

Used to identify your service. Has to be unique.
Best practice for naming : `<my_namespace>.attribute_type.<name_of_my_attribute>`

2. Attribute code

Name of your attribute. This name will be used when chosing the type of your EAV Attribute when declaring the model. Exemple with our `size` attribute :

```yaml
sidus_eav_model:
    families:
        Product:
            [...]
            size:
                type: size # <- your custom type name
                required: true
            [...]
```

3. Database type

This declares the ways your type will be stored in database. Here, we only need the size information (S, M, L etc.) so we will be using the `stringValue`. For a list of database type and more information, [see the documentation about extending the model](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/11-extend.html).

4. Form Type

Finaly, we need to specify the path to our Form Type Class where we can delare the form name and optionaly form options.

## Form Type

According your service declaration, we now need to create a Form Type Class named `MyNamespace\MyBundle\Form\Type\SizeType.php`. This class is a based on the Symfony Form Type class, so please refer to [the official Symfony Documentation](https://symfony.com/doc/3.4/form/create_custom_field_type.html)

```php
/**
 * You can inherit from other forms types, depending on your needs. In most cases, use the AbstractType
 */
class SizeType extends AbstractType
{
    
    /**
    * Add default values, options etc.
    */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Set default choices
        $resolver->setDefaults(
            [
                'choices' => [
                    'Extra Small' => 'XS',
                    'Small' => 'S',
                    'Medium' => 'M',
                    'Large' => 'L',
                    'Extra Large' => 'XL',
                    'Extra Extra Large' => 'XXL',
                ],
                'choice_attr' => function ($val, $key, $index) {
                    return ['class' => 'select2'];
                },
            ]
        );
    }
    
    /**
     * Use to declare the form parent
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * Use to name your form
     */
    public function getName()
    {
        return 'size';
    }
}
```

## Custom template

Each field type is rendered by a template fragment, which is determined in part by the class name of your type. This the class is called `SizeType, the tample name will be `size`. As our form as the `ChoiceType::class` as parent, it will be rendered as a `ChoiceType`. But here, we would like to display it as a simple list.

To create a template, edit `fields.html.twig` file, and add a `{% block size_widget %}` block. The widget name is your form type name.

```twig
{# app/Resources/views/form/fields.html.twig #}
{% block size_widget %}
    {% spaceless %}
    <ul>
        {% for child in form %}
            <li>
                {{ form_widget(child) }}
                {{ form_label(child) }}
            </li>
        {% endfor %}
    </ul>
    {% endspaceless %}
{% endblock %}
```

In the template, you have access to the `form` variable.

When everything is defined, your widget will be displayed on every form for every attribute that has the type `size`.
