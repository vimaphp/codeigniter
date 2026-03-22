<?php

namespace Vima\CodeIgniter\Support;

final class Utils
{
    public static function creatVimaUser(int|string $id): object
    {
        $user = new class () {
            public $id;
            public function vimaGetId()
            {
                return $this->id;
            }
        };

        $user->id = $id;

        return $user;
    }

    public static function truncate(mixed $text, int $limit, string $onNull = '[--NONE--]'): string
    {
        if (!$text || !is_string($text)) {
            return $onNull;
        }

        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit - 3) . '...';
    }
}
