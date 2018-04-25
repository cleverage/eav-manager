CleverAge/EAVManager
====================

| Help needed |
| ----------- |
| This documentation is a work in progress, we need help to write guides and cookbooks |
| Don't hesitate to create issues if you want to speed up the process for a particular feature |

# What's inside?

The "EAV Manager" or "Clever Data Manager" (CDM) is an extensive set of tools designed to build business-oriented data
repositories in Symfony.

It consists of several bundles put together to speed-up data modeling, data transformation and user interface
development.

## Philosophy

The CDM was designed using the same philosophy than the Symfony framework, using many small independent "bricks" that
provide simple but easily extendable features.

We didn't want to create a full-stack solution with a huge coupling between its components but instead focus on keeping
things simple and reusable.

## Installation

Either use the [EAVManager starter kit](https://github.com/cleverage/eav-manager-starter-kit) or follow these steps:

 - [How to install this bundle manually](Documentation/A-01-install.md)

## Components

To understand the various configurations needed in order to create a full-scale application using this bundle, you need
to take a look at the inner components under the hood:

 - [Components](Documentation/B-01-components.md)

## Configuration

You need to check several different bundle documentation that are stored in different places:

Only the checked entries are completed.

 - [x] [EAV Model documentation](https://vincentchalnot.github.io/SidusEAVModelBundle)
 - [-] [Process bundle documentation](https://github.com/cleverage/process-bundle)
 - [ ] [EAV bootstrap extension](https://github.com/VincentChalnot/SidusEAVBootstrapBundle)
 - [x] [Admin configuration](https://github.com/VincentChalnot/SidusAdminBundle)
 - [ ] [Datagrid configuration](https://github.com/VincentChalnot/SidusDataGridBundle)
 - [ ] [Filter configuration](https://github.com/VincentChalnot/SidusFilterBundle)
 - [x] [EAV Filter configuration](https://github.com/VincentChalnot/SidusEAVFilterBundle)
 - [-] [File upload configuration](https://github.com/VincentChalnot/SidusFileUploadBundle)
 - [x] [Base Bundle](https://github.com/VincentChalnot/SidusBaseBundle)

## Cookbooks

Only the checked entries are completed.

### Basics

 - [ ] [Project setup](Documentation/Cookbooks/project_setup.md)
 - [ ] [Choosing an attribute type](Documentation/Cookbooks/choosing_attribute_type.md)
 - [ ] [Context setup](Documentation/Cookbooks/context_setup.md)

### Data management

 - [ ] [Data import](Documentation/Cookbooks/data_import.md)
 - [ ] [Data export](Documentation/Cookbooks/data_export.md)
 - [ ] [API Platform](Documentation/Cookbooks/api_platform.md)
 - [ ] [Doctrine events](Documentation/Cookbooks/doctrine_events.md)

### Internationalization

 - [ ] [Translations](Documentation/Cookbooks/translations.md)
 - [ ] [EAV translations](Documentation/Cookbooks/eav_translations.md)
 - [ ] [Model translations](Documentation/Cookbooks/model_translations.md)

### Going further

 - [ ] [Elastic Search setup](Documentation/Cookbooks/elastic_search_setup.md)

### Customizing

 - [ ] [Custom actions](Documentation/Cookbooks/custom_actions.md)
 - [ ] [Custom attribute type](Documentation/Cookbooks/custom_attribute_type.md)
 - [ ] [Custom autocomplete](Documentation/Cookbooks/custom_autocomplete.md)
 - [ ] [Custom datagrid](Documentation/Cookbooks/custom_datagrid.md)
 - [ ] [Custom datagrid filters](Documentation/Cookbooks/custom_datagrid_filters.md)
 - [x] [Custom EAV Queries](Documentation/Cookbooks/custom_eav_query.md)
 - [ ] [Custom JS and CSS](Documentation/Cookbooks/custom_js_css.md)

### Advanced concepts

 - [ ] [Advanced EAV context features](Documentation/Cookbooks/advanced_context.md)
 - [ ] [Advanced form options](Documentation/Cookbooks/advanced_form_options.md)
 - [ ] [Base config override](Documentation/Cookbooks/base_config_override.md)
 - [ ] [Entities inheritance](Documentation/Cookbooks/entities_inheritance.md)
 - [ ] [Form templating](Documentation/Cookbooks/form_templating.md)
 - [ ] [Model permissions](Documentation/Cookbooks/model_permissions.md)
 - [ ] [Publication workflow](Documentation/Cookbooks/publication_workflow.md)
