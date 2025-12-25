@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Create Cross Cut Checksheet</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('cross_cut.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Item Part -->
                    <div class="col-md-6 form-group">
                        <label for="item_id">Item Part</label>
                        <select class="form-control" id="item_id" name="item_id" required>
                            <option value="">-- Select Item --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Tanggal Jam Produksi -->
                    <div class="col-md-6 form-group">
                        <label for="production_datetime">Tanggal Jam Produksi</label>
                        <input type="datetime-local" class="form-control" id="production_datetime" name="production_datetime" required>
                    </div>

                    <!-- Tanggal Jam QC -->
                    <div class="col-md-6 form-group">
                        <label for="qc_datetime">Tanggal Jam QC</label>
                        <input type="datetime-local" class="form-control" id="qc_datetime" name="qc_datetime" required>
                    </div>
                </div>

                <!-- Ambil Gambar -->
                <div class="form-group">
                    <label for="image">Ambil Gambar</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                </div>
                
                <!-- Kimia -->
                <div class="form-group">
                    <label>Kimia</label>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="chemical_copper">Copper</label>
                            <input type="text" class="form-control" id="chemical_copper" name="chemical_copper">
                        </div>
                        <div class="col-md-3">
                            <label for="chemical_nikel">Nikel</label>
                            <input type="text" class="form-control" id="chemical_nikel" name="chemical_nikel">
                        </div>
                        <div class="col-md-3">
                            <label for="chemical_eching">Eching</label>
                            <input type="text" class="form-control" id="chemical_eching" name="chemical_eching">
                        </div>
                        <div class="col-md-3">
                            <label for="chemical_abu">Abu</label>
                            <input type="text" class="form-control" id="chemical_abu" name="chemical_abu">
                        </div>
                    </div>
                </div>

                <!-- Posisi Remark -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="position_remark_judgment">Judgment</label>
                        <select class="form-control" id="position_remark_judgment" name="position_remark_judgment" required>
                            <option value="OK">OK</option>
                            <option value="NG">NG</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="position_remark_no_lot">No Lot</label>
                        <input type="text" class="form-control" id="position_remark_no_lot" name="position_remark_no_lot" required>
                    </div>
                </div>

                <!-- Result Remark -->
                <div class="form-group">
                    <label for="result_remark">Result Remark</label>
                    <input type="text" class="form-control" id="result_remark" name="result_remark">
                </div>

                <!-- Keterangan -->
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
@endsection
