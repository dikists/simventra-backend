<?php

namespace App\Livewire\Documents;

use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class EmployeeDocumentIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterDocType = '';
    public bool $showForm = false;
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    // Form fields
    public int $employee_id = 0;
    public string $document_type = '';
    public string $document_number = '';
    public string $title = '';
    public $file = null;
    public string $issued_date = '';
    public string $expiry_date = '';
    public string $status = 'active';
    public string $notes = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function openForm(): void { $this->showForm = true; $this->resetForm(); }
    public function closeForm(): void { $this->showForm = false; $this->resetForm(); }

    protected function resetForm(): void
    {
        $this->employee_id = 0;
        $this->document_type = '';
        $this->document_number = '';
        $this->title = '';
        $this->file = null;
        $this->issued_date = '';
        $this->expiry_date = '';
        $this->status = 'active';
        $this->notes = '';
    }

    public function save(): void
    {
        $this->validate([
            'employee_id'     => 'required|exists:employees,id',
            'document_type'   => 'required|string|max:60',
            'title'           => 'required|string|max:150',
            'document_number' => 'nullable|string|max:60',
            'issued_date'     => 'nullable|date',
            'expiry_date'     => 'nullable|date|after_or_equal:issued_date',
            'status'          => 'required|in:active,expired,revoked',
            'file'            => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('employee-documents', config('filesystems.default_public_disk'));
        }

        EmployeeDocument::create([
            'employee_id'     => $this->employee_id,
            'document_type'   => $this->document_type,
            'document_number' => $this->document_number,
            'title'           => $this->title,
            'file_path'       => $filePath,
            'issued_date'     => $this->issued_date ?: null,
            'expiry_date'     => $this->expiry_date ?: null,
            'status'          => $this->status,
            'notes'           => $this->notes,
            'uploaded_by'     => auth()->id(),
        ]);

        session()->flash('success', 'Dokumen karyawan berhasil ditambahkan.');
        $this->closeForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $doc = EmployeeDocument::findOrFail($this->deleteId);

            // hapus file lama
            if ($doc->file_path && Storage::disk(config('filesystems.default_public_disk'))->exists($doc->file_path)) {
                Storage::disk(config('filesystems.default_public_disk'))->delete($doc->file_path);
            }

            $doc->delete();
            session()->flash('success', 'Dokumen berhasil dihapus.');
        }
        $this->showDeleteModal = false;
    }

    public function render()
    {
        $documents = EmployeeDocument::with('employee')
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhere('title', 'like', "%{$this->search}%")
                ->orWhere('document_number', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDocType, fn($q) => $q->where('document_type', 'like', "%{$this->filterDocType}%"))
            ->orderBy('expiry_date')
            ->paginate(15);

        $employees = \App\Models\Employee::active()->orderBy('name')->get();

        return view('livewire.documents.employee-document-index', compact('documents', 'employees'))
            ->layout('layouts.app', ['title' => 'Dokumen Karyawan']);
    }
}
