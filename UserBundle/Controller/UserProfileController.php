<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use CleverAge\EAVManager\UserBundle\Form\Type\UserProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Profile edition.
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserProfileController extends Controller
{
    use BaseControllerTrait;

    /**
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @throws \Exception
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(
            UserProfileType::class,
            $user,
            [
                'label' => 'eavmanager.user.profile.title',
                'action' => $this->getCurrentUri($request),
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
                'method' => 'post',
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('eavmanager_user.user.manager')->save($user);

            $this->addFlash('success', 'eavmanager.flash.edit.success');

            return $this->redirectToRoute(
                'eavmanager_user.profile.edit',
                [
                    'id' => $user->getId(),
                ]
            );
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
        ];
    }
}
