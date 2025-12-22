@extends('layouts.admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Data Checksheet Inprocess</h1>
    <a href="{{ route('in_process.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Checksheet Inprocess</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('in_process.update', $checksheet->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="item_id">Barang</label>
                        <select name="item_id" id="item_id" class="form-control" required>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ $checksheet->item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} ({{ $item->customer }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date">Tanggal</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ $checksheet->date }}" required>
                    </div>
                </div>
                @if(auth()->user()->role !== 'inspector')
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="created_time">Jam Input</label>
                        <input type="time" name="created_time" id="created_time" class="form-control" value="{{ $checksheet->created_at->format('H:i') }}">
                    </div>
                </div>
                @endif
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="shift">Shift</label>
                        <select name="shift" id="shift" class="form-control" required>
                            <option value="1" {{ $checksheet->shift == '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ $checksheet->shift == '2' ? 'selected' : '' }}>2</option>
                            <option value="3" {{ $checksheet->shift == '3' ? 'selected' : '' }}>3</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="total_qty">Total Qty</label>
                        <input type="number" name="total_qty" id="total_qty" class="form-control" value="{{ $checksheet->total_qty }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sampling_qty">Sampling Qty</label>
                        <input type="number" name="sampling_qty" id="sampling_qty" class="form-control" value="{{ $checksheet->sampling_qty }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="total_ok">Total OK</label>
                        <input type="number" name="total_ok" id="total_ok" class="form-control" value="{{ $checksheet->total_ok }}" min="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="total_ng">Total NG</label>
                        <input type="number" name="total_ng" id="total_ng" class="form-control" value="{{ $checksheet->total_ng }}" min="0" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="judgment">Judgment</label>
                        <select name="judgment" id="judgment" class="form-control" required>
                            <option value="OK" {{ $checksheet->judgment == 'OK' ? 'selected' : '' }}>OK</option>
                            <option value="NG" {{ $checksheet->judgment == 'NG' ? 'selected' : '' }}>NG</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="operator_initials">Inisial Operator</label>
                        <input type="text" name="operator_initials" id="operator_initials" class="form-control" value="{{ $checksheet->operator_initials }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Check Dimensi</label>
                @php
                    $dimensions = json_decode($checksheet->dimension_check, true) ?? [];
                @endphp
                <table class="table table-sm table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th style="width: 15%;">Cavity</th>
                            <th>Point 1</th>
                            <th>Point 2</th>
                            <th>Point 3</th>
                            <th>Point 4</th>
                            <th>Point 5</th>
                            <th>Point 6</th>
                            <th>Point 7</th>
                            <th>Point 8</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 8; $i++)
                            <tr>
                                <td class="text-center font-weight-bold">Cav {{ $i }}</td>
                                @for ($j = 1; $j <= 3; $j++)
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="dimensions[{{ $i }}][{{ $j }}]" value="{{ $dimensions[$i][$j] ?? '' }}" placeholder="P{{ $j }}">
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label for="remarks">Keterangan</label>
                <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ $checksheet->remarks }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
