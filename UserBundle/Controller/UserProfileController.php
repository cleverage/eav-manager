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
