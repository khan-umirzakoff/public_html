<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title', 'about', 'info', 'img', 'youtube', 'cat_id', 'embedding'
    ];
}
