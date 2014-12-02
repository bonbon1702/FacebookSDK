<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 12/2/2014
 * Time: 4:11 PM
 */

namespace bonbon1702\Facebook\Facades;

use Illuminate\Support\Facades\Facade;

class Fb extends Facade{
    protected static function getFacadeAccessor()
    {
        return 'bonbon1702.facebooksdk';
    }
} 