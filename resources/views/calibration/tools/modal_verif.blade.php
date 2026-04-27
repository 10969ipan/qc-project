<!-- Modal Verifikasi Alat (From Tools Index) -->
<div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog" aria-labelledby="modalVerifikasiBaruLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg text-left">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalVerifikasiBaruLabel">
                    <i class="fas fa-plus-circle mr-2"></i> Input Verifikasi Alat Ukur
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year ?? date('Y') }}">
                
                <div class="modal-body p-4 bg-light">
                    <div class="row">
                        {{-- Section 1: Informasi Alat --}}
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm rounded-lg h-100">
                                <div class="card-header bg-white py-3 border-bottom-0">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-search mr-2"></i> Informasi Alat
                                    </h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Pilih Alat Ukur <span class="text-danger">*</span></label>
                                        <select name="tool_id" id="modal_verif_tool_select" 
                                            class="form-control form-control-sm border-0 bg-light shadow-none" 
                                            style="border-radius: 8px;" required>
                                            <option value="">-- Pilih Alat --</option>
                                            @foreach($tools as $t)
                                                <option value="{{ $t->id }}" 
                                                    data-name="{{ $t->name_alat }}"
                                                    data-serial="{{ $t->serial_number }}" 
                                                    data-range="{{ $t->range }}"
                                                    data-resolusi="{{ $t->resolusi }}"
                                                    data-frekuensi="{{ $t->frekuensi_kalibrasi }}"
                                                    data-schedules="{{ json_encode($t->schedules->pluck('schedule_date')->map(fn($d) => $d->format('Y-m-d'))) }}">
                                                    {{ $t->name_alat }} ({{ $t->serial_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Nama Alat</label>
                                        <input type="text" name="name_alat" id="modal_verif_name_alat"
                                            class="form-control form-control-sm border-0 bg-light shadow-none" 
                                            style="border-radius: 8px;" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Merk <span class="text-danger">*</span></label>
                                        <input type="text" name="merk" 
                                            class="form-control form-control-sm border-0 bg-light shadow-none" 
                                            style="border-radius: 8px;" required>
                                    </div>
                                    <div class="row px-0">
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">No. Seri</label>
                                                <input type="text" name="serial_number" id="modal_verif_serial_number"
                                                    class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Resolusi</label>
                                                <input type="text" name="resolusi" id="modal_verif_resolusi"
                                                    class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700">Rentang Ukur (Range)</label>
                                        <input type="text" name="rentang_ukur" id="modal_verif_rentang_ukur"
                                            class="form-control form-control-sm border-0 bg-light shadow-none" 
                                            style="border-radius: 8px;" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Jadwal & Hasil --}}
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm rounded-lg h-100">
                                <div class="card-header bg-white py-3 border-bottom-0">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-calendar-check mr-2"></i> Jadwal & Hasil Kalibrasi
                                    </h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row px-0">
                                        <div class="col-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Tgl. Kalibrasi <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_kalibrasi" 
                                                    class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Tgl. Verifikasi <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_verifikasi" id="modal_verif_tanggal_verifikasi"
                                                    class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Next Kalibrasi <span class="text-danger">*</span></label>
                                                <input type="date" name="next_kalibrasi" id="modal_verif_next_kalibrasi"
                                                    class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-gray-700">Frekuensi Kalibrasi</label>
                                        <input type="text" name="frekuensi_kalibrasi" id="modal_verif_frekuensi_kalibrasi"
                                            class="form-control form-control-sm border-0 bg-light shadow-none" 
                                            style="border-radius: 8px;" required>
                                    </div>

                                    <div class="row px-0">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Judgment <span class="text-danger">*</span></label>
                                                <select name="judgment" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required>
                                                    <option value="-">-</option>
                                                    <option value="OK">OK</option>
                                                    <option value="NG">NG</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Std. Toleransi <span class="text-danger">*</span></label>
                                                <input type="text" name="std_toleransi" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required placeholder="Input Std. Toleransi">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-gray-700">Acuan Toleransi <span class="text-danger">*</span></label>
                                                <input type="text" name="acuan_toleransi" class="form-control form-control-sm border-0 bg-light shadow-none" 
                                                    style="border-radius: 8px;" required placeholder="Input Acuan Toleransi">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold text-gray-700 d-flex align-items-center">
                                            Upload PDF (Sertifikat)
                                            <i class="fas fa-file-upload ml-2 text-muted"></i>
                                        </label>
                                        <div class="custom-file custom-file-sm">
                                            <input type="file" name="certification" class="custom-file-input" id="modal_verif_cert_file" accept=".pdf">
                                            <label class="custom-file-label border-0 bg-light" for="modal_verif_cert_file" style="border-radius: 8px;">Pilih file PDF...</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-light">

                    {{-- Measurement Table --}}
                    <div class="card border-0 shadow-sm rounded-lg">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-ruler-combined mr-2"></i> Data Pengukuran & Koreksi
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-xs px-3" id="modal-verif-add-row" style="border-radius: 20px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="card-body pt-0 px-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-center" style="min-width: 600px;">
                                    <thead class="bg-dark text-white small text-uppercase">
                                        <tr>
                                            <th class="py-3 border-0" style="width: 25%;">Nilai Ditunjukkan Alat</th>
                                            <th class="py-3 border-0" style="width: 25%;">Nilai Koreksi Alat</th>
                                            <th class="py-3 border-0" style="width: 20%;">Ketidakpastian</th>
                                            <th class="py-3 border-0" style="width: 25%;">Hasil Verifikasi <small class="d-block text-white-50 opacity-75">(Koreksi + U)</small></th>
                                            <th class="py-3 border-0" style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-verif-verification-body">
                                        <tr>
                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm border-0 bg-light mx-auto" style="border-radius: 6px; width: 80%;"></td>
                                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm border-0 bg-light mx-auto calc-input" style="border-radius: 6px; width: 80%;"></td>
                                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm border-0 bg-light mx-auto calc-input" style="border-radius: 6px; width: 80%;"></td>
                                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm border-0 bg-light mx-auto shadow-none" style="border-radius: 6px; width: 80%;" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm text-danger modal-verif-remove-row" title="Hapus" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 p-4 justify-content-end shadow-sm">
                    <button type="button" class="btn btn-light btn-sm px-4 mr-2" data-dismiss="modal" style="border-radius: 20px;">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-5 shadow-sm" style="border-radius: 20px; font-weight: 600;">
                        <i class="fas fa-save mr-2"></i> SIMPAN DATA VERIFIKASI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
