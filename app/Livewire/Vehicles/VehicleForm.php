<?php

namespace App\Livewire\Vehicles;

use App\Models\Employee;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class VehicleForm extends Component
{
    use WithFileUploads;

    public ?Vehicle $vehicle = null;
    public bool $isEdit = false;

    public string $vehicle_code = '';
    public string $license_plate = '';
    public string $brand = '';
    public ?string $model = null;
    public string $vehicle_type = '';
    public int|string|null $year = null;
    public ?string $color = null;
    public ?string $chassis_number = null;
    public ?string $engine_number = null;
    public float|int|string|null $max_capacity_kg = null;
    public float|int|string|null $current_odometer_km = null;
    public string $fuel_type = 'solar';
    public bool $is_halal_dedicated = false;
    public string $status = 'active';
    public int|string|null $assigned_driver_id = null;
    public ?string $gps_device_id = null;
    public ?string $notes = null;
    public ?string $existingPhoto = null;
    public $photo = null;

    protected function rules(): array
    {
        $uniquePlate = 'required|string|max:15|unique:vehicles,license_plate';
        $uniqueCode  = 'required|string|max:30|unique:vehicles,vehicle_code';
        if ($this->isEdit && $this->vehicle) {
            $uniquePlate .= ',' . $this->vehicle->id;
            $uniqueCode  .= ',' . $this->vehicle->id;
        }
        return [
            'vehicle_code'        => $uniqueCode,
            'license_plate'       => $uniquePlate,
            'brand'               => 'required|string|max:60',
            'model'               => 'nullable|string|max:60',
            'vehicle_type'        => 'required|string|max:60',
            'year'                => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'color'               => 'nullable|string|max:30',
            'chassis_number'      => 'nullable|string|max:30',
            'engine_number'       => 'nullable|string|max:30',
            'max_capacity_kg'     => 'nullable|numeric|min:0',
            'current_odometer_km' => 'nullable|numeric|min:0',
            'fuel_type'           => 'required|in:solar,bensin,listrik,hybrid',
            'is_halal_dedicated'  => 'boolean',
            'status'              => 'required|in:active,maintenance,inactive,scrapped',
            'assigned_driver_id'  => 'nullable|exists:employees,id',
            'gps_device_id'       => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:1000',
            'photo'               => 'nullable|image|max:10240', // Dukung foto kamera smartphone hingga 10MB
        ];
    }

    protected $messages = [
        'vehicle_code.required'   => 'Kode kendaraan wajib diisi.',
        'vehicle_code.unique'     => 'Kode kendaraan ini sudah digunakan.',
        'license_plate.required'  => 'Nomor polisi kendaraan wajib diisi.',
        'license_plate.unique'    => 'Nomor polisi ini sudah terdaftar di sistem.',
        'brand.required'          => 'Merek kendaraan wajib diisi.',
        'vehicle_type.required'   => 'Jenis kendaraan wajib diisi.',
        'photo.image'             => 'File foto harus berupa format gambar (JPG, PNG, WEBP).',
        'photo.max'               => 'Ukuran foto maksimal adalah 10MB.',
        'assigned_driver_id.exists' => 'Sopir yang dipilih tidak valid atau tidak ditemukan.',
    ];

    public function mount(?Vehicle $vehicle = null): void
    {
        if ($vehicle && $vehicle->exists) {
            $this->vehicle = $vehicle;
            $this->isEdit  = true;
            $this->fill($vehicle->toArray());
            $this->existingPhoto = $vehicle->photo;
            $this->photo = null;
        } else {
            $nextId = ((int) Vehicle::max('id')) + 1;
            $this->vehicle_code = 'KND-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
        }
    }

    public function removePhoto(): void
    {
        $this->photo = null;
        if ($this->isEdit) {
            $this->existingPhoto = null;
        }
    }

    public function save(): void
    {
        // Normalisasi input string kosong menjadi null sebelum validasi
        // agar tidak memicu error nullable pada rule integer, numeric, dan foreign key exists
        if ($this->assigned_driver_id === '' || $this->assigned_driver_id === '0') {
            $this->assigned_driver_id = null;
        }
        if ($this->year === '') $this->year = null;
        if ($this->max_capacity_kg === '') $this->max_capacity_kg = null;
        if ($this->current_odometer_km === '') $this->current_odometer_km = null;
        if ($this->model === '') $this->model = null;
        if ($this->color === '') $this->color = null;
        if ($this->chassis_number === '') $this->chassis_number = null;
        if ($this->engine_number === '') $this->engine_number = null;
        if ($this->gps_device_id === '') $this->gps_device_id = null;
        if ($this->notes === '') $this->notes = null;

        $validated = $this->validate();

        // Convert empty strings to null for all nullable fields
        $validated = array_map(function ($value) {
            return ($value === '') ? null : $value;
        }, $validated);

        // Preserve boolean types
        $validated['is_halal_dedicated'] = (bool) ($this->is_halal_dedicated ?? false);

        try {
            $disk = config('filesystems.default_public_disk');

            if ($this->photo && !is_string($this->photo)) {
                // Hapus file lama jika ada saat edit
                if ($this->isEdit && $this->vehicle?->photo && Storage::disk($disk)->exists($this->vehicle->photo)) {
                    Storage::disk($disk)->delete($this->vehicle->photo);
                }
                $validated['photo'] = $this->photo->store('vehicles/photos', $disk);
            } elseif ($this->isEdit) {
                $validated['photo'] = $this->existingPhoto ?? $this->vehicle?->photo;
            } else {
                unset($validated['photo']);
            }

            if ($this->isEdit && $this->vehicle) {
                $this->vehicle->update($validated);
                session()->flash('success', "Kendaraan '{$this->vehicle->license_plate}' berhasil diperbarui.");
            } else {
                $vehicle = Vehicle::create($validated);
                session()->flash('success', "Kendaraan '{$vehicle->license_plate}' berhasil ditambahkan.");
            }

            $this->redirect(route('vehicles.index'), navigate: true);
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menyimpan kendaraan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $drivers = Employee::active()->drivers()->orderBy('name')->get();
        $title   = $this->isEdit ? 'Edit Kendaraan: ' . $this->vehicle?->license_plate : 'Tambah Kendaraan';

        return view('livewire.vehicles.vehicle-form', array_merge(get_object_vars($this), [
            'drivers' => $drivers,
            'title'   => $title,
        ]))->layout('layouts.app', ['title' => $title]);
    }
}
