<?php

namespace App\Services;

use Illuminate\Support\Str;

class SlugGenerator
{
    public function generate(string $name, string $city): string
    {
        // Formato: {nombre}-{ciudad}-{random6}
        // Ejemplo: don-pepe-lima-a1b2c3
        $base = Str::slug($name) . '-' . Str::slug($city);
        return $base . '-' . Str::random(6);
    }
}
