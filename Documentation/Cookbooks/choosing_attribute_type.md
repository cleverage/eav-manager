## Choosing an attribute type

Keep in mind that ALL types are nullable.

### Basic types

These types correspond to basic HTML inputs.

#### string
Stores strings up to 255 characters, is automatically indexed by MySQL.

````html
<input type="text">
````

#### text
Stores longer texts, for rich text input see the ````html```` type.

````html
<textarea></textarea>
````

#### integer
Stores integers, warning: no default validation is applied, any string input will simply be casted to an int for
storage.

````html
<input type="text">
````

#### decimal
Stores decimals with default precisions settings. Like integer, no validation is applied at input but the data will be
cast to float for storage.

````html
<input type="text">
````

#### boolean
Stores a boolean (or null). Will always stores a boolean after form submission unless programmatically set to null.

````html
<input type="checkbox">
````

#### choice
Stores a string like the ````string```` type (max 255 characters and indexed). See Symfony's 
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

#### hidden
Stores a string like the ````string```` type (max 255 characters and indexed).

````html
<input type="hidden">
````

If you really need to completely hide the attribute in the form, you can use the hidden attribute option, it will work
with any attribute type: ````options: {hidden: true}````

### Enriched types

These type use an additional layer of Javascript to work.

#### html
Stores a long text, like the ````text```` type but triggers the TinyMCE editor to allow rich text formatting.
TinyMCE theme setting can be controlled through ````form_options: {tinymce_theme: <themecode>}````.

See [Stfalcon/TinymceBundle configuration reference](https://github.com/stfalcon/TinymceBundle#custom-configurations)
for more information.

#### date
Stores a DateTime with the time set to 00:00 (careful with the timezone when displaying the field). Displays a
date-picker using Bootstrap DatePicker.

#### datetime
Stores a DateTime, Displays a datetime-picker using Bootstrap DatePicker.


#### data_selector
Stores the id of an other EAV data. This is the most basic field for relationships. It uses a simple ````<select>````
to display the available entities. If you have to many entities, use the ````autocomplete_data_selector```` instead.

You can use the ````allowed_families````attribute option to restrict the list of entities, if you don't, the list will
contain all the EAV entities of your app.

````yaml
flags:
    type: data_selector
    options:
        allowed_families:
            - Post
````

#### embed





#### string_identifier





#### integer_identifier





#### switch





#### autocomplete_data_selector





#### combo_data_selector





#### document





#### image





#### media_browser





#### embed_multi_family





#### constrained_autocomplete_data_selector




