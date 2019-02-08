CleverAge/EAVManager Changelog
==============================

## v2.1.1

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2019-02-08 | no         | no            |

Fixing dependency issue.

## v2.1.0

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2019-02-08 | minor      | no            |

### Templating
- Fixing missing dependency in form rendering

### UI
- Minor esthetic fix in links without icons
- Datagrid filters now collapse if more than 3
- Improving left menu rendering when an admin has only one family

### Model
- Fixing datagrid attributes with paging

## v2.0.5

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2019-01-09 | no         | no            |

### API
- Fixing resource normalizer when associated file is missing

## v2.0.4

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2019-01-09 | no         | yes           |

### Doctrine
- Deprecating EntityManager and DataRepository as services

### Model
- New datagrid attribute type: still needs some work

### UI
- Minor improvements in Fileupload widget

### API
- Fixing bad ApiPlatform normalizer configuration since 2.0 refactoring

## v2.0.3

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-12-12 | no         | no            |

### Documentation
- Improving installation documentation

### UI
- Better permissions checking in menus and navigation

### Users
- Refactoring and fixing user management commands
- Fixing user management actions
- User infos in EAV are now available at user creation

### Actions
- New actions to edit singleton families

### Admin
- Fixing admin resolution in datagrid templates

### Templating
- Improving template reusability
- Adding support for disabled datagrids

### Process
- Fixing tasks exceptions handling conventions
- Fixing empty resultsets error logging
- Fixing missing log context

### Translations
- Fixing translation domains issues

### Assets
- Updating LiipImagineBundle to v2.0

## v2.0.2

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-10-25 | minor      | no            |

### Documentation
- How to kickstart your project

### Datagrids
- Fixing columns rendering when no action is allowed

### Templating
- Major refactoring for better reusability: could induce BC breaks
- Switching to FontAwesome only for icons rendering in Mopa

### Admin
- Fixing default admin configurations

### API
- Fixing API platform support

### Users
- Adding missing group voter

## v2.0.1

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-10-05 | no         | no            |

### Datagrids
- Fixing configuration code

## v2.0.0 New Major release

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-10-04 | yes        | yes           |

### Documentation
- How to choose an attribute type cookbook
- How to translate attributes and families cookbook
- How to translate validation messages
- Adding a custom attribute type cookbook
- Global update

### Process
- Migration to v2.0, lots of deprecation and BC breaks
- New EAVUpdateSingleValueTask

### Assets
- Image preview in lightbox

### Configuration
- Fixing minor configuration issues

### UI
- Better layout for datagrids and modals
- Fixing Datepickers: switching to new library
- Nicer login screen using Unsplash
- Fixing missing translations
- Adding automodal option in datagrid clickable template and actions

### Datagrids
- Migrating to new dependency version with lots of new features

### Controllers & actions
- Deprecating usage of all controllers, switching to actions: Huge refactoring
- All default configuration now uses actions instead of controllers
- Lots of new helpers used in actions and reusable for custom actions

### JS
- New data-target-element="_CURRENT_TARGET" that can be used in many places to keep the current target
- Minor update in Ajax navigation lib
- New script to update JS build
- Fixing some coding style
- Fixed problem with delete action in #tg_center

### Security
- Better custom permissions supportn now using the cleverage/permission-bundle
- Fixing HTML attributes escaping
- New voter for users: now supporting permissions checks

### Model
- Fixing embed multi attribute types

### Doctrine
- New RepositoryFinder as a service

## v1.3.11

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-06-24 | no         | no            |

### Dependency
- Adding missing BaseBundle dependency in Kernel loader

## v1.3.10

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-05-30 | yes        | yes           |

### Documentation
- Custom EAV Queries cookbook
- Global documentation update

### Regressions
- Fixing BC break induced by Twig update in the TemplateGuesser

### Migration
- Massive migration to Symfony 3.4 configuration style: deprecating all definitions with possible BC breaks
- Switching to ProcessBundle v1.1

### Assets
- Resource manager override is now handled in a different service

### Doctrine
- Refactoring dependencies to always inject the proper EntityManager in case of multiple managers.

## v1.3.9

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-04-24 | no         | no            |

### UX
 - Now displaying integrity constraints that prevents entity removal on delete action page
 - Better layout, sticky action header, better alerts.
 
## v1.3.8

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-04-12 | yes        | yes           |

### User / Security
 - Fixing mailer, using email instead of username
 
### Processes
 - The AbstractEAVQueryTask is now using the new EAVFinder API to create its query builder
 
### Internals
 - Switching some imports to Sidus/BaseBundle that regroups commonly used features accross all bundles
 
### EAV Query API
 - Removing deprecated DataRepository::createOptimizedQueryBuilder calls
 
## v1.3.7

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-21 | no         | no            |

