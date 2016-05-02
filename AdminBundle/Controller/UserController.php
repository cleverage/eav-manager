<?php

namespace CleverAge\EAVManager\AdminBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use CleverAge\EAVManager\UserBundle\Entity\User;

/**
 * @Security("is_granted('ROLE_USER_MANAGER')")
 */
class UserController extends GenericAdminController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws \Exception
     */
    public function resetPasswordAction(Request $request, User $user)
    {
        $form = $this->createFormBuilder(null, $this->getDefaultFormOptions($request, $user->getId()))->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $password = $this->generatePassword();
            $user->setPlainPassword($password);
            $this->saveEntity($user);

            $this->get('eavmanager_user.mailer')->sendAdminResetPasswordEmailMessage($user, $password);

            if ($request->isXmlHttpRequest()) {
                return [
                    'dataId' => $user->getId(),
                    'isAjax' => 1,
                    'target' => $request->get('target'),
                    'success' => 1,
                ];
            }
            return $this->redirectToAdmin($this->admin, 'list');
        }

        return $this->renderAction($this->getViewParameters($request, $form, $user) + [
            'dataId' => $user->getId(),
        ]);
    }

    /**
     * @param int $count
     * @return string
     */
    public function generatePassword($count = 10)
    {
        $passwd = '';
        $possible = '23456789ABCDEFGHJKLMNPQRSTVWXYZabcdefghijkmnpqrstvwxyz';
        $i = 0;
        while ($i < $count) {
            $char = $possible[mt_rand(0, strlen($possible) - 1)];
            $passwd .= $char;
            $i++;
        }
        return $passwd;
    }

    /**
     * @param mixed $data
     * @throws \Exception
     */
    protected function saveEntity($data)
    {
        if (!$data instanceof UserInterface) {
            parent::saveEntity($data);
            return;
        }
        $this->get('fos_user.user_manager')->updateUser($data);

        $action = $this->admin->getCurrentAction();
        $this->addFlash('success', "eavmanager.flash.{$action->getCode()}.success");
    }
}
