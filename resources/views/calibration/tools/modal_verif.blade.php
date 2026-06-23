<!-- Modal Verifikasi Alat (From Tools Index) -->
<div class="modal fade" id="modalVerifikasiBaru" tabindex="-1" role="dialog" aria-labelledby="modalVerifikasiBaruLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" id="modalVerifikasiBaruLabel" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i> Input Verifikasi Alat Ukur
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.verifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year ?? date('Y') }}">
                
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Pilih Alat <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="tool_id" id="modal_verif_tool_select" class="form-control form-control-sm border-0 shadow-sm" required>
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
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Nama Alat</label>
                                <div class="col-sm-8">
                                    <input type="text" name="name_alat" id="modal_verif_name_alat" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Merk <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="merk" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">No. Seri</label>
                                <div class="col-sm-8">
                                    <input type="text" name="serial_number" id="modal_verif_serial_number" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Rentang</label>
                                <div class="col-sm-8">
                                    <input type="text" name="rentang_ukur" id="modal_verif_rentang_ukur" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Resolusi</label>
                                <div class="col-sm-8">
                                    <input type="text" name="resolusi" id="modal_verif_resolusi" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Tgl. Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Tgl. Verifikasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_verifikasi" id="modal_verif_tanggal_verifikasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Next Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" name="next_kalibrasi" id="modal_verif_next_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Freq. Kalibrasi</label>
                                <div class="col-sm-8">
                                    <input type="text" name="frekuensi_kalibrasi" id="modal_verif_frekuensi_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Judgment <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="judgment" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="-">-</option>
                                        <option value="OK">OK</option>
                                        <option value="NG">NG</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Toleransi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="std_toleransi" class="form-control form-control-sm border-0 shadow-sm" required placeholder="Std">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Acuan <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="acuan_toleransi" class="form-control form-control-sm border-0 shadow-sm" required placeholder="Acuan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-start mb-2 mt-3">
                        <label class="col-sm-2 col-form-label small font-weight-bold pt-0">Data Pengukuran</label>
                        <div class="col-sm-10">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0 text-center" style="min-width: 600px;">
                                    <thead class="bg-light small">
                                        <tr>
                                            <th style="width: 25%; background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">Nilai Ditunjukkan Alat</th>
                                            <th style="width: 25%; background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">Nilai Koreksi Alat</th>
                                            <th style="width: 20%; background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">Ketidakpastian</th>
                                            <th style="width: 25%; background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">Hasil Verifikasi <small class="text-muted d-block">(Koreksi + U)</small></th>
                                            <th style="width: 50px; background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">
                                                <button type="button" class="btn btn-xs btn-primary add-edit-schedule-row" id="modal-verif-add-row"><i class="fas fa-plus"></i></button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-verif-verification-body">
                                        <tr>
                                            <td><input type="text" name="nilai_alat[]" class="form-control form-control-sm border-0 shadow-sm text-center"></td>
                                            <td><input type="text" name="nilai_koreksi[]" class="form-control form-control-sm border-0 shadow-sm text-center calc-input"></td>
                                            <td><input type="text" name="nilai_ketidakpastian[]" class="form-control form-control-sm border-0 shadow-sm text-center calc-input"></td>
                                            <td><input type="text" name="hasil_verifikasi[]" class="form-control form-control-sm border-0 shadow-sm text-center" readonly></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm text-danger modal-verif-remove-row" disabled><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-0">
                        <label class="col-sm-2 col-form-label small font-weight-bold">Sertifikat (PDF)</label>
                        <div class="col-sm-10">
                            <input type="file" name="certification" class="form-control-file border-0 p-1 shadow-sm rounded bg-white" accept=".pdf">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>