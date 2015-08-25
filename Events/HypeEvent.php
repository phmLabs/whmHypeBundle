<?php

namespace whm\HypeBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use whm\HypeBundle\Entity\Hype;

class HypeEvent extends Event
{
    private $hype;

    public function __construct(Hype $hype)
    {
        $this->hype = $hype;
    }

    /**
     * @return Hype
     */
    public function getHype()
    {
        return $this->hype;
    }
}