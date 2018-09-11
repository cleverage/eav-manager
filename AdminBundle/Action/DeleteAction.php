<?php

namespace CleverAge\EAVManager\AdminBundle\Action;

use CleverAge\EAVManager\AdminBundle\Templating\TemplatingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sidus\AdminBundle\Action\ActionInjectableInterface;
use Sidus\AdminBundle\Admin\Action;
use Sidus\AdminBundle\Doctrine\DoctrineHelper;
use CleverAge\EAVManager\AdminBundle\Form\FormHelper;
use Sidus\AdminBundle\Routing\RoutingHelper;
use Sidus\EAVModelBundle\Doctrine\IntegrityConstraintManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('delete', data)")
 */
class DeleteAction implements ActionInjectableInterface
{
    /** @var FormHelper */
    protected $formHelper;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RoutingHelper */
    protected $routingHelper;

    /** @var TemplatingHelper */
    protected $templatingHelper;

    /** @var IntegrityConstraintManager */
    protected $integrityConstraintManager;

    /** @var Action */
    protected $action;

    /**
     * @param FormHelper                 $formHelper
     * @param DoctrineHelper             $doctrineHelper
     * @param RoutingHelper              $routingHelper
     * @param TemplatingHelper           $templatingHelper
     * @param IntegrityConstraintManager $integrityConstraintManager
     */
    public function __construct(
        FormHelper $formHelper,
        DoctrineHelper $doctrineHelper,
        RoutingHelper $routingHelper,
        TemplatingHelper $templatingHelper,
        IntegrityConstraintManager $integrityConstraintManager
    ) {
        $this->formHelper = $formHelper;
        $this->doctrineHelper = $doctrineHelper;
        $this->routingHelper = $routingHelper;
        $this->templatingHelper = $templatingHelper;
        $this->integrityConstraintManager = $integrityConstraintManager;
    }

    /**
     * @ParamConverter(name="data", converter="sidus_admin.entity")
     *
     * @param Request $request
     * @param mixed   $data
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function __invoke(Request $request, $data): Response
    {
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
                            $this->templatingHelper->getViewParameters($this->action, $request),
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
