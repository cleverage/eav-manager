<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\Component\Controller\DataControllerTrait;
use CleverAge\EAVManager\EAVModelBundle\Entity\Data;
use Doctrine\ORM\EntityManager;
use Elastica\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\AdminBundle\Routing\AdminRouter;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 */
class VariantController extends AbstractAdminController
{
    use DataControllerTrait;

    /**
     * @param AttributeInterface $attribute
     * @param DataInterface      $parentData
     * @ParamConverter("parentData", class="CleverAge\EAVManager\EAVModelBundle\Entity\Data", options={"id" = "parentId"})
     * @param Request            $request
     * @return Response
     * @throws \Exception
     */
    public function selectAction(AttributeInterface $attribute, DataInterface $parentData, Request $request)
    {
        $form = $this->getForm($request, null, [
            'attribute' => $attribute,
            'parent_data' => $parentData,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var FamilyInterface $family */
            $family = $form->get('family')->getData();
            $parameters = [
                'attribute' => $attribute->getCode(),
                'familyCode' => $family->getCode(),
                'parentId' => $parentData->getId(),
            ];
            if ($request->get('target')) {
                $parameters['target'] = $request->get('target');
            }

            return $this->redirectToAdmin($this->admin->getCode(), 'create', $parameters);
        }

        $parameters = $this->getViewParameters($request, $form, null);
        $parameters['listPath'] = $this->getParentDataPath($parentData);

        return $this->renderAction($parameters);
    }

    /**
     * @param AttributeInterface $attribute
     * @param DataInterface      $parentData
     * @ParamConverter("parentData", class="CleverAge\EAVManager\EAVModelBundle\Entity\Data", options={"id" = "parentId"})
     * @param FamilyInterface    $family
     * @param Request            $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(
        AttributeInterface $attribute,
        DataInterface $parentData,
        FamilyInterface $family,
        Request $request
    ) {
        if (!$parentData->getFamily()->hasAttribute($attribute->getCode())) {
            throw new UnexpectedValueException("Attribute does not belong to parent data's family");
        }
        /** @var DataInterface $data */
        $data = $family->createData();
        $data->setParent($parentData);

        return $this->editAction($attribute, $parentData, $family, $data, $request);
    }

    /**
     * @param AttributeInterface $attribute
     * @param int|DataInterface  $parentId
     * @param FamilyInterface    $family
     * @param DataInterface      $data
     * @param Request            $request
     * @return array
     * @throws \Exception
     */
    public function editAction(
        AttributeInterface $attribute,
        $parentId,
        FamilyInterface $family,
        DataInterface $data,
        Request $request
    ) {
        $parentData = $this->getData($parentId);
        $this->checkAttributeFamily($parentData, $attribute);
        if ($data->getFamilyCode() !== $family->getCode()) {
            throw new UnexpectedValueException("Data's family does not match admin family");
        }
        $form = $this->getForm($request, $data, [
            'parent_data' => $parentData,
            'attribute' => $attribute,
            'family' => $family,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data->setParent($parentData);
            $this->saveEntity($data);
            $this->attachVariant($data, $attribute, $parentData);

            $parameters = [
                'attribute' => $attribute->getCode(),
                'parentId' => $parentData->getId(),
                'success' => 1,
            ];
            if ($request->get('target')) {
                $parameters['target'] = $request->get('target');
            }

            return $this->redirectToEntity($data, 'edit', $parameters);
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data));
    }

    /**
     * @Security("is_granted('delete', family) or is_granted('ROLE_DATA_ADMIN')")
     * @param AttributeInterface $attribute
     * @param int|DataInterface  $parentId
     * @param FamilyInterface    $family
     * @param DataInterface      $data
     * @param Request            $request
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(
        AttributeInterface $attribute,
        $parentId,
        FamilyInterface $family,
        DataInterface $data,
        Request $request
    ) {
        $parentData = $this->getData($parentId);
        $this->checkAttributeFamily($parentData, $attribute);
        if ($data->getFamilyCode() !== $family->getCode()) {
            throw new UnexpectedValueException("Data's family does not match admin family");
        }

        $builder = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $data->getId()));
        $form = $builder->getForm();
        $dataId = $data->getId();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->deleteEntity($data);

            return $this->redirectToEntity($parentData, 'edit');
        }

        return $this->renderAction($this->getViewParameters($request, $form, $data) + [
            'dataId' => $dataId,
        ]);
    }

    /**
     * Attach variant to original parent data if not already attached
     *
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     * @param DataInterface      $parentData
     * @throws \Exception
     */
    protected function attachVariant(DataInterface $data, AttributeInterface $attribute, DataInterface $parentData)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($parentData instanceof Data) {
            $parentData->setUpdatedAt(new \DateTime()); // Force update
            $em->flush($parentData);
        }

        // Only if variant is not part of data values
        /** @var DataInterface $variant */
        foreach ($parentData->getValuesData($attribute) as $variant) {
            // Skip adding variant to parent data if already set
            if ($variant->getId() === $data->getId()) {
                return;
            }
        }
        $parentData->addValueData($attribute, $data);
        $em->flush();
    }

    /**
     * @param DataInterface      $parentData
     * @param AttributeInterface $attribute
     * @throws UnexpectedValueException
     */
    protected function checkAttributeFamily(DataInterface $parentData, AttributeInterface $attribute)
    {
        if (!$parentData->getFamily()->hasAttribute($attribute->getCode())) {
            throw new UnexpectedValueException("Attribute {$attribute->getCode()} does not belong to parent data's family {$parentData->getFamilyCode()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getAdminListPath($data = null, array $parameters = [])
    {
        if ($data instanceof DataInterface && $data->getParent()) {
            return $this->getParentDataPath($data->getParent(), $parameters);
        }

        return null;
    }

    /**
     * @param DataInterface $parentData
     * @param array         $parameters
     * @return string
     * @throws \Exception
     */
    protected function getParentDataPath(DataInterface $parentData, array $parameters = [])
    {
        /** @var AdminRouter $adminRouter */
        $adminRouter = $this->get('sidus_admin.routing.admin_router');

        return $adminRouter->generateEntityPath($parentData, 'edit', $parameters);
    }
}
