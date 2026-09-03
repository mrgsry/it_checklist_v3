<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AssetImportService
{
    public const HEADERS = [
        'kategori', 'nama', 'tahun_pembelian', 'merk', 'tipe',
        'kode_barang', 'nomor_inventaris', 'serial_number', 'jumlah',
        'lokasi', 'keterangan',
    ];

    public function import(UploadedFile $file): int
    {
        $rows = $this->readRows($file);
        $categories = AssetCategory::query()->get()->keyBy(fn (AssetCategory $category) => mb_strtolower(trim($category->name)));
        $errors = [];
        $seen = [];
        $data = [];

        foreach ($rows as $rowNumber => $row) {
            $values = array_combine(self::HEADERS, array_pad(array_slice($row, 0, count(self::HEADERS)), count(self::HEADERS), null));
            $values = array_map(static fn ($value) => is_string($value) ? trim($value) : $value, $values);
            foreach (['merk', 'tipe', 'kode_barang', 'nomor_inventaris', 'serial_number', 'keterangan'] as $optionalField) {
                if (($values[$optionalField] ?? null) === '') {
                    $values[$optionalField] = null;
                }
            }
            $categoryName = mb_strtolower((string) ($values['kategori'] ?? ''));
            $values['asset_category_id'] = $categories[$categoryName]->id ?? null;

            $validator = Validator::make($values, [
                'asset_category_id' => ['required', 'exists:asset_categories,id'],
                'nama' => ['required', 'string', 'max:255'],
                'tahun_pembelian' => ['required', 'integer', 'between:1900,'.now()->year],
                'merk' => ['nullable', 'string', 'max:100'],
                'tipe' => ['nullable', 'string', 'max:100'],
                'kode_barang' => ['nullable', 'string', 'max:100', 'unique:assets,item_code'],
                'nomor_inventaris' => ['nullable', 'string', 'max:100', 'unique:assets,inventory_number'],
                'serial_number' => ['nullable', 'string', 'max:150', 'unique:assets,serial_number'],
                'jumlah' => ['required', 'integer', 'min:1'],
                'lokasi' => ['required', 'string', 'max:255'],
                'keterangan' => ['nullable', 'string', 'max:5000'],
            ], [], [
                'asset_category_id' => 'kategori', 'nama' => 'nama', 'tahun_pembelian' => 'tahun pembelian',
                'merk' => 'merk', 'tipe' => 'tipe', 'kode_barang' => 'kode barang',
                'nomor_inventaris' => 'nomor inventaris', 'serial_number' => 'serial number',
                'jumlah' => 'jumlah', 'lokasi' => 'lokasi', 'keterangan' => 'keterangan',
            ]);

            foreach (['kode_barang', 'nomor_inventaris', 'serial_number'] as $uniqueField) {
                if (($values[$uniqueField] ?? '') !== '' && isset($seen[$uniqueField][$values[$uniqueField]])) {
                    $validator->errors()->add($uniqueField, 'Nilai duplikat dengan baris '.$seen[$uniqueField][$values[$uniqueField]].'.');
                } elseif (($values[$uniqueField] ?? '') !== '') {
                    $seen[$uniqueField][$values[$uniqueField]] = $rowNumber;
                }
            }

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors["baris_{$rowNumber}"][] = "Baris {$rowNumber}: {$message}";
                }

                continue;
            }

            $data[] = [
                'asset_category_id' => $values['asset_category_id'], 'name' => $values['nama'],
                'purchase_year' => $values['tahun_pembelian'], 'brand' => $values['merk'], 'type' => $values['tipe'],
                'item_code' => $values['kode_barang'], 'inventory_number' => $values['nomor_inventaris'],
                'serial_number' => $values['serial_number'], 'quantity' => $values['jumlah'], 'location' => $values['lokasi'],
                'description' => $values['keterangan'] ?: null, 'created_at' => now(), 'updated_at' => now(),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($data): int {
            Asset::query()->insert($data);

            return count($data);
        });
    }

    public function template(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray(['Switch', 'Contoh UniFi Switch', now()->year, 'Ubiquiti', 'USW-24', 'IT-SW-001', 'INV-'.now()->year.'-001', 'SN-CONTOH-001', 1, 'Server Room', 'Contoh data, silakan hapus baris ini.'], null, 'A2');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function readRows(UploadedFile $file): array
    {
        try {
            $reader = match (mb_strtolower($file->getClientOriginalExtension())) {
                'csv' => IOFactory::createReader('Csv'),
                'xls' => IOFactory::createReader('Xls'),
                'xlsx' => IOFactory::createReader('Xlsx'),
                default => throw new \InvalidArgumentException('Unsupported file extension.'),
            };
            $reader->setReadDataOnly(true);
            $rows = $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca. Pastikan format CSV atau XLS benar.']);
        }

        if ($rows === [] || $this->normalizedHeaders($rows[0] ?? []) !== self::HEADERS) {
            throw ValidationException::withMessages(['file' => 'Header file tidak sesuai template import asset.']);
        }

        $rows = array_slice($rows, 1);
        $rows = array_values(array_filter($rows, static fn (array $row) => count(array_filter($row, static fn ($value) => $value !== null && trim((string) $value) !== '')) > 0));
        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'File tidak memiliki data asset.']);
        }
        if (count($rows) > 5000) {
            throw ValidationException::withMessages(['file' => 'Maksimal 5.000 baris dapat diimport sekaligus.']);
        }

        $result = [];
        foreach ($rows as $index => $row) {
            $result[$index + 2] = $row;
        }

        return $result;
    }

    private function normalizedHeaders(array $headers): array
    {
        return array_map(static fn ($header) => mb_strtolower(trim((string) $header)), array_slice($headers, 0, count(self::HEADERS)));
    }
}
