<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Title('Pengaturan Profil & Logo Perusahaan')]
class CompanySettings extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $short_name = '';
    public string $tagline = '';
    public string|null $phone = '';
    public string|null $email = '';
    public string|null $address = '';
    public string|null $current_logo = '';

    public $file = null;
    public bool $saved = false;

    public function mount(): void
    {
        $setting = CompanySetting::getSettings();

        $this->name         = $setting->name ?? 'PT Rajawali Handal Logistik';
        $this->short_name   = $setting->short_name ?? 'Rajawali Handal';
        $this->tagline      = $setting->tagline ?? 'Logistik';
        $this->phone        = $setting->phone ?? '';
        $this->email        = $setting->email ?? '';
        $this->address      = $setting->address ?? '';
        $this->current_logo = CompanySetting::getLogoUrl();
    }

    public function save(): void
    {
        $this->validate([
            'name'       => 'required|string|max:100',
            'short_name' => 'required|string|max:40',
            'tagline'    => 'nullable|string|max:50',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:100',
            'address'    => 'nullable|string|max:255',
            'file'       => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ]);

        $setting = CompanySetting::first();
        if (! $setting) {
            $setting = new CompanySetting();
        }

        $setting->name       = $this->name;
        $setting->short_name = $this->short_name;
        $setting->tagline    = $this->tagline ?? 'Logistik';
        $setting->phone      = $this->phone;
        $setting->email      = $this->email;
        $setting->address    = $this->address;

        // Handle upload logo sesuai pola modul dokumen & kendaraan
        if ($this->file) {
            $disk = config('filesystems.default_public_disk');

            // Hapus logo lama jika ada dan bukan asset bawaan /assets
            if ($setting->logo_path && !str_starts_with($setting->logo_path, '/assets') && Storage::disk($disk)->exists($setting->logo_path)) {
                Storage::disk($disk)->delete($setting->logo_path);
            }

            $setting->logo_path = $this->file->store('company', $disk);
            $this->file = null;
        }

        $setting->save();

        CompanySetting::clearCache();
        $this->current_logo = CompanySetting::getLogoUrl();
        $this->saved = true;

        session()->flash('success', 'Pengaturan & logo perusahaan berhasil diperbarui!');
    }

    public function resetToDefaultLogo(): void
    {
        $setting = CompanySetting::first();
        if ($setting) {
            $disk = config('filesystems.default_public_disk');

            // Hapus file custom jika ada
            if ($setting->logo_path && !str_starts_with($setting->logo_path, '/assets') && Storage::disk($disk)->exists($setting->logo_path)) {
                Storage::disk($disk)->delete($setting->logo_path);
            }

            $setting->logo_path = '/assets/logo_rhl.png';
            $setting->save();

            CompanySetting::clearCache();
            $this->current_logo = CompanySetting::getLogoUrl();
            $this->file = null;

            session()->flash('success', 'Logo berhasil dikembalikan ke default RHL!');
        }
    }

    public function render()
    {
        return view('livewire.settings.company-settings')
            ->layout('layouts.app', ['title' => 'Pengaturan Profil & Logo Perusahaan']);
    }
}
