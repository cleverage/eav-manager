#!/usr/bin/env bash

uglifyjs --compress --mangle -o vendor/cleverage/eav-manager/LayoutBundle/Resources/public/js/build-$(date +"%Y%m%d").js \
vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js \
vendor/twbs/bootstrap-sass/assets/javascripts/bootstrap.js \
vendor/mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/public/js/mopabootstrap-subnav.js \
vendor/mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/public/components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.js \
vendor/mopa/bootstrap-bundle/Mopa/Bundle/BootstrapBundle/Resources/public/components/smalot-bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.fr.js \
vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js \
vendor/blueimp/jquery-file-upload/js/jquery.fileupload-jquery-ui.js \
vendor/blueimp/jquery-file-upload/js/jquery.iframe-transport.js \
vendor/sidus/file-upload-bundle/Resources/public/js/jquery.fileupload.sidus.js \
vendor/pinano/select2-bundle/Pinano/Select2Bundle/Resources/public/js/select2.min.js \
vendor/pinano/select2-bundle/Pinano/Select2Bundle/Resources/public/js/i18n/fr.js \
vendor/sidus/eav-bootstrap-bundle/Resources/public/js/autocomplete.selector.js \
vendor/sidus/eav-bootstrap-bundle/Resources/public/js/autocomplete.combo.selector.js \
vendor/sidus/eav-bootstrap-bundle/Resources/public/js/bootstrap.collection.js \
vendor/sidus/eav-bootstrap-bundle/Resources/public/js/sortable.collection.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.autoload.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.bootstrap.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.datepickers.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.edit-inline.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.embed-multi-family.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.global.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.lightbox.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.pagination.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.selectall.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.sortable-collections.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/initialization/init.wysiwyg.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/navigation/autoclose.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/navigation/automodal.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/navigation/navigation.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/navigation/navigation.lock.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/forms/better.tabs.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/forms/input.picker.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/forms/media.browser.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/datagrid/datagrid.updater.js \
vendor/cleverage/eav-manager/LayoutBundle/Resources/javascript/lib/ajax.navigation.js
