<?php

namespace CleverAge\EAVManager\AdminBundle\Form\Type;


use Sidus\AdminBundle\Routing\AdminRouter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminLink extends AbstractType
{
    /** @var AdminRouter */
    protected $adminRouter;

    /**
     * @param AdminRouter $adminRouter
     */
    public function __construct(AdminRouter $adminRouter)
    {
        $this->adminRouter = $adminRouter;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'action',
        ]);
        $resolver->setDefaults([
            'admin' => null,
        ]);
        $resolver->setNormalizer('uri', function (Options $options, $value) {
            if (null === $value) {
                return $this->adminRouter->generateAdminPath($options['admin'], $options['action'],
                    $options['route_parameters']);
            }

            return $value;
        });
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'admin_link';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'sidus_link';
    }
}
