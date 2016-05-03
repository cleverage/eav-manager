<?php

namespace CleverAge\EAVManager\UserBundle\Controller;

use CleverAge\EAVManager\Component\Controller\BaseControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserProfileController extends Controller
{
    use BaseControllerTrait;

    /**
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     * @throws \Exception
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm('eavmanager_user_profile', $user, [
            'label' => 'eavmanager.user.profile.title',
            'action' => $this->getCurrentUri($request),
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'method' => 'post',
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'eavmanager.flash.edit.success');

            return $this->redirectToRoute('eavmanager_user.profile.edit', [
                'id' => $user->getId(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'user' => $user,
        ];
    }

}