### Forms
 - Removing/disabling edit button in embed multi families form types when form is disabled
 
## v1.3.6

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-16 | no         | no            |

### Processes
 - Fixing item count reporting in EAVReaderTask
 
## v1.3.5

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-16 | no         | no            |

### Context
 - Fixing default context form rendering
 
### Processes
 - Fixing EAVReaderTask

## v1.3.4

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-14 | yes        | yes           |

### DataGrids
 - Changing datagrid rendering method, now using render_datagrid() twig function
 
## v1.3.3

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-14 | no         | no            |

### User / Security
 - Fixing regression with family permissions

## v1.3.2

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-03-14 | no         | no            |

### Processes
 - Fixing EAV Reader task

## v1.3.1

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-02-26 | no         | yes           |

### Deprecated
 - Doctrine's setMaxResults() does'nt behave as expected when using joins, please fix your code using Doctrine
 Paginator

### User / Security
 - Fixing family permission exception on missing family
 - User are now loaded either by username or by email
 
### Processes
 - EAV queries with limits now uses the Doctrine Paginator to allow joins
 
### UI
 - Better clickable columns template (allowing template reuse with options)
 - Error alerts were using the wrong Bootstrap CSS class
 - Action buttons for admins now accepts the icon option

### Licensing
 - Switching to MIT because it's more compatible with Symfony's ecosystem

## v1.3.0

*SUMMARY*: PHP7.1 and new DataGrid system with optional Elastic Search support

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-01-09 | yes        | yes           |

### Back compatibility breaks
 - DataGrid (and filters) configurations now follows a slightly different convention, see Sidus/FilterBundle for more
 information
 - Requires PHP 7.1

### Deprecated
 - Assetic is not a required dependency anymore (but can still be used in final project)
 - Dependency to Sidus/PublishingBundle is now removed because it's not compatible anymore with Symfony's Serializer
 
### DataGrid / Filters
 - Major update to Sidus/FilterBundle v1.3 with better abstraction support
 - Configuration update

### User / Security
 - Login page is now free from any javascript to enhance security
 - Controller refactoring for better access control

### Admin / Routing
 - Adding read action on default controllers with custom CSS style
 - Refactoring default configuration
 
### Integration
 - CSS and JS are now directly pre-compiled in the bundle
 
### UI
 - Major template refactoring, especially for actions
 - Minor Ajax Navigation bugfixes
 
### EAV
 - New FamilyResolver service than tries to resolve the family of a given data class (Used by ApiPlatform)

### Configuration
 - Major configuration refactoring for default config

## 1.2.9

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2018-01-04 | no         | no            |

### ApiPlatform
 - Fixing base EAV filter

### Processes
 - Improved logging support for various tasks
 - Various new tasks and transformers
 
### Integration
 - Fixing DataObject plugin for TinyMCE

### UI
 - Minor templating enhancements and bugfixes
 
### DataGrid
 - Filters refactoring and optimizations


## 1.2.8

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-08-24 | no         | no            |

### UI
 - Fixing pagination issue in datagrid when using ajax navigation
 - Fixing TinyMCE plugins DataObject and DataLink
 - Default families menu improvement
 
### ApiPlatform
 - Major refactoring now allowing filtering on nested properties

## 1.2.7

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-07-11 | no         | no            |

### UI
 - Fixing issues in TinyMCE plugins
 - Fixing media preview size calculation issue
 - Adding family name to embed multi-families
 
## 1.2.6

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-07-03 | no         | no            |

### UI
 - Fixing embed multi-families javascript
 
## 1.2.5

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-07-02 | no         | no*           |

### UI
 - Better tab navigation: remembering the last navigated tab
 - Refactoring data selector

### EAV
 - Adding support for multi-families embed data types
 - Removing dependency on deprecated Variant bundle (Was already deprecated since 1.2.0)
 
### Integration
 - Recompiling CSS with improvements

## 1.2.4

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-06-28 | no         | no            |

### Integration
 - Major Javascript refactoring: better events and better decoupling
 
## 1.2.3

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-06-27 | no         | no            |

### ApiPlatform
 - Adding denormalization support
 
## 1.2.2

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-06-27 | no         | no            |

### Processes
 - Adding new transformers
 - Assets handling
 
### UI
 - Better media preview
 
## 1.2.1

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-06-22 | no         | no            |

### UI
 - Fixed default sort order for assets datagrid and assets browsers
 - Fixed DataLink plugin for TinyMCE
 - Adding clear button in date pickers

### Integration
 - New compiled version of assets
 
### Admin / Routing
 - Fixing redirection after login
 - Fixing exception during delete actions
 
### Console
 - New command to update users passwords

## 1.2.0

| Date       | BC Breaks? | Deprecations? |
| ---------- | :--------: | :-----------: |
| 2017-06-13 | yes        | yes            |

Too many changes: see commits
