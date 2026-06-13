<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassroomStudent extends Pivot
{
    use HasUuidPrimaryKey;

    protected $table = 'classroom_student';
}
