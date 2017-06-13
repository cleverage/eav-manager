<?php

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
 * Gestion de l'authentification et de la perte/création de mot de passe.
 *
 * @todo translate everything !
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
