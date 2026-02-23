<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Role;

class AuthMasterController extends BaseController
{
    protected array $middleware = ['guest'];
    protected User $user_model;
    protected Role $role_model;

    public function __construct()
    {
        $this->user_model = new User();
        $this->role_model = new Role();
    }
}
