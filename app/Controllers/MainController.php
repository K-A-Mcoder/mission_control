<?php

namespace App\Controllers;

use App\Models\Mission;
use App\Models\Report;
use App\Models\Notification;
use App\Models\User;
use App\Models\Team;
use App\Models\Setting;
use App\Controllers\BaseController;

class MainController extends BaseController
{
    protected User $user_model;
    protected Mission $mission_model;
    protected Notification $notification_model;
    protected Report $report_model;
    protected Team $team_model;
    protected Setting $setting_model;

    public function __construct()
    {
        // Common initialization code for all controllers can go here
        $this->user_model = new User();
        $this->mission_model = new Mission();
        $this->notification_model = new Notification();
        $this->report_model = new Report();
        $this->team_model = new Team();
        $this->setting_model = new Setting();
    }
}
