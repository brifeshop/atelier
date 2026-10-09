<?php

namespace App\Http\Controllers\Engineering;

use App\Http\Controllers\Controller;
use App\Models\Engineering\Bom;
use App\Models\Engineering\BomItem;
use App\Models\Master\Material;
use App\Models\Master\Product;
use App\Services\BomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BomImportController extends Controller
{
    /**
     * Form upload
     */
    public function index(Bom $bom)
    {
        if (!$bom->canEdit()) {
            return redirect()
                ->route('engineering.boms.show', $bom)
                ->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        return view('engineering.boms.import', [
            'bom' => $bom,
        ]);
    }

    /**
     * Download template CSV
     */
    public function template(Bom $bom)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-import-bom.csv"',
        ];

        $columns = [
            'level',
            'header_group',
            'divisi',
            'kode_bahan',
            'kode',
            'nama_item',
            'qty',
            'unit',
            'spesifikasi',
            'panjang_pakai',
            'lebar_pakai',
            'tinggi_pakai',
            'berat_pakai',
            'scrap_percent',
        ];

        $samples = [
            // Header Group
            ['L.1', 'Meja Pukul Palu', 'Kayu', '', '', '', '', '', '', '', '', '', '', ''],
            // Items di bawah group
            ['L.2', '', 'Kayu', 'A8-18-0', '', 'Kayu Pinus', '1', 'pcs', '164 x 120 x 18', '164', '120', '18', '', '0'],
            ['L.2', '', 'Kayu', 'A8-19-0', '', 'Kayu Pinus', '2', 'pcs', '150 x 100 x 18', '150', '100', '18', '', '0'],
            ['L.2', '', 'Offset Printing', 'F5-2-1', '', 'Varnish', '1', 'pcs', 'Doff 714D', '', '', '', '', '0'],
            // Header Group 2
            ['L.1', 'Palu', 'Kayu', '', '', '', '', '', '', '', '', '', '', ''],
            ['L.2', '', 'Kayu', 'A7-9-0', '', 'Dowel Pinus', '1', 'pcs', 'dia.50 mm', '', '', '', '', '0'],
            ['L.2', '', 'Kayu', 'A7-4-0', '', 'Dowel Pinus', '1', 'pcs', 'dia.15 mm', '', '', '', '', '0'],
            ['L.2', '', 'Offset Printing', 'F5-2-1', '', 'Varnish', '1', 'pcs', 'Doff 714D', '', '', '', '', '0'],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            foreach ($samples as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview CSV
     */
    public function preview(Request $request, Bom $bom)
    {
        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $data = $this->parseCSV($path);

        if (empty($data['rows'])) {
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }

        $validated = $this->validateRows($data['rows'], $data['headers'], $bom);

        return view('engineering.boms.import-preview', [
            'bom'        => $bom,
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
    public function store(Request $request, Bom $bom)
    {
        if (!$bom->canEdit()) {
            return back()->with('error', 'BOM yang sudah aktif tidak bisa diubah.');
        }

        $request->validate([
            'rows' => 'required|string',
            'mode' => 'required|in:append,replace',
        ]);

        $rows = json_decode($request->rows, true);
        $mode = $request->mode;

        if (!is_array($rows) || empty($rows)) {
            return back()->with('error', 'Data tidak valid.');
        }

        DB::beginTransaction();
        try {
            // Mode replace: hapus semua item dulu
            if ($mode === 'replace') {
                $bom->items()->delete();
            }

            $maxSeq = $bom->items()->max('sequence') ?? 0;
            $imported = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                if (!empty($row['_errors'])) {
                    $skipped++;
                    continue;
                }

                $maxSeq++;

                // Cek apakah header group
                if (!empty($row['header_group'])) {
                    BomItem::create([
                        'bom_id'       => $bom->id,
                        'sequence'     => $maxSeq,
                        'is_header'    => true,
                        'header_label' => $row['header_group'],
                        'level'        => $row['level'] ?? null,
                        'divisi'       => $row['divisi'] ?? null,
                        'item_type'    => 'material',
                        'item_id'      => 0,
                        'qty'          => 0,
                        'unit'         => '-',
                        'unit_cost'    => 0,
                        'total_cost'   => 0,
                    ]);
                    $imported++;
                    continue;
                }

                // Item biasa
                $material = $row['_matched_material'] ?? null;
                if (!$material) {
                    $skipped++;
                    continue;
                }

                $bomItem = new BomItem();
                $bomItem->bom_id         = $bom->id;
                $bomItem->sequence       = $maxSeq;
                $bomItem->item_type      = 'material';
                $bomItem->item_id        = $material['id'];
                $bomItem->qty            = $row['qty'];
                $bomItem->unit           = $row['unit'] ?: $material['unit'];
                $bomItem->spesifikasi    = $row['spesifikasi'] ?? null;
                $bomItem->divisi         = $row['divisi'] ?? null;
                $bomItem->level          = $row['level'] ?? null;
                $bomItem->panjang_pakai  = $row['panjang_pakai'] ?? null;
                $bomItem->lebar_pakai    = $row['lebar_pakai'] ?? null;
                $bomItem->tinggi_pakai   = $row['tinggi_pakai'] ?? null;
                $bomItem->berat_pakai    = $row['berat_pakai'] ?? null;
                $bomItem->scrap_percent  = $row['scrap_percent'] ?? 0;

                // Hitung cost
                $materialModel = Material::find($material['id']);
                if ($materialModel) {
                    $cost = BomService::calculateItemCost($bomItem, $materialModel);
                    $bomItem->unit_cost  = $cost['unit_cost'];
                    $bomItem->total_cost = $cost['total_cost'];
                }

                $bomItem->save();
                $imported++;
            }

            // Recalculate BOM cost
            BomService::updateCost($bom);

            DB::commit();

            return redirect()
                ->route('engineering.boms.show', $bom)
                ->with('success', "✅ Berhasil import {$imported} baris." . ($skipped > 0 ? " ⚠️ {$skipped} baris di-skip." : ""));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV — auto-detect separator
     */
    private function parseCSV(string $path): array
    {
        $file = fopen($path, 'r');
        if (!$file) return ['headers' => [], 'rows' => []];

        $headers = [];
        $rows = [];
        $isFirst = true;

        // Skip BOM
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Detect separator
        $firstLine = fgets($file);
        rewind($file);
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($file);

        $countComma = substr_count($firstLine, ',');
        $countSemicolon = substr_count($firstLine, ';');
        $separator = ($countSemicolon > $countComma) ? ';' : ',';

        while (($line = fgetcsv($file, 0, $separator)) !== false) {
            if (empty(array_filter($line))) continue;

            if ($isFirst) {
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

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Validasi rows
     */
    private function validateRows(array $rows, array $headers, Bom $bom): array
    {
        $validated = [];
        $validCount = 0;
        $errorCount = 0;

        // Cek header wajib
        $requiredHeaders = ['nama_item'];
        $missing = [];
        foreach ($requiredHeaders as $req) {
            if (!in_array($req, $headers)) {
                $missing[] = $req;
            }
        }

        if (!empty($missing)) {
            $headerList = implode(', ', $headers);
            $missingList = implode(', ', $missing);
            throw new \Exception("Kolom wajib '{$missingList}' tidak ditemukan. Header yang terdeteksi: [{$headerList}]");
        }

        foreach ($rows as $row) {
            $errors = [];
            $matchedMaterial = null;

            // Cek header group
            if (!empty($row['header_group'])) {
                // Ini header group — minimal ada label
                if (empty($row['header_group'])) {
                    $errors[] = 'Nama header group wajib diisi';
                }
            } else {
                // Ini item biasa — butuh material
                $material = null;

                // Match by kode_bahan
                if (!empty($row['kode_bahan'])) {
                    $material = Material::where('kode_bahan', $row['kode_bahan'])->first();
                    if (!$material) {
                        $errors[] = "Material dengan kode_bahan '{$row['kode_bahan']}' tidak ditemukan";
                    }
                }

                // Fallback: match by kode
                if (!$material && !empty($row['kode'])) {
                    $material = Material::where('kode', $row['kode'])->first();
                    if (!$material) {
                        $errors[] = "Material dengan kode '{$row['kode']}' tidak ditemukan";
                    }
                }

                // Fallback: match by nama
                if (!$material && !empty($row['nama_item'])) {
                    $material = Material::where('nama', 'like', $row['nama_item'])->first();
                    if (!$material) {
                        $errors[] = "Material '{$row['nama_item']}' tidak ditemukan";
                    }
                }

                // Validasi qty
                if (empty($row['qty']) || !is_numeric($row['qty']) || $row['qty'] <= 0) {
                    $errors[] = 'Qty wajib diisi dan harus angka positif';
                }

                if ($material) {
                    $matchedMaterial = [
                        'id'   => $material->id,
                        'kode' => $material->kode,
                        'kode_bahan' => $material->kode_bahan,
                        'nama' => $material->nama,
                        'unit' => $material->unit,
                    ];
                }
            }

            $row['_errors'] = $errors;
            $row['_matched_material'] = $matchedMaterial;
            $row['_is_header'] = !empty($row['header_group']);

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