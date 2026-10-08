<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialImportController extends Controller
{
    public function index()
    {
        return view('master.materials.import', [
            'title' => 'Import Material',
        ]);
    }

    /**
     * Download template CSV
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-material.csv"',
        ];

        $columns = [
            'kode',
            'kode_bahan',
            'nama',
            'spesifikasi',
            'category',
            'unit',
            'costing_method',
            'volume_type',
            'panjang_standar',
            'lebar_standar',
            'tinggi_standar',
            'berat_standar',
            'volume_standar',
            'price',
            'yield_percent',
            'min_stock',
            'max_stock',
            'location',
            'notes',
        ];

        // Contoh data — kode dikosongkan untuk auto-generate
        $samples = [
            // Material per unit
            ['', 'A8-18-0', 'Kayu Pinus 164x120x18', 'Pinus, 164 x 120 x 18 mm', 'Kayu', 'pcs', 'per_unit', '', '', '', '', '', '', '100000', '100', '10', '100', 'Rak A-01', 'Kayu kualitas A'],
            ['', 'J1-9-0', 'Kardus Cokelat', 'Cokelat, Single Corrugated', 'Kemasan', 'pcs', 'per_unit', '', '', '', '', '', '', '5000', '100', '50', '500', 'Rak B-01', ''],
            // Material per area (MDF)
            ['', '', 'MDF 8mm', 'MDF 8mm single side melamin', 'Kayu', 'lembar', 'per_area', '', '2400', '1200', '8', '', '', '500000', '85', '5', '50', 'Rak A-02', 'Papan MDF'],
            // Material per length (Kabel)
            ['', '', 'Kabel Listrik 2x1.5', 'Kabel listrik 2x1.5 mm', 'Elektrik', 'roll', 'per_length', '', '100000', '', '', '', '', '200000', '100', '2', '20', 'Rak C-01', ''],
            // Material per weight (Tepung)
            ['', '', 'Tepung Terigu', 'Protein sedang', 'Bahan', 'karung', 'per_weight', '', '', '', '', '25000', '', '300000', '100', '5', '30', 'Gudang Bahan', ''],
            // Material per volume KOTAK (Kayu Balok)
            ['', '', 'Kayu Balok 5x10', 'Kayu balok 50x100 mm', 'Kayu', 'batang', 'per_volume', 'kotak', '4000', '50', '100', '', '', '150000', '100', '5', '50', 'Rak A-03', ''],
            // Material per volume CAIR (Varnish)
            ['', 'F5-2-1', 'Varnish Doff 714D', 'Varnish Doff 714D', 'Finishing', 'kaleng', 'per_volume', 'cair', '', '', '', '', '5000', '500000', '100', '2', '20', 'Rak D-01', 'Varnish 5 liter'],
            // Material per volume CAIR (Tinta Mimaki)
            ['', 'F6-4-0', 'Tinta Mimaki Cyan', 'Tinta Mimaki Cyan 1 liter', 'Tinta', 'botol', 'per_volume', 'cair', '', '', '', '', '1000', '800000', '100', '2', '15', 'Rak D-02', 'Tinta printer Mimaki'],
            // Contoh kode manual
            ['MAT-CUSTOM-01', 'CUSTOM-001', 'Material Custom', 'Contoh kode manual', 'Custom', 'pcs', 'per_unit', '', '', '', '', '', '', '50000', '100', '5', '50', 'Rak E-01', 'Ini contoh kode manual'],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 (biar Excel baca benar)
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, $columns);

            // Sample data
            foreach ($samples as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview CSV sebelum import
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $data = $this->parseCSV($path);

        if (empty($data['rows'])) {
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }

        $validated = $this->validateRows($data['rows'], $data['headers']);

        // Generate preview kode
        $lastMaterial = Material::orderBy('id', 'desc')->first();
        $lastNumber = $lastMaterial ? (int) substr($lastMaterial->kode, -4) : 0;

        foreach ($validated['rows'] as &$row) {
            if (empty($row['kode']) && empty($row['_errors'])) {
                $lastNumber++;
                $row['_preview_kode'] = 'MAT-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);
                $row['_kode_source'] = 'auto';
            } elseif (!empty($row['kode'])) {
                $row['_preview_kode'] = $row['kode'];
                $row['_kode_source'] = 'manual';
            } else {
                $row['_preview_kode'] = '-';
                $row['_kode_source'] = 'error';
            }
        }
        unset($row);

        return view('master.materials.import-preview', [
            'headers'    => $data['headers'],
            'rows'       => $validated['rows'],
            'validCount' => $validated['valid_count'],
            'errorCount' => $validated['error_count'],
            'totalCount' => count($data['rows']),
        ]);
    }

    /**
     * Proses import
     */
    public function store(Request $request)
    {
        $request->validate([
            'rows' => 'required|string',
        ]);

        $rows = json_decode($request->rows, true);

        if (!is_array($rows) || empty($rows)) {
            return back()->with('error', 'Data tidak valid.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 1;

                if (!empty($row['_errors'])) {
                    $skipped++;
                    continue;
                }

                $kode = !empty($row['kode']) ? $row['kode'] : Material::generateKode();

                if (Material::where('kode', $kode)->exists()) {
                    $skipped++;
                    $errors[] = "Baris {$rowNumber}: Kode {$kode} sudah ada.";
                    continue;
                }

                try {
                    Material::create([
                        'kode'              => $kode,
                        'kode_bahan'        => !empty($row['kode_bahan']) ? $row['kode_bahan'] : null,
                        'nama'              => $row['nama'],
                        'spesifikasi'       => !empty($row['spesifikasi']) ? $row['spesifikasi'] : null,
                        'category'          => !empty($row['category']) ? $row['category'] : null,
                        'unit'              => !empty($row['unit']) ? $row['unit'] : 'pcs',
                        'costing_method'    => !empty($row['costing_method']) ? $row['costing_method'] : 'per_unit',
                        'volume_type'       => !empty($row['volume_type']) ? $row['volume_type'] : null,
                        'panjang_standar'   => !empty($row['panjang_standar']) ? $row['panjang_standar'] : null,
                        'lebar_standar'     => !empty($row['lebar_standar']) ? $row['lebar_standar'] : null,
                        'tinggi_standar'    => !empty($row['tinggi_standar']) ? $row['tinggi_standar'] : null,
                        'berat_standar'     => !empty($row['berat_standar']) ? $row['berat_standar'] : null,
                        'volume_standar'    => !empty($row['volume_standar']) ? $row['volume_standar'] : null,
                        'price'             => !empty($row['price']) ? $row['price'] : 0,
                        'yield_percent'     => !empty($row['yield_percent']) ? $row['yield_percent'] : 100,
                        'min_stock'         => !empty($row['min_stock']) ? $row['min_stock'] : 0,
                        'max_stock'         => !empty($row['max_stock']) ? $row['max_stock'] : 0,
                        'current_stock'     => 0,
                        'location'          => !empty($row['location']) ? $row['location'] : null,
                        'notes'             => !empty($row['notes']) ? $row['notes'] : null,
                        'is_active'         => true,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "✅ Berhasil import {$imported} material.";
            if ($skipped > 0) {
                $message .= " ⚠️ {$skipped} baris di-skip.";
            }

            return redirect()
                ->route('master.materials.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV file — support separator , dan ;
     */
    private function parseCSV(string $path): array
    {
        $file = fopen($path, 'r');
        if (!$file) return ['headers' => [], 'rows' => []];

        $headers = [];
        $rows = [];
        $isFirst = true;

        // Detect BOM dan skip
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Baca first line untuk detect separator
        $firstLine = fgets($file);
        rewind($file);

        // Skip BOM lagi kalau ada
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Auto-detect separator: bandingkan jumlah , vs ;
        $countComma = substr_count($firstLine, ',');
        $countSemicolon = substr_count($firstLine, ';');
        $separator = ($countSemicolon > $countComma) ? ';' : ',';

        while (($line = fgetcsv($file, 0, $separator)) !== false) {
            if (empty(array_filter($line))) continue;

            if ($isFirst) {
                // Normalize header: lowercase, trim, replace spasi
                $headers = array_map(function($h) {
                    $h = trim($h);
                    $h = strtolower($h);
                    $h = str_replace(' ', '_', $h);
                    return $h;
                }, $line);
                $isFirst = false;
                continue;
            }

            $line = array_pad($line, count($headers), '');

            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = isset($line[$i]) ? trim($line[$i]) : '';
            }

            $rows[] = $row;
        }

        fclose($file);

        return [
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    /**
     * Validasi per baris
     */
    private function validateRows(array $rows, array $headers): array
    {
        $validated = [];
        $validCount = 0;
        $errorCount = 0;

        // Cek header wajib
        $requiredHeaders = ['nama'];
        $missingHeaders = [];

        foreach ($requiredHeaders as $req) {
            if (!in_array($req, $headers)) {
                $missingHeaders[] = $req;
            }
        }

        if (!empty($missingHeaders)) {
            $headerList = implode(', ', $headers);
            $missingList = implode(', ', $missingHeaders);
            throw new \Exception(
                "Kolom wajib '{$missingList}' tidak ditemukan di CSV. " .
                "Header yang terdeteksi: [{$headerList}]. " .
                "Pastikan baris pertama CSV adalah header (bukan data)."
            );
        }

        foreach ($rows as $row) {
            $errors = [];

            if (empty($row['nama'])) {
                $errors[] = 'Nama wajib diisi';
            }

            if (!empty($row['kode'])) {
                if (Material::where('kode', $row['kode'])->exists()) {
                    $errors[] = "Kode '{$row['kode']}' sudah ada di database";
                }
            }

            if (empty($row['unit'])) {
                $errors[] = 'Satuan wajib diisi';
            }

            // Costing method valid
            $validMethods = ['per_unit', 'per_area', 'per_volume', 'per_length', 'per_weight'];
            if (!empty($row['costing_method']) && !in_array($row['costing_method'], $validMethods)) {
                $errors[] = 'Costing method tidak valid';
            }

            // Volume type valid
            if (!empty($row['volume_type'])) {
                $validVolumeTypes = ['kotak', 'cair'];
                if (!in_array($row['volume_type'], $validVolumeTypes)) {
                    $errors[] = 'Volume type harus kotak atau cair';
                }
            }

            // Price numeric
            if (!empty($row['price']) && !is_numeric($row['price'])) {
                $errors[] = 'Harga harus angka';
            }

            // Yield numeric
            if (!empty($row['yield_percent'])) {
                if (!is_numeric($row['yield_percent'])) {
                    $errors[] = 'Yield harus angka';
                } elseif ($row['yield_percent'] < 0 || $row['yield_percent'] > 100) {
                    $errors[] = 'Yield harus 0-100';
                }
            }

            // Dimensi untuk costing method
            $method = !empty($row['costing_method']) ? $row['costing_method'] : 'per_unit';
            $volumeType = !empty($row['volume_type']) ? $row['volume_type'] : 'kotak';

            if ($method === 'per_area') {
                if (empty($row['panjang_standar']) || empty($row['lebar_standar'])) {
                    $errors[] = 'Per Area butuh Panjang & Lebar standar';
                }
            }
            if ($method === 'per_volume') {
                if ($volumeType === 'cair') {
                    if (empty($row['volume_standar'])) {
                        $errors[] = 'Per Volume Cair butuh Volume Standar (ml)';
                    }
                } else {
                    if (empty($row['panjang_standar']) || empty($row['lebar_standar']) || empty($row['tinggi_standar'])) {
                        $errors[] = 'Per Volume Kotak butuh Panjang, Lebar & Tinggi standar';
                    }
                }
            }
            if ($method === 'per_length' && empty($row['panjang_standar'])) {
                $errors[] = 'Per Panjang butuh Panjang standar';
            }
            if ($method === 'per_weight' && empty($row['berat_standar'])) {
                $errors[] = 'Per Berat butuh Berat standar';
            }

            $row['_errors'] = $errors;

            if (empty($errors)) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $validated[] = $row;
        }

        return [
            'rows'        => $validated,
            'valid_count' => $validCount,
            'error_count' => $errorCount,
        ];
    }
}