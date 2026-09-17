<?php

namespace Tests\Feature;

use App\Livewire\Documents\EmployeeDocumentIndex;
use App\Livewire\Documents\VehicleDocumentIndex;
use App\Livewire\Employees\EmployeeForm;
use App\Livewire\Employees\EmployeeIndex;
use App\Livewire\Vehicles\VehicleForm;
use App\Livewire\Vehicles\VehicleIndex;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileUploadPatternTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup user with permissions
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_employee_photo_upload_and_replace_deletes_old_file(): void
    {
        $disk = config('filesystems.default_public_disk');
        Storage::fake($disk);

        $file1 = UploadedFile::fake()->image('avatar1.jpg');

        // Create employee
        Livewire::test(EmployeeForm::class)
            ->set('employee_number', 'EMP-0001')
            ->set('name', 'John Doe')
            ->set('type', 'karyawan')
            ->set('employment_status', 'tetap')
            ->set('status', 'active')
            ->set('photo', $file1)
            ->call('save');

        $employee = Employee::first();
        $this->assertNotNull($employee);
        $this->assertNotNull($employee->photo);
        Storage::disk($disk)->assertExists($employee->photo);
        $oldPhoto = $employee->photo;

        // Edit employee with new photo
        $file2 = UploadedFile::fake()->image('avatar2.jpg');
        Livewire::test(EmployeeForm::class, ['employee' => $employee])
            ->set('photo', $file2)
            ->call('save');

        $employee->refresh();
        $this->assertNotEquals($oldPhoto, $employee->photo);
        Storage::disk($disk)->assertMissing($oldPhoto);
        Storage::disk($disk)->assertExists($employee->photo);

        // Test delete employee deletes photo
        Livewire::test(EmployeeIndex::class)
            ->call('confirmDelete', $employee->id)
            ->call('delete');

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        Storage::disk($disk)->assertMissing($employee->photo);
    }

    public function test_vehicle_photo_upload_and_replace_deletes_old_file(): void
    {
        $disk = config('filesystems.default_public_disk');
        Storage::fake($disk);

        $file1 = UploadedFile::fake()->image('vehicle1.jpg');

        Livewire::test(VehicleForm::class)
            ->set('vehicle_code', 'V-001')
            ->set('license_plate', 'B 1234 ABC')
            ->set('brand', 'Toyota')
            ->set('vehicle_type', 'Pickup')
            ->set('fuel_type', 'solar')
            ->set('status', 'active')
            ->set('photo', $file1)
            ->call('save');

        $vehicle = Vehicle::first();
        $this->assertNotNull($vehicle);
        $this->assertNotNull($vehicle->photo);
        Storage::disk($disk)->assertExists($vehicle->photo);
        $oldPhoto = $vehicle->photo;

        // Edit vehicle with new photo
        $file2 = UploadedFile::fake()->image('vehicle2.jpg');
        Livewire::test(VehicleForm::class, ['vehicle' => $vehicle])
            ->set('photo', $file2)
            ->call('save');

        $vehicle->refresh();
        $this->assertNotEquals($oldPhoto, $vehicle->photo);
        Storage::disk($disk)->assertMissing($oldPhoto);
        Storage::disk($disk)->assertExists($vehicle->photo);

        // Delete vehicle
        Livewire::test(VehicleIndex::class)
            ->call('confirmDelete', $vehicle->id)
            ->call('delete');

        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
        Storage::disk($disk)->assertMissing($vehicle->photo);
    }

    public function test_document_upload_and_delete(): void
    {
        $disk = config('filesystems.default_public_disk');
        Storage::fake($disk);

        $employee = Employee::create([
            'employee_number' => 'EMP-0002',
            'name' => 'Jane Doe',
            'type' => 'karyawan',
            'employment_status' => 'tetap',
            'status' => 'active',
        ]);

        $docFile = UploadedFile::fake()->create('contract.pdf', 100);

        Livewire::test(EmployeeDocumentIndex::class)
            ->set('employee_id', $employee->id)
            ->set('document_type', 'Kontrak')
            ->set('title', 'Kontrak Kerja')
            ->set('status', 'active')
            ->set('file', $docFile)
            ->call('save');

        $doc = EmployeeDocument::first();
        $this->assertNotNull($doc);
        $this->assertNotNull($doc->file_path);
        Storage::disk($disk)->assertExists($doc->file_path);

        // Delete document
        Livewire::test(EmployeeDocumentIndex::class)
            ->call('confirmDelete', $doc->id)
            ->call('delete');

        $this->assertSoftDeleted('employee_documents', ['id' => $doc->id]);
        Storage::disk($disk)->assertMissing($doc->file_path);
    }
}
