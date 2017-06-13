<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Security("is_granted('ROLE_USER_MANAGER')")
 */
class UserController extends GenericAdminController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function resetPasswordAction(Request $request, User $user)
    {
        $form = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $user->getId()))->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('eavmanager_user.user.manager')->requestNewPassword($user);
            $this->saveEntity($user);

            if ($request->isXmlHttpRequest()) {
                return array_merge(
                    $this->getViewParameters($request, $form, $user),
                    [
                        'dataId' => $user->getId(),
                        'success' => 1,
                    ]
                );
            }

            return $this->redirectToAction('list');
        }

        return $this->renderAction(
            array_merge(
                $this->getViewParameters($request, $form, $user),
                [
                    'dataId' => $user->getId(),
                ]
            )
        );
    }

    /**
     * @param int $count
     *
     * @return string
     */
    protected function generatePassword($count = 10)
    {
        $passwd = '';
        $possible = '23456789ABCDEFGHJKLMNPQRSTVWXYZabcdefghijkmnpqrstvwxyz';
        $i = 0;
        while ($i < $count) {
            $char = $possible[mt_rand(0, strlen($possible) - 1)];
            $passwd .= $char;
            ++$i;
        }

        return $passwd;
    }

    /**
     * @param User $user
     *
     * @throws \Exception
     */
    protected function saveEntity($user)
    {
        if (!$user instanceof UserInterface) {
            parent::saveEntity($user);

            return;
        }

        $this->get('eavmanager_user.user.manager')->save($user);

        $action = $this->admin->getCurrentAction();
        $this->addFlash('success', "eavmanager.flash.{$action->getCode()}.success");
    }
}
