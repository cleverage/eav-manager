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

use CleverAge\EAVManager\UserBundle\Form\Type\LostUserPasswordType;
use CleverAge\EAVManager\UserBundle\Form\Type\ResetUserPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use CleverAge\EAVManager\UserBundle\Configuration\Configuration;

/**
 * Handles authentication and lost password as well as password creation
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class SecurityController extends Controller
{
    /**
     * @Template("@CleverAgeEAVManagerUser/Security/login.html.twig")
     *
     * @Route("/login", name="login")
     *
     * @throws \Exception
     *
     * @return Response|array
     */
    public function loginAction()
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get(Configuration::class)->getHomeRoute());
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
     * @Template("@CleverAgeEAVManagerUser/Security/lostPassword.html.twig")
     *
     * @Route("/login/lost-password", name="lost_password")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response|array
     */
    public function lostPasswordAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get(Configuration::class)->getHomeRoute());
        }
        $form = $this->createForm(
            LostUserPasswordType::class,
            null,
            [
                'show_legend' => false,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
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
                $error = 'lost_password.not_found';
            }

            if ($user) {
                $userManager->requestNewPassword($user);

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('lost_password.password_changed', [], 'security')
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
     * @Template("@CleverAgeEAVManagerUser/Security/resetPassword.html.twig")
     *
     * @Route("/login/reset-password", name="reset_password")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return Response|array
     */
    public function resetPasswordAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->get(Configuration::class)->getHomeRoute());
        }
        $token = $request->query->get('token');
        if (!$token) {
            return $this->redirectToRoute('lost_password');
        }

        $userManager = $this->get('eavmanager_user.user.manager');
        $user = $userManager->loadUserByToken($token);
        if (!$user) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans('reset_password.token_not_found', [], 'security')
            );

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

            return $this->redirectToRoute($this->get(Configuration::class)->getHomeRoute());
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }
}
