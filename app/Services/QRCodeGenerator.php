<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeGenerator
{
    public function generate(Restaurant $restaurant): string
    {
        // Asegurar que el directorio existe
        if (!Storage::disk('public')->exists('qrs')) {
            Storage::disk('public')->makeDirectory('qrs');
        }

        $filename = "qrs/{$restaurant->id}-" . time() . ".svg";
        $url = config('app.frontend_url', 'http://localhost:3000') . '/' . $restaurant->slug;

        // Generar QR en formato SVG (texto XML)
        $qrImage = QrCode::format('svg')
            ->size(500)
            ->margin(1)
            ->generate($url);

        // Guardar en disco público
        Storage::disk('public')->put($filename, $qrImage);

        return $filename;
    }
}
