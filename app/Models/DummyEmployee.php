<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DummyEmployee extends Model
{
    use HasFactory;

    protected $fillable = ['name','email','mobile','staff_id','place','dob','designation','request_id'];
}
