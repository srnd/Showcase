<?php

namespace Showcase\Jobs;

use Opis\Closure\SerializableClosure;

class QueueableClosure extends SerializableClosure
{
    public function handle() {
        call_user_func_array($this->closure, func_get_args());
    }
}
