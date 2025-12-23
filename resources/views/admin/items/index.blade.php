@extends('layouts.admin')

@section('title', 'Master Data Items')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Data Barang</h1>
    <a href="{{ route('admin.items.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Barang
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Barang</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.items.index') }}" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="name">Nama Barang</label>
                    <input type="text" name="name" class="form-control" value="{{ request('name') }}" placeholder="Cari Nama Barang...">
                </div>
                <div class="col-md-3">
                    <label for="customer">Customer</label>
                    <input type="text" name="customer" class="form-control" value="{{ request('customer') }}" placeholder="Cari Customer...">
                </div>
                <div class="col-md-3">
                    <label for="part_number">No Part</label>
                    <input type="text" name="part_number" class="form-control" value="{{ request('part_number') }}" placeholder="Cari No Part...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">Cari</button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Standard</th>
                        <th>Nama Barang</th>
                        <th>Customer</th>
                        <th>No Part</th>
                        <th>Std Cycle Time (s)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $items->firstItem() + $index }}</td>
                        <td>
                            @if($item->file_path)
                                <button type="button" class="btn btn-primary btn-sm view-pdf-btn" data-toggle="modal" data-target="#pdfModal" data-src="{{ asset($item->file_path) }}">
                                    <i class="fas fa-file-pdf"></i> Lihat
                                </button>
                            @else
                                <span class="text-muted">No File</span>
                            @endif
                        </td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->customer }}</td>
                        <td>{{ $item->part_number }}</td>
                        <td>{{ $item->standard_cycle_time }}</td>
                        <td>
                            <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
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
    document.addEventListener('DOMContentLoaded', function() {
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
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({scale: scale});
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                const renderTask = page.render(renderContext);

                // Wait for render to finish
                renderTask.promise.then(function() {
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
        document.getElementById('zoomIn').addEventListener('click', function() {
            scale += 0.25;
            queueRenderPage(pageNum);
        });

        document.getElementById('zoomOut').addEventListener('click', function() {
            if (scale > 0.25) {
                scale -= 0.25;
                queueRenderPage(pageNum);
            }
        });

        document.getElementById('zoomReset').addEventListener('click', function() {
            scale = 1.0;
            queueRenderPage(pageNum);
        });

        // Handle Modal Open
        $('.view-pdf-btn').on('click', function() {
            const url = $(this).data('src');
            
            // Reset state
            pdfDoc = null;
            pageNum = 1;
            scale = 1.0;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('pageInfo').textContent = 'Loading...';

            // Load PDF
            pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('pageInfo').textContent = 'Page 1 of ' + pdfDoc.numPages;
                renderPage(pageNum);
            }, function (reason) {
                // PDF loading error
                console.error(reason);
                alert('Error loading PDF: ' + reason);
            });
        });
    });
</script>
@endpush
@endsection
