<?php

namespace Vima\CodeIgniter\Support;

use Vima\Core\Entities\User;

final class Utils
{
    public static function creatVimaUser(int|string $id): User
    {
        return User::define($id);
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
