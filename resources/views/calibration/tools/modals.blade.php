<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header py-2" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="pdfModalLabel" style="font-size: 0.9rem;">Lihat Sertifikat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="pdfFrame" src="" width="100%" height="600px" style="border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a id="downloadPdf" href="#" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Alat -->
<div class="modal fade" id="modalTambahAlat" tabindex="-1" role="dialog" aria-labelledby="modalTambahAlatLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" id="modalTambahAlatLabel" style="font-size: 1.1rem;">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i> Tambah Master Data Alat
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.tools.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Bagian <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="bagian" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Nama Alat <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name_alat" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Merk</label>
                                <div class="col-sm-9">
                                    <input type="text" name="merk" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">No. Seri <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="serial_number" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Range</label>
                                <div class="col-sm-9">
                                    <input type="text" name="range" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Resolusi</label>
                                <div class="col-sm-9">
                                    <input type="text" name="resolusi" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Tgl. Beli</label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_beli" class="form-control form-control-sm border-0 shadow-sm">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Freq. Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="frekuensi_kalibrasi" class="form-control form-control-sm border-0 shadow-sm no-autoupper" placeholder="Contoh: 1 Tahun" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Jenis Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="jenis_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="Internal">Internal</option>
                                        <option value="Eksternal">Eksternal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2" id="modal-schedule-container">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Schedule Planning</label>
                                <div class="col-sm-8">
                                    <div class="input-group input-group-sm mb-0 shadow-sm" style="border-radius: 4px; overflow: hidden;">
                                        <input type="date" name="schedule_planning[]" class="form-control border-0">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary border-0" type="button" id="modal-add-schedule-btn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-start mb-2 mt-3">
                        <label class="col-sm-2 col-form-label small font-weight-bold">Riwayat Kalibrasi</label>
                        <div class="col-sm-10">
                            <textarea name="riwayat_kalibrasi" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-0">
                        <label class="col-sm-2 col-form-label small font-weight-bold">Sertifikasi (PDF)</label>
                        <div class="col-sm-10">
                            <input type="file" name="certification" class="form-control-file border-0 p-1 shadow-sm rounded bg-white" accept=".pdf">
                        </div>
                    </div>
                </div><div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Alat -->
<div class="modal fade" id="modalEditAlat" tabindex="-1" role="dialog" aria-labelledby="modalEditAlatLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" id="modalEditAlatLabel" style="font-size: 1.1rem;">
                    <i class="fas fa-edit mr-2 text-primary"></i> Edit Master Data Alat
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAlat" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                                <div class="modal-body px-4 py-4" style="background-color: #f8fafc; max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Bagian <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="bagian" id="edit_bagian" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Nama Alat <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="name_alat" id="edit_name_alat" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Merk</label>
                                <div class="col-sm-9">
                                    <input type="text" name="merk" id="edit_merk" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">No. Seri <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="serial_number" id="edit_serial_number" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Range</label>
                                <div class="col-sm-9">
                                    <input type="text" name="range" id="edit_range" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-3 col-form-label small font-weight-bold">Resolusi</label>
                                <div class="col-sm-9">
                                    <input type="text" name="resolusi" id="edit_resolusi" class="form-control form-control-sm border-0 shadow-sm no-autoupper">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-left">
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Tgl. Beli</label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal_beli" id="edit_tanggal_beli" class="form-control form-control-sm border-0 shadow-sm">
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Freq. Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="frekuensi_kalibrasi" id="edit_frekuensi_kalibrasi" class="form-control form-control-sm border-0 shadow-sm no-autoupper" required>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-2">
                                <label class="col-sm-4 col-form-label small font-weight-bold">Jenis Kalibrasi <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select name="jenis_kalibrasi" id="edit_jenis_kalibrasi" class="form-control form-control-sm border-0 shadow-sm" required>
                                        <option value="Internal">Internal</option>
                                        <option value="Eksternal">Eksternal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-start mb-2 mt-3">
                        <label class="col-sm-2 col-form-label small font-weight-bold pt-0">Schedule Planning</label>
                        <div class="col-sm-10">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="edit-schedule-table">
                                    <thead class="bg-light small text-center">
                                        <tr>
                                            <th style="background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">Tanggal Planning</th>
                                            <th style="background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;">PR Number</th>
                                            <th width="40" style="background-color: #ffffff !important; color: #5a5c69 !important; border-bottom: 2px solid #e3e6f0;"><button type="button" class="btn btn-xs btn-success add-edit-schedule-row"><i class="fas fa-plus"></i></button></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Will be filled by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-start mb-2">
                        <label class="col-sm-2 col-form-label small font-weight-bold">Riwayat Kalibrasi</label>
                        <div class="col-sm-10">
                            <textarea name="riwayat_kalibrasi" id="edit_riwayat_kalibrasi" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-0">
                        <label class="col-sm-2 col-form-label small font-weight-bold">Sertifikasi</label>
                        <div class="col-sm-10">
                            <input type="file" name="certification" class="form-control-file" accept=".pdf">
                            <div id="edit_existing_cert" class="mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lapor Masalah -->
