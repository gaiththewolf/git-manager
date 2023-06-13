<?php

namespace Gaiththewolf\GitManager;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Gaiththewolf\GitManager\Skeleton\SkeletonClass
 */
class GitManagerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'git-manager';
    }
}
