<?php

use Illuminate\Support\Arr;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ppath = storage_path('app/psgc/refprovince.csv');
$cpath = storage_path('app/psgc/refcitymun.csv');

foreach ([$ppath,$cpath] as $p) {
    if (!file_exists($p)) { echo "Missing file: $p\n"; exit(1); }
}

/** helper: read CSV and return [headersArray, rowsArrayOfArrays] */
$readCsv = function(string $path): array {
    $h = fopen($path, 'r');
    if (!$h) { throw new RuntimeException("Cannot open $path"); }
    $head = fgetcsv($h, 0, ',', '"', '\\');
    $head = array_map(fn($x) => trim(preg_replace("/^\xEF\xBB\xBF/", "", (string)$x)), $head);
    $rows = [];
    while (($r = fgetcsv($h, 0, ',', '"', '\\')) !== false) {
        $rows[] = $r;
    }
    fclose($h);
    return [$head, $rows];
};

/** 1) provinces map: provCode -> regCode */
[$pHead, $pRows] = $readCsv($ppath);
$pIdx = array_flip($pHead);
$needP = ['provCode','regCode'];
foreach ($needP as $col) {
    if (!isset($pIdx[$col])) {
        echo "refprovince.csv header mismatch. Got: ".json_encode($pHead)."\n";
        exit(1);
    }
}
$prov2reg = [];
foreach ($pRows as $r) {
    $prov = $r[$pIdx['provCode']] ?? null;
    $reg  = $r[$pIdx['regCode']]  ?? null;
    if ($prov !== null && $reg !== null && $prov !== '') {
        $prov2reg[(string)$prov] = (string)$reg;
    }
}
if (!$prov2reg) { echo "No province→region mapping found.\n"; exit(1); }

/** 2) cities: code, name, province_code, region_code (derived) */
[$cHead, $cRows] = $readCsv($cpath);
$cIdx = array_flip($cHead);

$codeCol = $cIdx['citymunCode'] ?? ($cIdx['psgcCode'] ?? null);
$nameCol = $cIdx['citymunDesc'] ?? ($cIdx['name'] ?? null);
$provCol = $cIdx['provCode']    ?? null;

if ($codeCol === null || $nameCol === null || $provCol === null) {
    echo "refcitymun.csv header mismatch. Got: ".json_encode($cHead)."\n";
    exit(1);
}

$rows = [];
foreach ($cRows as $r) {
    $code = $r[$codeCol] ?? null;
    $name = $r[$nameCol] ?? null;
    $prov = $r[$provCol] ?? null;
    if (!$code || !$name) continue;

    $reg  = $prov !== null && isset($prov2reg[(string)$prov]) ? $prov2reg[(string)$prov] : null;
    if (!$reg) continue; // skip if cannot map region

    $rows[] = [
        'code'          => (string)$code,
        'name'          => trim((string)$name),
        'province_code' => $prov !== '' ? (string)$prov : null,
        'region_code'   => (string)$reg,
    ];
}

if (!$rows) { echo "No city rows parsed.\n"; exit(1); }

$up = 0;
foreach (array_chunk($rows, 1000) as $chunk) {
    DB::table('psgc_cities')->upsert($chunk, ['code'], ['name','province_code','region_code']);
    $up += count($chunk);
}

echo "upserted_cities={$up}\n";
