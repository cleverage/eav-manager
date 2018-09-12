<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public static function getBundles(): array
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

            // Sidus bundles
            // Base bundle containing many dependencies
            new \Sidus\BaseBundle\SidusBaseBundle(),
            // Base bundle for EAV model
            new \Sidus\EAVModelBundle\SidusEAVModelBundle(),
            // Data filtering based on user input
            new \Sidus\FilterBundle\SidusFilterBundle(),
            // Data filtering with EAV support
            new \Sidus\EAVFilterBundle\SidusEAVFilterBundle(),
            // Bootstrap integration + additional EAV components
            new \Sidus\EAVBootstrapBundle\SidusEAVBootstrapBundle(),
            // Datagrid made easy
            new \Sidus\DataGridBundle\SidusDataGridBundle(),
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
            // Default cache system
            new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),

            // Clever Process Bundle
            new \CleverAge\ProcessBundle\CleverAgeProcessBundle(),

            // Simple permission management
            new \CleverAge\PermissionBundle\CleverAgePermissionBundle(),

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
