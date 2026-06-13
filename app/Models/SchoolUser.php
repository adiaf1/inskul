<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SchoolUser extends Pivot
{
    use HasUuidPrimaryKey;

    protected $table = 'school_user';
}
