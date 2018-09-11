<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use CleverAge\EAVManager\AdminBundle\Templating\EAVTemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use CleverAge\EAVManager\AdminBundle\Form\EAVFormHelper;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\WrongFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('read', data)")
 */
class ReadAction implements ActionInjectableInterface
{
    /** @var EAVFormHelper */
    protected $formHelper;

    /** @var EAVTemplatingHelper */
    protected $templatingHelper;

    /** @var Action */
    protected $action;

    /**
     * @param EAVFormHelper       $formHelper
     * @param EAVTemplatingHelper $templatingHelper
     */
    public function __construct(
        EAVFormHelper $formHelper,
        EAVTemplatingHelper $templatingHelper
    ) {
        $this->formHelper = $formHelper;
        $this->templatingHelper = $templatingHelper;
    }

    /**
     * @param Request         $request
     * @param DataInterface   $data
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, DataInterface $data, FamilyInterface $family = null): Response
    {
        if ($family) {
            WrongFamilyException::assertFamily($data, $family->getCode());
        } else {
            $family = $data->getFamily();
        }
        $form = $this->formHelper->getForm($this->action, $request, $data, ['disabled' => true]);

        return $this->templatingHelper->renderFormAction($this->action, $request, $family, $form, $data);
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
