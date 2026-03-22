<?php

namespace Vima\CodeIgniter\Support;

final class Utils
{
    public static function creatVimaUser(int|string $id): object
    {
        return new class () {
            public $id;
            public function vimaGetId()
            {
                return $this->id; }
        };
    }
}
