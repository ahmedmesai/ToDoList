<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'title', 'content', 'is_completed', 'created_at', 'task_date'];

    // protected $casts = [
    //     'task_date' => 'datetime:d/m/Y', // Change your format
    // ];
}
