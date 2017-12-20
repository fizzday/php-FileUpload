<?php

namespace Fizzday\FileUpload;

use Fizzday\Facades\Facade;

/**
 * Class DB
 * @package Fizzday\FizzDB
 * @see \Fizzday\FizzDB\DBBuilder
 */
class FileFacade extends Facade
{
//    protected static $builder = '\Fizzday\Database\Builder';
    protected static $builder = 'FileUpload';
}
