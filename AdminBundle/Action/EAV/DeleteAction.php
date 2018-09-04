<?php

namespace CleverAge\EAVManager\AdminBundle\Action\EAV;

use CleverAge\EAVManager\AdminBundle\Templating\EAVAdminTemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use CleverAge\EAVManager\AdminBundle\Form\EAVAdminFormHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\EAVModelBundle\Doctrine\IntegrityConstraintManager;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Exception\WrongFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('delete', data)")
 */
class DeleteAction implements ActionInjectableInterface
{
    /** @var EAVAdminFormHelper */
    protected $formHelper;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RoutingHelper */
    protected $routingHelper;

    /** @var EAVAdminTemplatingHelper */
    protected $templatingHelper;

    /** @var IntegrityConstraintManager */
    protected $integrityConstraintManager;

    /** @var Action */
    protected $action;

    /**
     * @param EAVAdminFormHelper         $formHelper
     * @param DoctrineHelper             $doctrineHelper
     * @param RoutingHelper              $routingHelper
     * @param EAVAdminTemplatingHelper   $templatingHelper
     * @param IntegrityConstraintManager $integrityConstraintManager
     */
    public function __construct(
        EAVAdminFormHelper $formHelper,
        DoctrineHelper $doctrineHelper,
        RoutingHelper $routingHelper,
        EAVAdminTemplatingHelper $templatingHelper,
        IntegrityConstraintManager $integrityConstraintManager
    ) {
        $this->formHelper = $formHelper;
        $this->doctrineHelper = $doctrineHelper;
        $this->routingHelper = $routingHelper;
        $this->templatingHelper = $templatingHelper;
        $this->integrityConstraintManager = $integrityConstraintManager;
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
    public function __invoke(Request $request, DataInterface $data, FamilyInterface $family): Response
    {
        WrongFamilyException::assertFamily($data, $family->getCode());
        $constrainedEntities = $this->integrityConstraintManager->getEntityConstraints($data);

        $dataId = $data->getId();
        $form = $this->formHelper->getEmptyForm($this->action, $request, $data);

        if (0 === \count($constrainedEntities)) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->doctrineHelper->deleteEntity($this->action, $data, $request->getSession());

                if ($request->isXmlHttpRequest()) {
                    return $this->templatingHelper->renderAction(
                        $this->action,
                        array_merge(
                            $this->templatingHelper->getViewParameters($this->action, $request, $family),
                            [
                                'dataId' => $dataId,
                                'success' => 1,
                            ]
                        )
                    );
                }

                return $this->routingHelper->redirectToAction(
                    $this->action->getAdmin()->getAction(
                        $this->action->getOption('redirect_action', 'list')
                    )
                );
            }
        }

        return $this->templatingHelper->renderFormAction(
            $this->action,
            $request,
            $family,
            $form,
            $data,
            [
                'dataId' => $dataId,
                'constrainedEntities' => $constrainedEntities,
            ]
        );
    }

    /**
     * @param Action $action
     */
    public function setAction(Action $action): void
    {
        $this->action = $action;
    }
}
