<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use CleverAge\EAVManager\AdminBundle\Templating\EAVTemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use CleverAge\EAVManager\AdminBundle\Form\EAVFormHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\WrongFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('edit', data)")
 */
class EditAction implements ActionInjectableInterface
{
    /** @var EAVFormHelper */
    protected $formHelper;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RoutingHelper */
    protected $routingHelper;

    /** @var EAVTemplatingHelper */
    protected $templatingHelper;

    /** @var Action */
    protected $action;

    /** @var Action */
    protected $redirectAction;

    /**
     * @param EAVFormHelper       $formHelper
     * @param DoctrineHelper      $doctrineHelper
     * @param RoutingHelper       $routingHelper
     * @param EAVTemplatingHelper $templatingHelper
     */
    public function __construct(
        EAVFormHelper $formHelper,
        DoctrineHelper $doctrineHelper,
        RoutingHelper $routingHelper,
        EAVTemplatingHelper $templatingHelper
    ) {
        $this->formHelper = $formHelper;
        $this->doctrineHelper = $doctrineHelper;
        $this->routingHelper = $routingHelper;
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
        $form = $this->formHelper->getForm($this->action, $request, $data);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrineHelper->saveEntity($this->action, $data, $request->getSession());

            $parameters = $request->query->all();
            $parameters['success'] = 1;

            return $this->routingHelper->redirectToEntity($this->redirectAction, $data, $parameters);
        }

        return $this->templatingHelper->renderFormAction($this->action, $request, $family, $form, $data);
    }

    /**
     * @param Action $action
     */
    public function setRedirectAction(Action $action): void
    {
        $this->redirectAction = $action;
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
        $this->redirectAction = $action;
    }
}
