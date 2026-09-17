<?php

namespace App\Livewire\Settings;

use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Pengaturan Gudang')]
class WarehouseSettings extends Component
{
    public int|null $warehouseId = null;
    public string $name     = '';
    public string $address  = '';
    public float  $lat      = -6.2088;
    public float  $lng      = 106.8456;

    public bool $saved = false;

    public function mount(): void
    {
        $warehouse = Warehouse::primary()->first();

        if ($warehouse) {
            $this->warehouseId = $warehouse->id;
            $this->name        = $warehouse->name;
            $this->address     = $warehouse->address;
            $this->lat         = $warehouse->latitude;
            $this->lng         = $warehouse->longitude;
        } else {
            // Fallback ke config/.env
            $this->name    = config('warehouse.name', 'Pusat Distribusi Utama');
            $this->address = config('warehouse.address', 'Jakarta, Indonesia');
            $this->lat     = config('warehouse.lat', -6.2088);
            $this->lng     = config('warehouse.lng', 106.8456);
        }
    }

    /**
     * Dipanggil dari JavaScript saat user klik di peta.
     */
    public function setCoords($lat, $lng): void
    {
        $this->lat = round((float) $lat, 7);
        $this->lng = round((float) $lng, 7);
    }

    /**
     * Dipanggil dari JS saat Nominatim reverse geocoding selesai.
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    /**
     * Safety guard jika JS proxy memanggil toJSON saat serialization
     */
    public function toJSON(): array
    {
        return [
            'name'    => $this->name,
            'address' => $this->address,
            'lat'     => $this->lat,
            'lng'     => $this->lng,
        ];
    }

    public function save(): void
    {
        $this->validate([
            'name'    => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'lat'     => 'required|numeric|between:-90,90',
            'lng'     => 'required|numeric|between:-180,180',
        ]);

        $data = [
            'name'      => $this->name,
            'address'   => $this->address,
            'latitude'  => $this->lat,
            'longitude' => $this->lng,
            'is_primary'=> true,
            'is_active' => true,
        ];

        if ($this->warehouseId) {
            Warehouse::where('id', $this->warehouseId)->update($data);
        } else {
            $warehouse = Warehouse::create($data);
            $this->warehouseId = $warehouse->id;
        }

        // Hapus cache agar live tracking langsung reflect perubahan
        Warehouse::clearPrimaryCache();

        $this->saved = true;

        $this->dispatch('warehouse-saved');
    }

    public function render()
    {
        return view('livewire.settings.warehouse-settings')
            ->layout('layouts.app', ['title' => 'Pengaturan Gudang']);
    }
}
