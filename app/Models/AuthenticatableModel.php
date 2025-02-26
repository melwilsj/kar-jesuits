<?php

namespace App\Models;

use App\Traits\HasTimeTravel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AuthenticatableModel extends Authenticatable
{
    use HasTimeTravel;
} 