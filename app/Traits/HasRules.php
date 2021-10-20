<?php

namespace App\Traits;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasRules
{
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }
}
