<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\AssetBundle\Form\Type\MediaBrowserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sidus\EAVBootstrapBundle\Form\Type\ComboDataSelectorType;
use Sidus\EAVBootstrapBundle\Form\Type\SwitchType;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

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
     * @return array
     *
     * @throws \InvalidArgumentException
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
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function selectMediaAction(Request $request)
    {
        $formData = [
            'data' => $this->getData($request),
            'filter' => $request->get('dataFilter'),
            'responsive' => (string) $request->get('dataResponsive') === '1',
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
                'allowed_families' => 'Image',
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
     * @return DataInterface|null
     *
     * @throws \InvalidArgumentException
     */
    protected function getData(Request $request)
    {
        $data = null;
        if ($request->query->has('dataId')) {
            $dataClass = $this->getParameter('sidus_eav_model.entity.data.class');
            $dataRepository = $this->get('doctrine')->getRepository($dataClass);
            $data = $dataRepository->find($request->query->get('dataId'));
        }

        return $data;
    }
}
