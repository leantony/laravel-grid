<?php

namespace Leantony\Grid\Test\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $fillable = [
        'name',
        'description'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
