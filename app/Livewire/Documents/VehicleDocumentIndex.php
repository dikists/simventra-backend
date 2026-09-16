<?php

namespace App\Livewire\Documents;

use App\Models\VehicleDocument;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class VehicleDocumentIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public int $vehicle_id = 0;
    public string $document_type = '';
    public string $document_number = '';
    public string $title = '';
    public $file = null;
    public string $issued_date = '';
    public string $expiry_date = '';
    public string $issuing_authority = '';
    public string $cost = '';
    public string $status = 'active';
    public string $notes = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function openForm(): void { $this->showForm = true; }
    public function closeForm(): void { $this->showForm = false; $this->resetExcept(['search','filterStatus']); }

    public function save(): void
    {
        $this->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'document_type'     => 'required|string|max:60',
            'title'             => 'required|string|max:150',
            'document_number'   => 'nullable|string|max:60',
            'issued_date'       => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:issued_date',
            'issuing_authority' => 'nullable|string|max:100',
            'cost'              => 'nullable|numeric|min:0',
            'status'            => 'required|in:active,expired,revoked',
            'file'              => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('vehicle-documents', 'public');
        }

        VehicleDocument::create([
            'vehicle_id'        => $this->vehicle_id,
            'document_type'     => $this->document_type,
            'document_number'   => $this->document_number,
            'title'             => $this->title,
            'file_path'         => $filePath,
            'issued_date'       => $this->issued_date ?: null,
            'expiry_date'       => $this->expiry_date ?: null,
            'issuing_authority' => $this->issuing_authority,
            'cost'              => $this->cost ?: null,
            'status'            => $this->status,
            'notes'             => $this->notes,
            'uploaded_by'       => auth()->id(),
        ]);

        session()->flash('success', 'Dokumen kendaraan berhasil ditambahkan.');
        $this->closeForm();
    }

    public function confirmDelete(int $id): void { $this->deleteId = $id; $this->showDeleteModal = true; }
    public function delete(): void
    {
        if ($this->deleteId) { VehicleDocument::findOrFail($this->deleteId)->delete(); session()->flash('success', 'Dokumen dihapus.'); }
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $documents = VehicleDocument::with('vehicle')
            ->when($this->search, fn($q) => $q->whereHas('vehicle', fn($q) => $q->where('license_plate', 'like', "%{$this->search}%"))->orWhere('title', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('expiry_date')
            ->paginate(15);

        $vehicles = \App\Models\Vehicle::active()->orderBy('license_plate')->get();

        return view('livewire.documents.vehicle-document-index', compact('documents', 'vehicles'))
            ->layout('layouts.app', ['title' => 'Dokumen Kendaraan']);
    }
}
