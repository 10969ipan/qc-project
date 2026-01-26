{{-- Modal Tambah Record --}}
<div class="modal fade" id="modalTambahRecord" tabindex="-1" role="dialog" aria-labelledby="modalTambahRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahRecordLabel"><i class="fas fa-plus-circle mr-2"></i>Tambah Data
                    Claim</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.customer-claim-records.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        {{-- Basic Info --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Claim <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="tanggal_claim" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="customer" class="form-control form-control-sm uppercase-input"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Plant/UP Customer</label>
                                <input type="text" name="plant_up_customer"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Claim Type <span
                                        class="text-danger">*</span></label>
                                <select name="claim_type" class="form-control form-control-sm" required>
                                    <option value="OFFICIAL">OFFICIAL</option>
                                    <option value="NON OFFICIAL">NON OFFICIAL</option>
                                    <option value="SUSPECT">SUSPECT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">No. Report (Dokumen)</label>
                                <input type="text" name="no_report"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Source Type</label>
                                <select name="source_type" class="form-control form-control-sm">
                                    <option value="EKSTERNAL">EKSTERNAL</option>
                                    <option value="INTERNAL">INTERNAL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Project (NM/MP)</label>
                                <select name="project" class="form-control form-control-sm">
                                    <option value="MP">MP</option>
                                    <option value="NM">NM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Plant IPP <span
                                        class="text-danger">*</span></label>
                                <select name="plant_id" class="form-control form-control-sm" required>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant->id }}" {{ $plantId == $plant->id ? 'selected' : '' }}>
                                            {{ $plant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Nama Part <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_part" class="form-control form-control-sm uppercase-input"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Problem <span class="text-danger">*</span></label>
                                <textarea name="problem" class="form-control form-control-sm uppercase-input" rows="2"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Kategori Defect</label>
                                <input type="text" name="kategori_defect"
                                    class="form-control form-control-sm uppercase-input"
                                    placeholder="Appearance, Function, etc.">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Kategori Penyimpangan</label>
                                <input type="text" name="kategori_penyimpangan"
                                    class="form-control form-control-sm uppercase-input" placeholder="4M, IPQ, etc.">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Qty (pcs) <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="qty" class="form-control form-control-sm" value="0" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Total Cost (Rp)</label>
                                <input type="number" step="0.01" name="total_cost" class="form-control form-control-sm"
                                    value="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Initial Operator</label>
                                <input type="text" name="initial_operator"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Initial Inspektor</label>
                                <input type="text" name="initial_inspektor"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Frek</label>
                                <input type="text" name="frek" class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold text-nowrap">% Frek</label>
                                <input type="text" name="persen_frek"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Action Taken</label>
                                <textarea name="action_taken" class="form-control form-control-sm uppercase-input"
                                    rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Feedback</label>
                                <textarea name="feedback" class="form-control form-control-sm uppercase-input"
                                    rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status Feedback</label>
                                <input type="text" name="status_feedback"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status CM</label>
                                <input type="text" name="status_cm"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Monitoring</label>
                                <input type="text" name="monitoring"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Evaluasi</label>
                                <input type="text" name="evaluasi" class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="small font-weight-bold">Monitoring Status</label>
                                <select name="monitoring_status" class="form-control form-control-sm">
                                    <option value="OPEN">OPEN</option>
                                    <option value="CLOSE">CLOSE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Record --}}
<div class="modal fade" id="modalEditRecord" tabindex="-1" role="dialog" aria-labelledby="modalEditRecordLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditRecordLabel"><i class="fas fa-edit mr-2"></i>Edit Data Claim</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditRecord" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Same structure as Add Modal --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Tanggal Claim <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="tanggal_claim" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="customer" class="form-control form-control-sm uppercase-input"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Plant/UP Customer</label>
                                <input type="text" name="plant_up_customer"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Claim Type <span
                                        class="text-danger">*</span></label>
                                <select name="claim_type" class="form-control form-control-sm" required>
                                    <option value="OFFICIAL">OFFICIAL</option>
                                    <option value="NON OFFICIAL">NON OFFICIAL</option>
                                    <option value="SUSPECT">SUSPECT</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">No. Report (Dokumen)</label>
                                <input type="text" name="no_report"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Source Type</label>
                                <select name="source_type" class="form-control form-control-sm">
                                    <option value="EKSTERNAL">EKSTERNAL</option>
                                    <option value="INTERNAL">INTERNAL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Project (NM/MP)</label>
                                <select name="project" class="form-control form-control-sm">
                                    <option value="MP">MP</option>
                                    <option value="NM">NM</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Plant IPP <span
                                        class="text-danger">*</span></label>
                                <select name="plant_id" class="form-control form-control-sm" required>
                                    @foreach($plants as $plant)
                                        <option value="{{ $plant->id }}">
                                            {{ $plant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Nama Part <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_part" class="form-control form-control-sm uppercase-input"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Problem <span class="text-danger">*</span></label>
                                <textarea name="problem" class="form-control form-control-sm uppercase-input" rows="2"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Kategori Defect</label>
                                <input type="text" name="kategori_defect"
                                    class="form-control form-control-sm uppercase-input"
                                    placeholder="Appearance, Function, etc.">
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Kategori Penyimpangan</label>
                                <input type="text" name="kategori_penyimpangan"
                                    class="form-control form-control-sm uppercase-input" placeholder="4M, IPQ, etc.">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Qty (pcs) <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="qty" class="form-control form-control-sm" required>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Total Cost (Rp)</label>
                                <input type="number" step="0.01" name="total_cost" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Initial Operator</label>
                                <input type="text" name="initial_operator"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Initial Inspektor</label>
                                <input type="text" name="initial_inspektor"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Frek</label>
                                <input type="text" name="frek" class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold text-nowrap">% Frek</label>
                                <input type="text" name="persen_frek"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Action Taken</label>
                                <textarea name="action_taken" class="form-control form-control-sm uppercase-input"
                                    rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Feedback</label>
                                <textarea name="feedback" class="form-control form-control-sm uppercase-input"
                                    rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status Feedback</label>
                                <input type="text" name="status_feedback"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status CM</label>
                                <input type="text" name="status_cm"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Monitoring</label>
                                <input type="text" name="monitoring"
                                    class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="small font-weight-bold">Evaluasi</label>
                                <input type="text" name="evaluasi" class="form-control form-control-sm uppercase-input">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="small font-weight-bold">Monitoring Status</label>
                                <select name="monitoring_status" class="form-control form-control-sm">
                                    <option value="OPEN">OPEN</option>
                                    <option value="CLOSE">CLOSE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>