<div class="modal fade" id="modalReportProblem" tabindex="-1" role="dialog" aria-labelledby="modalReportProblemLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom py-3 px-4" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-gray-800" id="modalReportProblemLabel" style="font-size: 1.1rem;">
                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i> Lapor Masalah Alat
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('calibration.tools.store-problem') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plant" value="{{ $plantCode }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="calibration_tool_id" id="problem_tool_id">
                <div class="modal-body px-4 py-4" style="background-color: #f8fafc;">
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-4 col-form-label small font-weight-bold">Nama Alat</label>
                        <div class="col-sm-8">
                            <input type="text" id="problem_tool_name" class="form-control form-control-sm border-0 shadow-sm bg-light" readonly>
                        </div>
                    </div>
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-4 col-form-label small font-weight-bold">Tgl. Kejadian <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="date" name="reported_date" class="form-control form-control-sm border-0 shadow-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="form-group row align-items-center mb-2">
                        <label class="col-sm-4 col-form-label small font-weight-bold">Jenis Problem <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="problem_type" id="problem_type" class="form-control form-control-sm border-0 shadow-sm" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="ERROR">ERROR (Masih bisa diperbaiki)</option>
                                <option value="RUSAK">RUSAK (Mati total / pecah)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row align-items-start mb-2" id="action_taken_wrapper" style="display: none;">
                        <label class="col-sm-4 col-form-label small font-weight-bold pt-1">Aksi Lanjut <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="action_taken" id="action_taken" class="form-control form-control-sm border-0 shadow-sm" placeholder="Contoh: Service Internal..." list="action_suggestions">
                            <datalist id="action_suggestions">
                                <option value="SERVICE_INTERNAL">
                                <option value="SERVICE_EXTERNAL">
                                <option value="PO_GA">
                                <option value="REPLACE">
                            </datalist>
                            <div class="alert alert-danger small mt-2 py-1 mb-0" id="rusak_info" style="display: none;">
                                <i class="fas fa-exclamation-circle mr-1"></i> Alat <strong>RUSAK</strong> akan di-set <strong>BROKEN</strong> & jadwal mendatang dihapus.
                            </div>
                        </div>
                    </div>
                    <div class="form-group row align-items-start mb-2">
                        <label class="col-sm-4 col-form-label small font-weight-bold pt-1">Bukti (Opsional)</label>
                        <div class="col-sm-8">
                            <input type="file" name="evidence_report" class="form-control-file border-0 p-1 shadow-sm rounded bg-white" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted d-block mt-1">Max: 5MB (JPG/PDF)</small>
                        </div>
                    </div>
                    <div class="form-group row align-items-start mb-0">
                        <label class="col-sm-4 col-form-label small font-weight-bold pt-1">Detail Masalah <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <textarea name="description" class="form-control form-control-sm border-0 shadow-sm no-autoupper" rows="3" placeholder="Jelaskan detail masalahnya..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 px-4" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light border btn-sm px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 font-weight-bold shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
