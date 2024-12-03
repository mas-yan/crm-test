<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone'];

    public function managers()
    {
        return $this->hasMany(Manager::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
