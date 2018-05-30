<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\AssetBundle\Form\Type\MediaBrowserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\EAVBootstrapBundle\Form\Type\ComboDataSelectorType;
use Sidus\EAVBootstrapBundle\Form\Type\SwitchType;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enrich the WYSIWYG experience with in-place data selectors.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class WysiwygController extends Controller
{
    /**
     * @param Request $request
     * @param string  $configName
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function selectDataAction(Request $request, $configName)
    {
        $formOptions = [];
        $selectorConfig = $this->getParameter('cleverage_eavmanager.configuration')['wysiwyg'];
        if (array_key_exists($configName, $selectorConfig)) {
            $formOptions = $selectorConfig[$configName];
        }
        $formData = [
            'data' => $this->getData($request),
        ];
        $builder = $this->createFormBuilder(
            $formData,
            [
                'show_legend' => false,
            ]
        );
        $builder->add('data', ComboDataSelectorType::class, $formOptions);

        $form = $builder->getForm();
        $form->handleRequest($request);

        return $this->render(
            'CleverAgeEAVManagerAdminBundle:Wysiwyg:select'.ucfirst($configName).'.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Template("@CleverAgeEAVManagerAdmin/Wysiwyg/selectMedia.html.twig")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function selectMediaAction(Request $request)
    {
        $formData = [
            'data' => $this->getData($request),
            'filter' => $request->get('dataFilter'),
            'responsive' => '1' === (string) $request->get('dataResponsive'),
        ];
        $builder = $this->createFormBuilder(
            $formData,
            [
                'show_legend' => false,
            ]
        );
        $builder->add(
            'data',
            MediaBrowserType::class,
            [
                'allowed_families' => ['Image'],
            ]
        );

        $filterConfig = $this->get('liip_imagine.filter.manager')->getFilterConfiguration()->all();
        $choices = array_combine(array_keys($filterConfig), array_keys($filterConfig));
        $builder->add(
            'filter',
            ChoiceType::class,
            [
                'choices' => $choices,
            ]
        );

        $builder->add(
            'responsive',
            SwitchType::class,
            [
                'label' => 'Responsive',
                'required' => false,
            ]
        );

        $form = $builder->getForm();
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     *
     * @return DataInterface|null
     */
    protected function getData(Request $request)
    {
        $data = null;
        if ($request->query->has('dataId')) {
            $data = $this->get(DataRepository::class)->find($request->query->get('dataId'));
        }

        return $data;
    }
}
