<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['image_path', 'sort_order'])]
class SliderImage extends Model
{
    public function url(): string
    {
        $path = str_replace('\\', '/', (string) $this->image_path);
        $path = ltrim($path, '/');

        return '/storage/'.$path;
    }
}
