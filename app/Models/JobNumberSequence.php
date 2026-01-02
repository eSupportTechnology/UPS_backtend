<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobNumberSequence extends Model
{
    use HasUuids;

    protected $table = 'job_number_sequences';
    public $incrementing = false;

    protected $fillable = ['year', 'sequence', 'prefix'];
}
