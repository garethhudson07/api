<?php

namespace Api\Events\Contracts;

use Closure;

interface Emitter
{
    public function extend(): Emitter;

    public function bubble(Emitter $parent): Emitter;

    public function emit(string $event, ?Closure $closure = null): Event;

    public function fire(Event $event): Event;

    public function listen(string $event, $listener);
}
