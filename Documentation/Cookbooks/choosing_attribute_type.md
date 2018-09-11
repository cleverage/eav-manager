## Choosing an attribute type

When you build your model, you should have a quick preview of all the available attribute types in mind, this will help
you save a lot of time.

All attribute types supports the
[_multiple_ attribute property](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/03-multiple.html)
which allows you to add multiple values to a single
attribute. Any multiple attribute can optionally be sorted using the ````sortable```` form option.

All these types should cover the vast majority of SQL storage types, meaning that for most application you will not need
to declare additional storage fields in your ````Value```` class but simply use the existing ones with custom form
types.

For more information on how to create custom attribute types, 
[see this dedicated chapter](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/11-extend.html).

Keep in mind that by design ALL types are nullable. You can set an attribute as ````required```` to make it act like a
non-nullable property.

### Basic types

These types correspond to basic HTML inputs.

| Code | Description | Input type |
| --- | --- | --- |
| **string** | Stores strings up to 255 characters, is automatically indexed by MySQL. | ````<input type="text">```` |
| **text** | Stores longer texts, for rich text input see the ````html```` type. | ````<textarea></textarea>```` |
| **integer** | Stores integers, warning: no default validation is applied, any string input will simply be casted to an int for storage. | ````<input type="text">```` |
| **decimal** | Stores decimals with default precisions settings. Like integer, no validation is applied at input but the data will be cast to float for storage. | ````<input type="text">```` |
| **boolean** | Stores a boolean (or null). Will always stores a boolean after form submission unless programmatically set to null. | ````<input type="checkbox">```` |
| **choice** | Stores a string like the ````string```` type. [See dedicated section](#choice-choice) | ````<select>...</select>```` |
| **hidden** | Stores a string like the ````string```` type. [See dedicated section](#hidden-hidden) | ````<input type="hidden">```` |
| **string_identifier** | Acts exactly like the ````string```` type but is automatically set to ````unique: true```` and ````required: true```` | ````<input type="text">```` |
| **integer_identifier** | Acts exactly like the ````integer```` type but is automatically set to ````unique: true```` and ````required: true```` | ````<input type="text">```` |

### Enriched types

Input types are not detailed here because all types use a complex HTML structure and an additional layer of Javascript to work .

| Code | Description |
| --- | --- |
| **html** | Stores a long text, like the ````text```` type but triggers the TinyMCE editor to allow rich text formatting. [See dedicated section](#html-html) |
| **switch** | Just like a ````boolean```` type but with a nicer look. |
| **date** | Stores a DateTime with the time set to 00:00 (careful with the timezone when displaying the field). Displays a date-picker using Bootstrap DatePicker. |
| **datetime** | Stores a DateTime, Displays a datetime-picker using Bootstrap DatePicker. |
| **data_selector** | Stores the id of an other EAV data. This is the most basic field for relationships. [See dedicated section](#data-selector-data_selector) |
| **autocomplete_data_selector** | Just like a ````data_selector```` but with a Select2 autocomplete. [See dedicated section](#autocomplete-data-selector-autocomplete_data_selector) |
| **constrained_autocomplete_data_selector** | Just like the autocomplete_data_selector but with a real SQL constraint. [See dedicated section](#constrained-autocomplete-data-selector-constrained_autocomplete_data_selector) |
| **combo_data_selector** | Allows you to choose the family of the relation before picking the data with an autocomplete. [See dedicated section](#combo-data-selector-combo_data_selector) |
| **embed** | Stores a relationship with and other EAV data but allows you to directly edit the data in a sub-form. [See dedicated section](#embed-embed) |
| **embed_multi_family** | [See dedicated section](#embed-multi-family-embed_multi_family) |
| **media_browser** | Allows you to pick a media from a datagrid. [See dedicated section](#media-browser-media_browser) |
| **document** | Simple upload form for any document. [See dedicated section](#document-document) |
| **image** | Simple upload form for any image. [See dedicated section](#image-image) |

### Detailed attributes specifications

#### Choice (choice)

See Symfony's 
[ChoiceType reference](https://symfony.com/doc/current/reference/forms/types/choice.html)
for more information on how to setup the form part.

Configuration example:

````yaml
status:
    type: choice
    form_options:
        choices:
            Valid: valid
            Draft: draft
            Rejected: rejected
            Pending validation: pending
````

````html
<select>
    <option value="valid">Valid</option>
    <option value="draft">Draft</option>
    <option value="rejected">Rejected</option>
    <option value="pending">Pending validation</option>
</select>
````

If you want to use the multiple option of the form type (NOT the multiple option of the attribute itself), you have to
tell the storage layer that the form type will send you an array instead of single string:

````yaml
flags:
    type: choice
    collection: true # This is NOT the same as multiple: true
    form_options:
        multiple: true
        choices: # ...
````

See this chapter about
[multiple attributes](https://vincentchalnot.github.io/SidusEAVModelBundle/Documentation/03-multiple.html)
for further information.

#### Hidden (hidden)

This types simply renders a hidden HTML input, if you really need to completely hide the attribute in the form, you can
use the hidden attribute option, it will work with any attribute type:

````yaml
    <attribute>:
        options:
            hidden: true
````

#### HTML (html)

Stores a long text, like the ````text```` type but triggers the TinyMCE editor to allow rich text formatting.
TinyMCE theme setting can be controlled through the ````tinymce_theme```` form option:

````yaml
    <attribute>:
        type: html
        form_options:
            tinymce_theme: <themecode>
````

See [Stfalcon/TinymceBundle configuration reference](https://github.com/stfalcon/TinymceBundle#custom-configurations)
for more information.

#### Data Selector (data_selector)

You can use the ````allowed_families```` attribute option to restrict the list of entities, if you don't, the list will
contain all the EAV entities of your app. (With a configurable limit of 100 results)

The ````allow_add```` and ````allow_edit```` form options will append buttons on the right side of the autocomplete that
when clicked will open a modal to dynamically create a new entity or edit the selected entity.

````yaml
relatedPost:
    type: data_selector
    options:
        allowed_families:
            - Post
    form_options:
        allow_add: true
        allow_edit: true
````

It uses a simple ````<select>```` to display the available entities so if you have to many entities, you should consider
using the [autocomplete_data_selector](#autocomplete-data-selector-autocomplete_data_selector) type instead.

**Advanced usage**: You can override the default admin, actions and target of quick create/edit buttons:

````yaml
    form_options:
        allow_add: true
        allow_edit: true
        admin: mycustomadmin # Default: _data
        edit_action: customEdit # Default: edit
        create_action: customCreate # Default: create
        target: my-custom-target # Default: null
````

#### Autocomplete Data Selector (autocomplete_data_selector)

This type will use Select2 with a remote URL to filter data by it's label. You can use the ````allowed_families````
attribute option just like the ````data_selector```` type to filter data based on their families.

This type supports the ````choice_label```` form option to control what's displayed in the results.

You can control how result are filtered by extending the type and customizing the ````query_uri```` form option to point
to a custom controller/action.

The ````AutocompleteDataSelectorType```` can be also used outside a EAV form to select an EAV data but the 
````choice_label```` won't work.

You can use the ````allow_add```` and ````allow_edit```` form options with this type just like for the
````data_selector```` type.

#### Combo Data Selector (combo_data_selector)

This basically allows you to first select the family of the object you are looking for and then the object itself.

See availables options in the [data_selector section](#data-selector-data_selector).

#### Constrained Autocomplete Data Selector (constrained_autocomplete_data_selector)

Same as the [autocomplete_data_selector](#autocomplete-data-selector-autocomplete_data_selector) but with a real SQL
constraint on it.
This means that when an object A points to an object B through this attribute type, the object B can't be deleted
without deleting the relationship first (deleting object A or unsetting the attribute).

#### Embed (embed)

This type creates a sub-form inside the main form to directly edit the related data. In combination with the
````multiple```` it can be a really powerful tool to enrich associations between eav data.

When validating the main form, the validator will also check the validity of every related embed objects.

The created objects can also be listed and edited independently with the proper admin and datagrid configuration.

**WARNING**: This type needs a single family in the ````allowed_families```` option
([See data_selector](#data-selector-data_selector)). If you need an embed with multiple families, consider using the
[the embed_multi_family type](#embed-multi-family-embed_multi_family)

**WARNING**: When using multiple sortable embed attributes, you cannot hope to edit the data separately without
risking some inconsistencies because all elements will be overwritten on save without being really reordered: saving the
form is just going to re-write all the sub-element properties like you want but keeping the same order than before for
data pointers.

#### Embed Multi Family (embed_multi_family)

This type will allow you to dynamically attach objects with different families to the same attribute. It's not a "real"
embed as it uses modals to create and edit elements because it's technically extremely difficult to submit a form with
variable sub-form inside a specific form node.

This is very similar to a multiple combo data selector with the ````allow_add```` and ````allow_edit```` form options
but with a better preview of each element directly in the form.

**Advanced usage**: The preview of each element can be overriden by specifying a dedicated admin with a ````preview````
action in the form options.

#### Media Browser (media_browser)

This type is an advanced data picker, it's only meant to pick data from the ````Document```` or ````Image```` families
but it can be used as an example to build custom data picker.

The main feature of this data picker is that it allows you to browse the data of the target family in a separate
datagrid displayed in a modal. It also allows you to create and edit data on the fly in the same modal.

#### Document (document)

This type allows you to create relationships with Document entities through an upload input. Upload mechanism uses a
configuration maintained by both Sidus/FileUploadBundle and OneUp/UploaderBundle to describes where to put uploaded
file using the Flysystem abstraction layer.

#### Image (image)

See document.
