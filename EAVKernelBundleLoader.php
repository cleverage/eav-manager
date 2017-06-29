<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace CleverAge\EAVManager;

/**
 * Allow simple loading of all necessary bundles.
 */
class EAVKernelBundleLoader
{
    /**
     * Return the required bundles.
     *
     * @return array
     */
    public static function getBundles()
    {
        // Dependencies
        return [
            // Required by SidusEAVBootstrapBundle
            new \Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            // Required by SidusFilterBundle
            new \WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            // Required by SidusEAVBootstrapBundle
            new \Pinano\Select2Bundle\PinanoSelect2Bundle(),
            // Required by SidusFileUploadBundle
            new \Oneup\UploaderBundle\OneupUploaderBundle(),
            // Required by SidusFileUploadBundle
            new \Oneup\FlysystemBundle\OneupFlysystemBundle(),
            // Required by SidusPublishingBundle
            new \Circle\RestClientBundle\CircleRestClientBundle(),

            // Sidus bundles
            //  Base bundle for EAV model
            new \Sidus\EAVModelBundle\SidusEAVModelBundle(),
            // Data filtering based on user input
            new \Sidus\FilterBundle\SidusFilterBundle(),
            // Data filtering with EAV support
            new \Sidus\EAVFilterBundle\SidusEAVFilterBundle(),
            // Bootstrap integration + additional EAV components
            new \Sidus\EAVBootstrapBundle\SidusEAVBootstrapBundle(),
            // Datagrid made easy
            new \Sidus\DataGridBundle\SidusDataGridBundle(),
            // EAV support for datagrids
            new \Sidus\EAVDataGridBundle\SidusEAVDataGridBundle(),
            // Collect entities, serialize and push them on configured remote servers
            new \Sidus\PublishingBundle\SidusPublishingBundle(),
            // Easily attach file to doctrine's entities
            new \Sidus\FileUploadBundle\SidusFileUploadBundle(),
            // Very basic admin configuration in YML to regroup entities and route actions easily
            new \Sidus\AdminBundle\SidusAdminBundle(),

            // Additionnal Bundles
            // TinyMCE WYSIWYG integration
            new \Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            // JS routing needed for TinyMCE extensions
            new \FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            // Automatic image resizing
            new \Liip\ImagineBundle\LiipImagineBundle(),
            // Clean HTML input (or during import)
            new \Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),

            // Clever Process Bundle
            new \CleverAge\ProcessBundle\CleverAgeProcessBundle(),

            // CleverAge EAVManager
            new \CleverAge\EAVManager\EAVModelBundle\CleverAgeEAVManagerEAVModelBundle(),
            new \CleverAge\EAVManager\LayoutBundle\CleverAgeEAVManagerLayoutBundle(),
            new \CleverAge\EAVManager\AdminBundle\CleverAgeEAVManagerAdminBundle(),
            new \CleverAge\EAVManager\UserBundle\CleverAgeEAVManagerUserBundle(),
            new \CleverAge\EAVManager\SecurityBundle\CleverAgeEAVManagerSecurityBundle(),
            new \CleverAge\EAVManager\AssetBundle\CleverAgeEAVManagerAssetBundle(),
            new \CleverAge\EAVManager\ProcessBundle\CleverAgeEAVManagerProcessBundle(),

            // ApiPlatformBundle support for EAV manager
            new \CleverAge\EAVManager\ApiPlatformBundle\CleverAgeEAVManagerApiPlatformBundle(),
        ];
    }
}
