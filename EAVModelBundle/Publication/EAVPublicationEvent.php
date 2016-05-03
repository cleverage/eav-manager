<?php

namespace CleverAge\EAVManager\EAVModelBundle\Publication;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\PublishingBundle\Entity\PublishableInterface;
use Sidus\PublishingBundle\Event\PublicationEvent;

class EAVManagerPublicationEvent extends PublicationEvent
{
    public $family;

    /**
     * @param PublishableInterface $data
     * @param string               $event
     */
    public function __construct(PublishableInterface $data, $event)
    {
        parent::__construct($data, $event);

        if ($data instanceof DataInterface) {
            $this->family = $data->getFamilyCode();
        }
    }
}
