<form action="{{ route('cross_cut_painting.update', $checksheet->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @if(auth()->user()->role === 'admin')
        <input type="hidden" name="plant" value="{{ request('plant') ?? $checksheet->plant_code }}">
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="text-center">
                <tr>
                    <th>Item Part</th>
                    <th>Tanggal / Shift</th>
                    <th>Hasil Foto Cross Cut / Tap Test & Pencil Scratch</th>
                    <th>Judgement</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select class="form-control" name="item_id" required>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} ({{ $item->part_number ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="form-group mb-1">
                            <label class="small font-weight-bold">Produksi</label>
                            <div class="d-flex">
                                <input type="date" class="form-control form-control-sm mr-1" name="production_date"
                                    value="{{ \Carbon\Carbon::parse($checksheet->production_datetime)->format('Y-m-d') }}"
                                    required>
                                <select class="form-control form-control-sm" name="production_shift" required>
                                    <option value="1" {{ $checksheet->production_shift == '1' ? 'selected' : '' }}>Shift 1
                                    </option>
                                    <option value="2" {{ $checksheet->production_shift == '2' ? 'selected' : '' }}>Shift 2
                                    </option>
                                    <option value="3" {{ $checksheet->production_shift == '3' ? 'selected' : '' }}>Shift 3
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">QC</label>
                            <div class="d-flex">
                                <input type="date" class="form-control form-control-sm mr-1" name="qc_date"
                                    value="{{ \Carbon\Carbon::parse($checksheet->qc_datetime)->format('Y-m-d') }}"
                                    required>
                                <select class="form-control form-control-sm" name="qc_shift" required>
                                    <option value="1" {{ $checksheet->qc_shift == '1' ? 'selected' : '' }}>Shift 1
                                    </option>
                                    <option value="2" {{ $checksheet->qc_shift == '2' ? 'selected' : '' }}>Shift 2
                                    </option>
                                    <option value="3" {{ $checksheet->qc_shift == '3' ? 'selected' : '' }}>Shift 3
                                    </option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @if ($checksheet->image_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $checksheet->image_path) }}" class="img-thumbnail"
                                    style="max-height: 100px;">
                            </div>
                        @else
                            <div class="mb-2 text-muted small">Belum ada gambar</div>
                        @endif
                        <input type="file" class="form-control-file" name="image" accept="image/*">
                        <small class="text-muted d-block mt-1">Upload gambar baru untuk mengganti</small>
                    </td>
                    <td>
                        <select class="form-control" name="position_remark_judgment" required>
                            <option value="OK" {{ $checksheet->position_remark_judgment == 'OK' ? 'selected' : '' }}>OK
                            </option>
                            <option value="NG" {{ $checksheet->position_remark_judgment == 'NG' ? 'selected' : '' }}>NG
                            </option>
                        </select>
                    </td>
                    <td>
                        <div class="form-group mb-2" id="editNextProsesContainer"
                            style="{{ $checksheet->position_remark_judgment == 'NG' ? '' : 'display: none;' }}">
                            <label class="small font-weight-bold text-danger">Next Proses</label>
                            <select class="form-control form-control-sm" name="next_proses" id="editNextProses">
                                <option value="">-- Pilih --</option>
                                <option value="CRUSHING" {{ $checksheet->next_proses == 'CRUSHING' ? 'selected' : '' }}>
                                    CRUSHING</option>
                                <option value="SORTIR" {{ $checksheet->next_proses == 'SORTIR' ? 'selected' : '' }}>SORTIR
                                </option>
                                <option value="FINISHING" {{ $checksheet->next_proses == 'FINISHING' ? 'selected' : '' }}>
                                    FINISHING</option>
                                <option value="REPAIR" {{ $checksheet->next_proses == 'REPAIR' ? 'selected' : '' }}>REPAIR
                                </option>
                                <option value="MARKING+FINISHING+PACKING" {{ $checksheet->next_proses == 'MARKING+FINISHING+PACKING' ? 'selected' : '' }}>
                                    MARKING+FINISHING+PACKING</option>
                            </select>
                        </div>
                        <textarea class="form-control" name="keterangan" rows="2"
                            placeholder="Keterangan...">{{ $checksheet->keterangan }}</textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="updateBtn">Simpan Perubahan</button>
    </div>
</form>

<script>
    // Logic for combining date and existing logic time
    $('#updateBtn').click(function (e) {
        if ($('select[name="position_remark_judgment"]').val() === 'NG' && !$('#editNextProses').val()) {
            e.preventDefault();
            alert('Harap pilih Next Proses untuk judgment NG!');
            return false;
        }
    });

    $('select[name="position_remark_judgment"]').change(function () {
        if ($(this).val() == 'NG') {
            $('#editNextProsesContainer').slideDown();
        } else {
            $('#editNextProsesContainer').slideUp();
            $('#editNextProses').val('');
        }
    });
</script>
