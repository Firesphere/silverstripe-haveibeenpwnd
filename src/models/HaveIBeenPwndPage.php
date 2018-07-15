<?php

namespace Firesphere\HaveIBeenPwnd\Models;

use Firesphere\HaveIBeenPwnd\Controllers\HaveIBeenPwndPageController;
use Page;

/**
 * Class \Firesphere\HaveIBeenPwnd\Models\HaveIBeenPwndPage
 *
 */
class HaveIBeenPwndPage extends Page
{
    private static $table_name = 'HaveIBeenPwndPage';

    /**
     * Get the controller name for this page
     *
     * @return string
     */
    public function getControllerName()
    {
        return HaveIBeenPwndPageController::class;
    }
}
