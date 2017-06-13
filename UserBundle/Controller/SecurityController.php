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

use CleverAge\EAVManager\UserBundle\Form\Type\LostUserPasswordType;
use CleverAge\EAVManager\UserBundle\Form\Type\ResetUserPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Handles authentication and lost password as well as password creation
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SecurityController extends Controller
{
    /**
     * @Template()
     * @Route("/login", name="login")
     *
     * @throws \Exception
     *
     * @return array
     */
    public function loginAction()
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get('eavmanager_user.config.holder')->getHomeRoute());
        }
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'last_username' => $lastUsername,
            'error' => $error,
        ];
    }

    /**
     * @Template()
     * @Route("/login/lost-password", name="lost_password")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function lostPasswordAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get('eavmanager_user.config.holder')->getHomeRoute());
        }
        $form = $this->createForm(
            LostUserPasswordType::class,
            null,
            [
                'show_legend' => false,
            ]
        );
        $form->handleRequest($request);

        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('eavmanager_user.user.manager');
            $user = null;
            try {
                $user = $userManager->loadUserByUsername($form->get('username')->getData());
            } catch (UsernameNotFoundException $e) {
                $error = "Aucun utilisateur correspondant à cet email n'a été trouvé";
            }

            if ($user) {
                $userManager->requestNewPassword($user);

                $this->addFlash(
                    'success',
                    "La demande de changement de mot de passe à été envoyée à l'adresse saisie."
                );

                return $this->redirectToRoute('login');
            }
        }

        return [
            'form' => $form->createView(),
            'error' => $error,
        ];
    }

    /**
     * @Template()
     * @Route("/login/reset-password", name="reset_password")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function resetPasswordAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get('eavmanager_user.config.holder')->getHomeRoute());
        }
        $token = $request->query->get('token');
        if (!$token) {
            return $this->redirectToRoute('lost_password');
        }

        $userManager = $this->get('eavmanager_user.user.manager');
        $user = $userManager->loadUserByToken($token);
        if (!$user) {
            $this->addFlash('error', "Aucun utilisateur n'a été trouvé avec ce token");

            return $this->redirectToRoute('lost_password');
        }

        $form = $this->createForm(ResetUserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $userManager->setPlainTextPassword($user, $password);
            $userManager->save($user);

            $authenticationManager = $this->get('security.authentication.manager');
            $token = $authenticationManager->authenticate(
                new UsernamePasswordToken($user, $password, 'main', $user->getRoles())
            );
            $tokenStorage = $this->get('security.token_storage');
            $tokenStorage->setToken($token);

            return $this->redirectToRoute($this->get('eavmanager_user.config.holder')->getHomeRoute());
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }
}
