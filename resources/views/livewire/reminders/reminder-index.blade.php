<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Reminder Dokumen</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau dokumen yang akan atau sudah kadaluarsa</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 border-l-4 border-l-red-500">
            <div class="text-2xl font-bold text-red-600">{{ $summary['critical'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Kritis (≤7 hari)</div>
        </div>
        <div class="card p-4 border-l-4 border-l-orange-500">
            <div class="text-2xl font-bold text-orange-600">{{ $summary['warning'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Peringatan (8–14 hari)</div>
        </div>
        <div class="card p-4 border-l-4 border-l-yellow-400">
            <div class="text-2xl font-bold text-yellow-600">{{ $summary['notice'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Perhatian (15–30 hari)</div>
        </div>
        <div class="card p-4 border-l-4 border-l-slate-400">
            <div class="text-2xl font-bold text-slate-600">{{ $summary['expired'] }}</div>
            <div class="text-xs text-slate-500 mt-1">Sudah Kadaluarsa</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-5">
        <div class="card-body flex flex-wrap gap-3 items-center">
            <div class="flex rounded-lg border border-slate-200 overflow-hidden">
                @foreach([['all','Semua'],['vehicle','Kendaraan'],['employee','Karyawan']] as [$val,$label])
                <button wire:click="$set('filterType', '{{ $val }}')"
                    class="px-4 py-2 text-sm font-medium transition {{ $filterType === $val ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <select wire:model.live="filterDays" class="form-select w-44">
                <option value="7">≤ 7 hari</option>
                <option value="14">≤ 14 hari</option>
                <option value="30">≤ 30 hari</option>
                <option value="60">≤ 60 hari</option>
                <option value="90">≤ 90 hari</option>
            </select>
        </div>
    </div>

    <!-- Document List -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Tipe</th>
                        <th>Milik</th>
                        <th>Kadaluarsa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sorted as $item)
                    @php
                        $days = $item['days_left'];
                        $color = $days < 0 ? 'red' : ($days <= 7 ? 'red' : ($days <= 14 ? 'orange' : 'yellow'));
                    @endphp
                    <tr>
                        <td>
                            <div class="font-medium text-slate-800">{{ $item['title'] }}</div>
                            <div class="text-xs text-slate-400">{{ $item['doc_type'] }}</div>
                        </td>
                        <td>
                            @if($item['type'] === 'vehicle')
                            <span class="badge badge-blue">🚛 Kendaraan</span>
                            @else
                            <span class="badge badge-blue">👤 Karyawan</span>
                            @endif
                        </td>
                        <td class="text-sm font-medium">{{ $item['related'] }}</td>
                        <td class="text-sm">{{ $item['expiry_date']->format('d/m/Y') }}</td>
                        <td>
                            @if($days < 0)
                            <span class="badge badge-red">EXPIRED</span>
                            @else
                            <span class="badge badge-{{ $color }}">H-{{ $days }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-slate-400 text-sm">Tidak ada dokumen yang akan kadaluarsa dalam periode ini 🎉</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
