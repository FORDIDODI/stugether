<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\JWTService;
use App\Services\PaginationService;
use App\Services\DiscussionTreeService;
use App\Services\AuthUserService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * JWT service for issuing and verifying tokens.
     */
    public static function jwt(bool $getShared = true): JWTService
    {
        if ($getShared) {
            return static::getSharedInstance('jwt');
        }

        return new JWTService(env('JWT_SECRET', 'dev_secret_change_me'));
    }

    /**
     * Pagination helper service to attach meta to responses.
     */
    public static function paginationSvc(bool $getShared = true): PaginationService
    {
        if ($getShared) {
            return static::getSharedInstance('paginationSvc');
        }

        return new PaginationService();
    }

    /**
     * Discussion tree builder service for threaded discussions.
     */
    public static function discussionTree(bool $getShared = true): DiscussionTreeService
    {
        if ($getShared) {
            return static::getSharedInstance('discussionTree');
        }

        return new DiscussionTreeService();
    }

    /**
     * Authenticated user holder for the current request lifecycle.
     */
    public static function authUser(bool $getShared = true): AuthUserService
    {
        if ($getShared) {
            return static::getSharedInstance('authUser');
        }

        return new AuthUserService();
    }
}
