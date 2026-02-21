<?php

namespace App\Controllers;

use App\Models\Mission;
use App\Models\User;
use Etus\Framework\Controllers\AbstractController;

class BaseController extends AbstractController
{
    protected User $user_model;
    protected Mission $mission;
    public function __construct()
    {
        // Common initialization code for all controllers can go here
        $this->user_model = new User();
        $this->mission = new Mission();
    }
}
