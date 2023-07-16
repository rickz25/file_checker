<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThresholdSettings extends Model
{
    use HasFactory;
    protected $table = 'threshold';
    public $timestamps = false;
    public $primaryKey = 'id';
}
