@extends('layouts.admin')

@section('title', 'Master Data Items')

@section('content')
    <x-plant-header title="Master Data Items" :plant="request()->get('plant')" />


    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Item</h6>
            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                <a href="{{ route('admin.items.create', ['plant' => request('plant')]) }}"
                    class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Item
                </a>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.index') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 mb-3">
                        <label for="name" class="font-weight-bold">Nama Item</label>
                        <input type="text" name="name" class="form-control form-control-sm shadow-sm"
                            value="{{ request('name') }}" placeholder="Cari Nama Item...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="category" class="font-weight-bold">Kategori</label>
                        <select name="category" class="form-control form-control-sm shadow-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="customer" class="font-weight-bold">Customer</label>
                        <input type="text" name="customer" class="form-control form-control-sm shadow-sm"
                            value="{{ request('customer') }}" placeholder="Cari Customer...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="part_number" class="font-weight-bold">No Part</label>
                        <input type="text" name="part_number" class="form-control form-control-sm shadow-sm"
                            value="{{ request('part_number') }}" placeholder="Cari No Part...">
                    </div>
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
                        <label for="sap_code" class="font-weight-bold">Kode SAP</label>
                        <input type="text" name="sap_code" class="form-control form-control-sm shadow-sm"
                            value="{{ request('sap_code') }}" placeholder="Kode SAP...">
                    </div>
                    {{-- Preserve plant parameter for all users --}}
                    @if(request('plant'))
                        <input type="hidden" name="plant" value="{{ request('plant') }}">
                    @endif
                    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 mb-3">
                        <label class="d-none d-xl-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-sm mr-2 shadow-sm">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.items.index', ['plant' => request('plant')]) }}"
                            class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Standard</th>
                            <th>Nama Item</th>
                            <th>Kategori</th>
                            <th>Customer</th>
                            <th>No Part</th>
                            <th>Kode SAP</th>
                            <th>Plant</th>
                            @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr>
                                <td>{{ $items->firstItem() + $index }}</td>
                                <td>
                                    @if($item->file_path)
                                        <button type="button" class="btn btn-primary btn-sm view-pdf-btn" data-toggle="modal"
                                            data-target="#pdfModal" data-src="{{ route('items.pdf', $item->id) }}">
                                            <i class="fas fa-file-pdf"></i> Lihat
                                        </button>
                                    @else
                                        <span class="text-muted">No File</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->name }}</td>
                                <td class="text-nowrap">
                                    @if($item->category)
                                        @php
                                            $badgeClass = match ($item->category->name) {
                                                'Sub Assy' => 'badge-primary',
                                                'Inprosess' => 'badge-success',
                                                'Cross Cut Plating' => 'badge-warning',
                                                'Cross Cut Painting' => 'badge-info',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $item->category->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $item->customer }}</td>
                                <td class="text-nowrap">{{ $item->part_number }}</td>
                                <td class="text-nowrap">{{ $item->sap_code ?? '-' }}</td>
                                <td>
                                    <span
                                        class="badge {{ optional($item->plant)->code === 'jakarta' ? 'badge-primary' : 'badge-info' }}">
                                        {{ strtoupper(optional($item->plant)->name ?? '-') }}
                                    </span>
                                </td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                                    <td class="text-nowrap">
                                                        <a href="{{ route('admin.items.edit', [
                                        'item' => $item->id,
                                        'page' => request('page', 1),
                                        'name' => request('name'),
                                        'category' => request('category'),
                                        'customer' => request('customer'),
                                        'part_number' => request('part_number'),
                                        'plant' => request('plant')
                                    ]) }}" class="btn btn-warning btn-sm" style="min-width: 110px;">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST"
                                                            class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                                            <input type="hidden" name="name" value="{{ request('name') }}">
                                                            <input type="hidden" name="category" value="{{ request('category') }}">
                                                            <input type="hidden" name="customer" value="{{ request('customer') }}">
                                                            <input type="hidden" name="part_number" value="{{ request('part_number') }}">
                                                            <input type="hidden" name="plant" value="{{ request('plant') }}">
                                                            <button type="button" class="btn btn-danger btn-sm delete-btn"
                                                                style="min-width: 110px;">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        @for($i = count($items); $i < 10; $i++)
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @if(!in_array(auth()->user()->role, ['manager', 'asst_manager', 'inspector']))
                                    <td>&nbsp;</td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $items->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- PDF Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Lihat PDF</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-2 align-items-center">
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="prevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="pageInfo" class="mr-2">Page 1 of ?</span>
                        <button type="button" class="btn btn-secondary btn-sm mr-2" id="nextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="border-left pl-2 ml-2">
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="zoomIn">
                                <i class="fas fa-search-plus"></i> Zoom In
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mr-2" id="zoomReset">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="zoomOut">
                                <i class="fas fa-search-minus"></i> Zoom Out
                            </button>
                        </div>
                    </div>
                    <div class="text-center bg-dark" style="overflow: auto; max-height: 80vh;">
                        <canvas id="the-canvas" style="border: 1px solid black; direction: ltr;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize PDF.js
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

                let pdfDoc = null;
                let pageNum = 1;
                let pageRendering = false;
                let pageNumPending = null;
                let scale = 1.0;
                const canvas = document.getElementById('the-canvas');
                const ctx = canvas.getContext('2d');

                /**
                 * Get page info from document, resize canvas accordingly, and render page.
                 * @param num Page number.
                 */
                function renderPage(num) {
                    pageRendering = true;
                    // Fetch page
                    pdfDoc.getPage(num).then(function (page) {
                        const viewport = page.getViewport({ scale: scale });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Render PDF page into canvas context
                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        const renderTask = page.render(renderContext);

                        // Wait for render to finish
                        renderTask.promise.then(function () {
                            pageRendering = false;
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });

                    // Update page counters
                    document.getElementById('pageInfo').textContent = 'Page ' + num + ' of ' + pdfDoc.numPages;
                }

                /**
                 * If another page rendering in progress, waits until the rendering is
                 * finised. Otherwise, executes rendering immediately.
                 */
                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                /**
                 * Displays previous page.
                 */
                function onPrevPage() {
                    if (pageNum <= 1) {
                        return;
                    }
                    pageNum--;
                    queueRenderPage(pageNum);
                }
                document.getElementById('prevPage').addEventListener('click', onPrevPage);

                /**
                 * Displays next page.
                 */
                function onNextPage() {
                    if (pageNum >= pdfDoc.numPages) {
                        return;
                    }
                    pageNum++;
                    queueRenderPage(pageNum);
                }
                document.getElementById('nextPage').addEventListener('click', onNextPage);

                // Zoom controls
                document.getElementById('zoomIn').addEventListener('click', function () {
                    scale += 0.25;
                    queueRenderPage(pageNum);
                });

                document.getElementById('zoomOut').addEventListener('click', function () {
                    if (scale > 0.25) {
                        scale -= 0.25;
                        queueRenderPage(pageNum);
                    }
                });

                document.getElementById('zoomReset').addEventListener('click', function () {
                    scale = 1.0;
                    queueRenderPage(pageNum);
                });

                // Handle Modal Open
                $('.view-pdf-btn').on('click', function () {
                    const url = $(this).data('src');

                    // Reset state
                    pdfDoc = null;
                    pageNum = 1;
                    scale = 1.0;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    document.getElementById('pageInfo').textContent = 'Loading...';

                    // Load PDF
                    pdfjsLib.getDocument(url).promise.then(function (pdfDoc_) {
                        pdfDoc = pdfDoc_;
                        document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                        renderPage(pageNum);
                    }, function (reason) {
                        // PDF loading error
                        console.error(reason);
                        let errorMsg = 'Error loading PDF. ';
                        if (reason.name === 'MissingPDFException') {
                            errorMsg += 'The PDF file could not be found on the server. Please contact admin or re-upload the file.';
                        } else {
                            errorMsg += reason.message || reason;
                        }

                        document.getElementById('pageInfo').textContent = 'Error: ' + reason.name;
                        alert(errorMsg);
                    });
                });
            });
        </script>

        {{-- SweetAlert for Delete Confirmation --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle delete button clicks
                document.querySelectorAll('.delete-btn').forEach(button => {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const form = this.closest('form');

                        Swal.fire({
                            title: 'Apakah Anda yakin?',
                            text: "Data item ini akan dihapus permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection