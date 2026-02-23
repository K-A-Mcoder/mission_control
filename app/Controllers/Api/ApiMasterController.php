<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Mission;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;

class ApiMasterController extends BaseController
{

    protected User $user_model;
    protected Role $role_model;
    protected Mission $mission_model;
    protected Notification $notif;

    public function __construct()
    {
        $this->user_model = new User();
        $this->role_model = new Role();
        $this->mission_model = new Mission(); 
        $this->notif = new Notification();

    }
}
