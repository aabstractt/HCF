<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event;


abstract class Event {

    public const SOTW = 0;

    private int $id;

    private int $time = 0;
    private bool $enabled = false;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTime(): int {
        return $this->time;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setTime(int $time): void {
        $this->time = $time;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function decreaseTime(): void {
        $this->time--;
    }

}