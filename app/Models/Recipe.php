<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'menu_item_id',
        'name',
        'description',
        'prep_time_minutes',
        'cook_time_minutes',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function steps()
    {
        return $this->hasMany(RecipeStep::class);
    }
}
