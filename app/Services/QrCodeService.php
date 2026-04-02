<?php

namespace App\Services;

use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generateForTable(RestaurantTable $table): string
    {
        $url = url('/order/' . $table->token);
        $filename = 'qrcodes/table_' . $table->id . '.svg';

        $qrCode = QrCode::format('svg')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        Storage::disk('public')->put($filename, $qrCode);

        $table->update(['qr_code_path' => $filename]);

        return $filename;
    }

    public function getPublicUrl(RestaurantTable $table): ?string
    {
        if (!$table->qr_code_path) {
            return null;
        }

        return Storage::disk('public')->url($table->qr_code_path);
    }
}
