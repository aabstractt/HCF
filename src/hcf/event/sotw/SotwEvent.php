<?php
/*
* Copyright (C) Sergittos - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
*/

declare(strict_types=1);


namespace hcf\event\sotw;


use hcf\event\Event;

class SotwEvent extends Event {

    public function __construct() {
        parent::__construct(self::SOTW);
    }

}