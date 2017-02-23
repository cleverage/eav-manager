<?php

namespace CleverAge\EAVManager;

/**
 * Allow simple loading of all necessary bundles
 */
class EAVKernelBundleLoader
{
    /**
     * Return the required bundles
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
            new \Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            // Required by SidusPublishingBundle
            new \Circle\RestClientBundle\CircleRestClientBundle(),

            // Sidus bundles
            //  Base bundle for EAV model
            new \Sidus\EAVModelBundle\SidusEAVModelBundle(),
            // Data filtering based on user input
            new \Sidus\FilterBundle\SidusFilterBundle(),
            // Data filtering with EAV support
            new \Sidus\EAVFilterBundle\SidusEAVFilterBundle(),
            // Bootstrap integration + additionnal EAV components
            new \Sidus\EAVBootstrapBundle\SidusEAVBootstrapBundle(),
            // Datagrid made easy
            new \Sidus\DataGridBundle\SidusDataGridBundle(),
            // EAV support for datagrids
            new \Sidus\EAVDataGridBundle\SidusEAVDataGridBundle(),
            // Handle variants of EAV entities with axles validation
            new \Sidus\EAVVariantBundle\SidusEAVVariantBundle(),
            // Collect entities, serialize and push them on configured remote servers
            new \Sidus\PublishingBundle\SidusPublishingBundle(),
            // Easily attach file to doctrine's entities
            new \Sidus\FileUploadBundle\SidusFileUploadBundle(),
            // Very basic admin configuration in YML to regroup entities and route actions easily
            new \Sidus\AdminBundle\SidusAdminBundle(),

            // Additionnal Bundles
            // TinyMCE WYSIWYG integration
            new \Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            // Automatic image resizing
            new \Liip\ImagineBundle\LiipImagineBundle(),
            // Clean HTML input (or during import)
            new \Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),

            // CleverAge EAVManager
            new \CleverAge\EAVManager\EAVModelBundle\CleverAgeEAVManagerEAVModelBundle(),
            new \CleverAge\EAVManager\LayoutBundle\CleverAgeEAVManagerLayoutBundle(),
            new \CleverAge\EAVManager\AdminBundle\CleverAgeEAVManagerAdminBundle(),
            new \CleverAge\EAVManager\UserBundle\CleverAgeEAVManagerUserBundle(),
            new \CleverAge\EAVManager\SecurityBundle\CleverAgeEAVManagerSecurityBundle(),
            new \CleverAge\EAVManager\AssetBundle\CleverAgeEAVManagerAssetBundle(),
            new \CleverAge\EAVManager\ImportBundle\CleverAgeEAVManagerImportBundle(),
        ];
    }
}
