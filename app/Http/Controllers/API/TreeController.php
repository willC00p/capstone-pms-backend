<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Teams;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Http\Request;

class TreeController extends BaseController
{
    public function index()
    {
        $teams = TeamUser::whereNull('lead_id')->with('team')->get();

        return $this->sendResponse($teams, "Teams successfully retrived");
    }

    public function show(Teams $lead)
    {
        return $this->sendResponse($lead->members, "{$lead->name} has been retrieved.");
    }
}
