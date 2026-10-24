<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    /*
    |--------------------------------------------------------------------------
    | Jam observasi yang dipakai pada dashboard
    |--------------------------------------------------------------------------
    | Dashboard hanya mengambil data pada jam berikut:
    | 08:00, 09:00, 12:00, 13:00, 16:00, 17:00
    */
    $targetHours = ['08:00', '09:00', '12:00', '13:00', '16:00', '17:00'];

    /*
    |--------------------------------------------------------------------------
    | Ambil 7 tanggal TERBARU yang benar-benar memiliki data
    | pada salah satu jam observasi target.
    |--------------------------------------------------------------------------
    */
    $latestDates = DB::table('artificial_intellegence')
        ->whereIn(DB::raw("TO_CHAR(date, 'HH24:MI')"), $targetHours)
        ->selectRaw('DATE(date) AS weather_date')
        ->distinct()
        ->orderByDesc('weather_date')
        ->limit(7)
        ->pluck('weather_date');

    /*
    |--------------------------------------------------------------------------
    | Jika belum ada data, tetap buka dashboard dengan empty state.
    |--------------------------------------------------------------------------
    */
    if ($latestDates->isEmpty()) {
        return view('home', [
            'weatherDays' => collect(),
            'targetHours' => $targetHours,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil seluruh record pada 7 tanggal tersebut, tetapi hanya
    | pada 6 jam observasi yang memang dibutuhkan.
    |--------------------------------------------------------------------------
    */
    $rows = DB::table('artificial_intellegence')
        ->whereIn(DB::raw('DATE(date)'), $latestDates->all())
        ->whereIn(DB::raw("TO_CHAR(date, 'HH24:MI')"), $targetHours)
        ->orderBy('date', 'asc')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Kelompokkan berdasarkan tanggal.
    |--------------------------------------------------------------------------
    */
    $groupedRows = $rows->groupBy(function ($row) {
        return Carbon::parse($row->date)->format('Y-m-d');
    });

    /*
    |--------------------------------------------------------------------------
    | Bentuk struktur data khusus untuk dashboard.
    |
    | - latest:
    |   Record TERAKHIR yang tersedia pada hari tersebut.
    |   Inilah yang dipakai untuk ringkasan klasifikasi harian.
    |
    | - averages:
    |   Rata-rata parameter aktual dari record yang tersedia
    |   pada 6 jam target. Dipakai untuk grafik 7 hari.
    |
    | - hourly:
    |   Data aktual untuk 08, 09, 12, 13, 16, 17.
    |   Kalau suatu jam tidak ada di database, available = false.
    |--------------------------------------------------------------------------
    */
    $weatherDays = collect($latestDates)
        ->sort()
        ->values()
        ->map(function ($date) use ($groupedRows, $targetHours) {
            $dayRows = collect($groupedRows->get($date, collect()))
                ->sortBy('date')
                ->values();

            $latest = $dayRows->last();

            $hourly = collect($targetHours)->map(function ($time) use ($dayRows) {
                $row = $dayRows->first(function ($item) use ($time) {
                    return Carbon::parse($item->date)->format('H:i') === $time;
                });

                if (!$row) {
                    return [
                        'time' => $time,
                        'available' => false,
                        'temperature' => null,
                        'humidity' => null,
                        'irradiance' => null,
                        'precipitation' => null,
                        'fuzzy_classification' => null,
                        'fuzzy_score' => null,
                    ];
                }

                return [
                    'time' => $time,
                    'available' => true,
                    'temperature' => (float) $row->temperature_c,
                    'humidity' => (float) $row->humidity_percent,
                    'irradiance' => (float) $row->irradiance,
                    'precipitation' => (float) $row->precipitation_mm_per_hr,
                    'fuzzy_classification' => $row->fuzzy_classification,
                    'fuzzy_score' => is_numeric($row->fuzzy_score)
                        ? (float) $row->fuzzy_score
                        : $row->fuzzy_score,
                ];
            })->values();

            return [
                'date' => $date,
                'date_label' => Carbon::parse($date)->format('d/m/Y'),

                'latest' => $latest ? [
                    'time' => Carbon::parse($latest->date)->format('H:i'),
                    'date_time' => Carbon::parse($latest->date)->format('d/m/Y H:i'),
                    'temperature' => (float) $latest->temperature_c,
                    'humidity' => (float) $latest->humidity_percent,
                    'irradiance' => (float) $latest->irradiance,
                    'precipitation' => (float) $latest->precipitation_mm_per_hr,
                    'fuzzy_classification' => $latest->fuzzy_classification,
                    'fuzzy_score' => is_numeric($latest->fuzzy_score)
                        ? (float) $latest->fuzzy_score
                        : $latest->fuzzy_score,
                ] : null,

                'averages' => [
                    'temperature' => $dayRows->isNotEmpty()
                        ? round((float) $dayRows->avg('temperature_c'), 2)
                        : null,

                    'humidity' => $dayRows->isNotEmpty()
                        ? round((float) $dayRows->avg('humidity_percent'), 2)
                        : null,

                    'irradiance' => $dayRows->isNotEmpty()
                        ? round((float) $dayRows->avg('irradiance'), 2)
                        : null,

                    'precipitation' => $dayRows->isNotEmpty()
                        ? round((float) $dayRows->avg('precipitation_mm_per_hr'), 2)
                        : null,
                ],

                'hourly' => $hourly,
            ];
        });

    return view('home', compact('weatherDays', 'targetHours'));
});