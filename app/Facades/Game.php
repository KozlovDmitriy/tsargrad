<?php
/**
 * Created by PhpStorm.
 * User: �����
 * Date: 15.11.2015
 * Time: 23:44
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Game extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'game';
    }
}