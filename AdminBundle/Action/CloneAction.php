<?php

namespace CleverAge\EAVManager\AdminBundle\Action;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Security("(is_granted('read', data) and is_granted('create', _admin.getEntity()))")
 *
 * @property EditAction $editAction
 */
class CloneAction extends \Sidus\AdminBundle\Action\CloneAction
{
    /** @noinspection MagicMethodsValidityInspection */
    /** @noinspection PhpMissingParentConstructorInspection */
    /** @noinspection SenselessMethodDuplicationInspection */
    /**
     * Redefining the action with our EditAction
     *
     * @param EditAction                    $editAction
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EditAction $editAction, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->editAction = $editAction;
        $this->authorizationChecker = $authorizationChecker;
    }
}
