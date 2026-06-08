<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Label Plating</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .control-panel {
            background-color: #ffffff;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .print-pages {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            width: 100%;
        }

        .label-container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-content: flex-start;
            gap: 15px 25px;
            width: 860px; /* Fits 2 columns */
            height: 1200px; /* Fits 4 rows */
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 30px 20px; /* Margins inside page */
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .thermal-label {
            background-color: #ffffff;
            width: 380px;
            min-width: 380px;
            height: 250px;
            min-height: 250px;
            padding: 8px 10px;
            border: 2px solid #000;
            border-radius: 4px;
            box-sizing: border-box;
            position: relative;
            color: #000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .label-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .label-logo-img {
            height: 22px;
            width: auto;
            object-fit: contain;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            letter-spacing: 0.3px;
        }

        .label-footer {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 6px;
            letter-spacing: 0.5px;
            color: #000;
            text-transform: uppercase;
        }

        .label-content {
            display: flex;
            align-items: stretch;
            gap: 8px;
            flex-grow: 1;
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100px;
            flex-shrink: 0;
        }

        .qr-image {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border: 1.5px solid #000;
            padding: 2px;
        }

        .qr-text {
            font-size: 8px;
            font-weight: bold;
            word-break: break-all;
            text-align: center;
            margin-top: 4px;
            color: #000;
            max-width: 90px;
            line-height: 1.2;
        }

        .details-section {
            flex-grow: 1;
            width: 0;
            display: flex;
        }

        .details-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            table-layout: fixed;
        }

        .details-table td {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 4px;
            vertical-align: middle;
            color: #000;
            border: 1.5px solid #000;
        }

        .details-table td.label {
            width: 42%;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .details-table td.value {
            width: 58%;
            word-break: break-word;
        }

        /* PREVIEW MODE STYLES */
        body.preview-mode {
            background-color: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
            display: flex !important;
            justify-content: center !important;
        }

        body.preview-mode .print-pages {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            width: 100% !important;
            gap: 0 !important;
        }

        body.preview-mode .label-container {
            width: 400px !important;
            height: auto !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            display: flex !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
            gap: 15px !important;
        }

        body.preview-mode .thermal-label {
            margin: 0 !important;
        }

        /* PRINT MEDIA RULES */
        @media print {
            body {
                background-color: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .control-panel {
                display: none !important;
            }

            body.preview-mode .print-pages,
            .print-pages {
                transform: none !important;
                display: block !important;
                width: 100% !important;
                gap: 0 !important;
            }

            body.preview-mode .label-container,
            .label-container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: wrap !important;
                justify-content: flex-start !important;
                align-content: flex-start !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                page-break-after: always;
                page-break-inside: avoid;
                width: 210mm !important;
                height: 297mm !important;
                padding: 10mm 5mm !important;
                gap: 5mm 5mm !important;
                background-color: #ffffff !important;
            }

            .label-container:last-child {
                page-break-after: avoid;
            }

            body.preview-mode .thermal-label,
            .thermal-label {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                width: 80mm !important;
                height: 52mm !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="{{ request()->has('preview') ? 'preview-mode' : '' }}">

    <div class="print-pages">
        @foreach(array_chunk($labels, 8) as $pageLabels)
            <div class="label-container">
                @foreach($pageLabels as $label)
                    <div class="thermal-label">
                        <div class="label-header">
                            <img src="{{ asset('master item/ipp.jpg') }}" class="label-logo-img" alt="Logo">
                            <span class="company-name">PT INDOPLAT PERKASA PURNAMA</span>
                        </div>
                        <div class="label-content">
                            <div class="qr-section">
                                <img src="{{ $label['qr_image'] }}" class="qr-image" alt="QR Code">
                                <div class="qr-text">{{ $label['qr_text'] }}</div>
                            </div>
                            <div class="details-section">
                                <table class="details-table">
                                    @foreach($label['details'] as $key => $value)
                                        <tr>
                                            <td class="label">{{ $key }}</td>
                                            <td class="value">: {{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        <div class="label-footer">
                            {{ str_ireplace('LABEL ', '', $label['title']) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    @if(request()->has('preview'))
    <script>
        function resizeAndScale() {
            var container = document.querySelector('.print-pages');
            if (!container) return;
            
            var labelWidth = 410; 
            var viewportWidth = window.innerWidth;
            
            // Calculate scale based on viewport width
            var scale = viewportWidth / labelWidth;
            
            // Cap scale at 1.6 to prevent huge previews on large displays
            scale = Math.min(scale, 1.6);
            
            // Apply zoom scaling (keeps document height/layout scaled accordingly)
            container.style.zoom = scale;
            
            // Tell the parent iframe to resize its height to fit the new zoomed height
            if (window.parent && window.parent.document) {
                var iframes = window.parent.document.querySelectorAll('iframe');
                for (var i = 0; i < iframes.length; i++) {
                    if (iframes[i].contentWindow === window) {
                        iframes[i].style.height = 'auto';
                        iframes[i].style.height = (document.body.scrollHeight + 15) + 'px';
                        break;
                    }
                }
            }
        }
        
        window.addEventListener('load', resizeAndScale);
        window.addEventListener('resize', resizeAndScale);
        document.addEventListener('DOMContentLoaded', resizeAndScale);
        // Instant trigger with small timeout to guarantee layout completes
        setTimeout(resizeAndScale, 50);
    </script>
    @endif
</body>
</html>
