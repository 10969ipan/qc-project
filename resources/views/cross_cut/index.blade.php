@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Laporan Cross Cut Checksheet</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Part</th>
                            <th>Shift</th>
                            <th>Tanggal Jam Produksi</th>
                            <th>Tanggal Jam QC</th>
                            <th>Hasil Cross Cut</th>
                            <th>Kimia</th>
                            <th>Posisi Remark</th>
                            <th>Result Remark</th>
                            <th>Keterangan</th>
                            <th>Cycle Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checksheets as $checksheet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $checksheet->item->name }}</td>
                                <td>{{ $checksheet->shift }}</td>
                                <td>{{ $checksheet->production_datetime }}</td>
                                <td>{{ $checksheet->qc_datetime }}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm view-image-btn" data-id="{{ $checksheet->id }}" data-toggle="modal" data-target="#imageModal">
                                        View Image
                                    </button>
                                </td>
                                <td>
                                    Copper: {{ $checksheet->chemical_copper ?? '-' }},
                                    Nikel: {{ $checksheet->chemical_nikel ?? '-' }},
                                    Eching: {{ $checksheet->chemical_eching ?? '-' }},
                                    Abu: {{ $checksheet->chemical_abu ?? '-' }}
                                </td>
                                <td>{{ $checksheet->position_remark_judgment }} - {{ $checksheet->position_remark_no_lot }}</td>
                                <td>{{ $checksheet->result_remark }}</td>
                                <td>{{ $checksheet->keterangan }}</td>
                                <td>{{ $checksheet->cycle_time ? $checksheet->cycle_time . 's' : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $checksheets->links() }}
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Cross Cut Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Cross Cut Image">
                <p id="modalItemName" class="mt-2"></p>
                <p id="modalQcDatetime"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const viewImageButtons = document.querySelectorAll('.view-image-btn');
        const modalImage = document.getElementById('modalImage');
        const modalItemName = document.getElementById('modalItemName');
        const modalQcDatetime = document.getElementById('modalQcDatetime');

        viewImageButtons.forEach(button => {
            button.addEventListener('click', function () {
                const checksheetId = this.getAttribute('data-id');
                
                fetch(`/checksheet/cross-cut/${checksheetId}`)
                    .then(response => response.json())
                    .then(data => {
                        modalImage.src = data.image_url;
                        modalItemName.textContent = `Item: ${data.item_name}`;
                        modalQcDatetime.textContent = `QC Datetime: ${data.qc_datetime}`;
                    })
                    .catch(error => console.error('Error fetching image data:', error));
            });
        });
    });
</script>
@endpush
