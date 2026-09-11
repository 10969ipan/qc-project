@forelse($sortedAllTools as $tool)
    @php
        $status = $tool->status_kalibrasi;
        $isCheck = in_array($tool->id, $hiddenToolIds ?? []);
        $isOverdue = ($status === 'overdue');
    @endphp
    <tr class="hidden-tool-row" data-status="{{ $status }}" data-is-overdue="{{ $isOverdue ? '1' : '0' }}" data-search="{{ strtolower(($tool->name_alat ?? '') . ' ' . ($tool->serial_number ?? '') . ' ' . ($tool->bagian ?? '') . ' ' . ($tool->merk ?? '')) }}">
        <td class="text-center align-middle">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="hidden_tool_ids[]" value="{{ $tool->id }}" 
                    class="custom-control-input chk-hidden-tool" id="chkHiddenTool{{ $tool->id }}" 
                    {{ $isCheck ? 'checked' : '' }}>
                <label class="custom-control-label" for="chkHiddenTool{{ $tool->id }}"></label>
            </div>
        </td>
        <td class="align-middle text-nowrap">
            @if($status === 'overdue')
                <span class="badge badge-danger px-2 py-1" style="font-size: 0.65rem;" title="Melewati Jadwal Planning">
                    <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                </span>
            @elseif($status === 'calibrated')
                <span class="badge badge-success px-2 py-1" style="font-size: 0.65rem;" title="Terkalibrasi OK">
                    <i class="fas fa-check-circle mr-1"></i>Terkalibrasi
                </span>
            @elseif($status === 'due_soon')
                <span class="badge badge-warning px-2 py-1 text-dark" style="font-size: 0.65rem;" title="Mendekati Jadwal">
                    <i class="fas fa-clock mr-1"></i>Due Soon
                </span>
            @elseif($status === 'broken')
                <span class="badge badge-secondary px-2 py-1" style="font-size: 0.65rem;" title="Rusak / Tidak Digunakan">
                    <i class="fas fa-times-circle mr-1"></i>Broken
                </span>
            @else
                <span class="badge badge-light border px-2 py-1 text-secondary" style="font-size: 0.65rem;">
                    {{ strtoupper($status) }}
                </span>
            @endif
        </td>
        <td class="align-middle text-muted small" style="font-size: 0.78rem;">
            {{ $tool->bagian ?? '-' }}
        </td>
        <td class="align-middle font-weight-bold text-dark" style="font-size: 0.8rem;">
            {{ $tool->name_alat ?? '-' }}
        </td>
        <td class="align-middle text-muted small" style="font-size: 0.78rem;">
            {{ $tool->merk ?? '-' }} {{ $tool->serial_number ? '/ '.$tool->serial_number : '' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-4">Tidak ada data alat ukur ditemukan.</td>
    </tr>
@endforelse
