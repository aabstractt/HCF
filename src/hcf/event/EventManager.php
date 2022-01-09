<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event;


use hcf\event\sotw\SotwEvent;

class EventManager {

    /** @var Event[] */
    private array $events;

    public function __construct() {
        $this->registerEvent(new SotwEvent());
    }

    /**
     * @return Event[]
     */
    public function getEvents(): array {
        return $this->events;
    }

    public function getEventById(int $id): ?Event {
        return $this->events[$id] ?? null;
    }

    private function registerEvent(Event $event): void {
        $this->events[$event->getId()] = $event;
    }

}