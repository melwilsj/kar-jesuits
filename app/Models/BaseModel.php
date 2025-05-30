<?php

namespace App\Models;

use App\Traits\HasTimeTravel;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasTimeTravel;
} 