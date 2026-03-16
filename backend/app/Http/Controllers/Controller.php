<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }
}
