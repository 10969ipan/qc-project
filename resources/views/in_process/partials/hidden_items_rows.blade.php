@forelse($sortedAllItems as $item)
    @php
        $normPn = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $item->part_number ?? ''));
        $hasDbStandards = !empty($item->dimension_standards) && is_array($item->dimension_standards) && count($item->dimension_standards) > 0;
        $hasConsolidatedStandards = !empty($normPn) && isset($partDimensionStandards[$normPn]) && !empty($partDimensionStandards[$normPn]);
        $hasDimension = $hasDbStandards || $hasConsolidatedStandards;
        $hasNg = in_array($item->id, $ngItemIds ?? []);
        $isCheck = in_array($item->id, $hiddenItemIds ?? []);
    @endphp
    <tr class="hidden-item-row" data-has-dimension="{{ $hasDimension ? '1' : '0' }}" data-has-ng="{{ $hasNg ? '1' : '0' }}" data-search="{{ strtolower(($item->name ?? '') . ' ' . ($item->part_number ?? '') . ' ' . ($item->customer ?? '')) }}">
        <td class="text-center align-middle">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="hidden_item_ids[]" value="{{ $item->id }}" 
                    class="custom-control-input chk-hidden-item" id="chkHiddenItem{{ $item->id }}" 
                    {{ $isCheck ? 'checked' : '' }}>
                <label class="custom-control-label" for="chkHiddenItem{{ $item->id }}"></label>
            </div>
        </td>
        <td class="align-middle text-nowrap">
            @if($hasDimension)
                <span class="badge badge-light text-success border border-success px-2 py-1" style="font-size: 0.68rem;" title="Memiliki standar dimensi">
                    <i class="fas fa-ruler-combined mr-1"></i>Cek Dimensi
                </span>
                @if($hasNg)
                    <span class="badge badge-danger px-2 py-1 ml-1" style="font-size: 0.65rem;" title="Item ini memiliki riwayat hasil NG Dimensi">
                        <i class="fas fa-exclamation-circle mr-1"></i>NG Dimensi
                    </span>
                @endif
            @else
                <span class="badge badge-light border px-2 py-1" style="font-size: 0.68rem; color: #d97706; background-color: #fffbeb; border-color: #fde68a !important;" title="Tidak ada standar dimensi (Hanya Check Visual)">
                    <i class="fas fa-eye mr-1"></i>Visual Only
                </span>
            @endif
        </td>
        <td class="align-middle font-weight-bold text-dark" style="font-size: 0.8rem;">
            {{ $item->name ?? '-' }}
        </td>
        <td class="align-middle text-muted small" style="font-size: 0.78rem;">
            {{ $item->part_number ?? '-' }}
        </td>
        <td class="align-middle text-muted small" style="font-size: 0.78rem;">
            {{ $item->customer ?? '-' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-4">Tidak ada data item ditemukan.</td>
    </tr>
@endforelse
