<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use Elastica\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;
use CleverAge\EAVManager\Component\Controller\DataControllerTrait;

/**
 * @Security("is_granted('ROLE_DATA_MANAGER')")
 */
class VariantController extends BaseAdminController
{
    use DataControllerTrait;

    /**
     * @Template()
     * @param AttributeInterface $attribute
     * @param DataInterface $parentData
     * @ParamConverter("parentData", class="CleverAgeEAVManagerEAVModelBundle:Data", options={"id" = "parentId"})
     * @param Request $request
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

        return $this->getViewParameters($request, $form, $parentData);
    }

    /**
     * @Template()
     * @param AttributeInterface $attribute
     * @param DataInterface $parentData
     * @ParamConverter("parentData", class="CleverAgeEAVManagerEAVModelBundle:Data", options={"id" = "parentId"})
     * @param FamilyInterface $family
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function createAction(AttributeInterface $attribute, DataInterface $parentData, FamilyInterface $family, Request $request)
    {
        if (!$parentData->getFamily()->hasAttribute($attribute->getCode())) {
            throw new UnexpectedValueException("Attribute does not belong to parent data's family");
        }
        /** @var DataInterface $data */
        $data = $family->createData();
        return $this->editAction($attribute, $parentData, $family, $data, $request);
    }

    /**
     * @Template()
     * @param AttributeInterface $attribute
     * @param int|DataInterface $parentId
     * @param FamilyInterface $family
     * @param DataInterface $data
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function editAction(AttributeInterface $attribute, $parentId, FamilyInterface $family, DataInterface $data, Request $request)
    {
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

        return $this->getViewParameters($request, $form, $data) + [
            'parentData' => $parentData,
        ];
    }

    /**
     * @Security("is_granted('delete', family) or is_granted('ROLE_SUPER_ADMIN')")
     * @Template()
     * @param AttributeInterface $attribute
     * @param int|DataInterface $parentId
     * @param FamilyInterface $family
     * @param DataInterface $data
     * @param Request $request
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function deleteAction(AttributeInterface $attribute, $parentId, FamilyInterface $family, DataInterface $data, Request $request)
    {
        $parentData = $this->getData($parentId);
        $this->checkAttributeFamily($parentData, $attribute);
        if ($data->getFamilyCode() !== $family->getCode()) {
            throw new UnexpectedValueException("Data's family does not match admin family");
        }

        $builder = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $data->getId()));
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->deleteEntity($data);
            return $this->redirectToEntity($parentData, 'edit');
        }

        return $this->getViewParameters($request, $form, $data) + [
            'parentData' => $parentData,
        ];
    }

    /**
     * Attach variant to original parent data if not already attached
     *
     * @param DataInterface $data
     * @param AttributeInterface $attribute
     * @param DataInterface $parentData
     * @throws \Exception
     */
    protected function attachVariant(DataInterface $data, AttributeInterface $attribute, DataInterface $parentData)
    {
        /** @var DataInterface $variant */
        foreach ($parentData->getValuesData($attribute) as $variant) {
            // Skip adding variant to parent data if already set
            if ($variant->getId() === $data->getId()) {
                return;
            }
        }
        $parentData->addValueData($attribute, $data);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * @param DataInterface $parentData
     * @param AttributeInterface $attribute
     * @throws UnexpectedValueException
     */
    protected function checkAttributeFamily(DataInterface $parentData, AttributeInterface $attribute)
    {
        if (!$parentData->getFamily()->hasAttribute($attribute->getCode())) {
            throw new UnexpectedValueException("Attribute {$attribute->getCode()} does not belong to parent data's family {$parentData->getFamilyCode()}");
        }
    }
}
