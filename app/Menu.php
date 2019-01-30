<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name','parent_id','position','slug'];

    public function parent()
    {
        return $this->belongsTo(Menu::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class,'parent_id');
    }
}
