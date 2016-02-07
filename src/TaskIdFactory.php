<?php

namespace Kyew;

class TaskIdFactory
{
    public static function new(): string
    {
        return bin2hex(random_bytes(32));
    }
}
