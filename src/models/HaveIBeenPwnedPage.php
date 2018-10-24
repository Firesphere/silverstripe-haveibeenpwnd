<?php

namespace Firesphere\HaveIBeenPwned\Models;

use Firesphere\HaveIBeenPwned\Controllers\HaveIBeenPwnedPageController;
use Page;

/**
 * Class \Firesphere\HaveIBeenPwned\Models\HaveIBeenPwnedPage
 *
 */
class HaveIBeenPwnedPage extends Page
{
    private static $table_name = 'HaveIBeenPwnedPage';

    /**
     * Get the controller name for this page
     *
     * @return string
     */
    public function getControllerName()
    {
        return HaveIBeenPwnedPageController::class;
    }

    public function canCreate($member = null, $context = array())
    {
        // This page should only exist once
        if (static::get()->count()) {
            return false;
        }

        return parent::canCreate($member, $context);
    }
}
