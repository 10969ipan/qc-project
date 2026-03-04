import xlsxwriter
import win32com.client
import os
import time

# ============================================================
# CONFIG
# ============================================================
TEMP_XLSX = "TEMP_REDESIGNED.xlsx"
OUTPUT_FILE = "MASTER LIST DRAWING NPD design NEW - REDESIGNED v2.xlsm"

DISPLAY_HEADERS = [
    "No", "No Dokumen", "Tanggal", "Nama Part", "Customer",
    "No Part", "Proses", "2D", "3D", "Rev 0", "Rev 1", "Rev 2",
    "Path 2D", "Path 3D", "Durasi (Jam)", "Status"
]
COL_WIDTHS = [5, 30, 14, 25, 22, 22, 32, 6, 6, 7, 7, 7, 55, 55, 14, 16]

# All 27 records parsed from the original file (embedded)
EMBEDDED_RECORDS = [
    ["1","DESIGN/15.12/MMXXV/001","15 DES 25","FOG LAMP","PT WANG SMU","P5401-0KA0B","JIG PLATING","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\FOG LAMP NEW\\\\DRAWING SHEET JIG FOG LAMP","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\FOG LAMP NEW\\\\JIG FOG LAMP ASSEMBLY","7","Selesai"],
    ["2","DESIGN/18.12/MMXXV/002","18 DES 25","PLUG OIL LEVEL","PT YIMM","BYW-E5362-00","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLUG OIL LEVEL BYW\\\\DRAWING PLUG OIL LEVEL BYW","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLUG OIL LEVEL BYW\\\\ASSEMBLY BUCKET PLUG OIL LEVEL BYW","6","Selesai"],
    ["3","DESIGN/23.12/MMXXV/003","23 DES 25","PLATE COVER","PT SKI","D35-F479R-10","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLATE COVER NEW\\\\DRAWING PACKING PLATE COVER OPSI 1 (APPROVE)","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLATE COVER NEW\\\\assembly bucket plate cover (approve)","6,5","Selesai"],
    ["4","DESIGN/24.12/MMXXV/004","24 DES 25","PLATE COVER","PT SKI","D35-F479R-10","JIG PLATING","√","√","√","√","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\PLATE COVER\\\\DRAWING JIG PLATE COVER OPSI 1","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\PLATE COVER\\\\JIG ASSEMBLY OPSI 1","6","Belum Selesai"],
    ["5","DESIGN/05.01/MMXXVI/005","5 JAN 26","CAP","PT YIMM","2-DJ-F8349-00","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET CAP\\\\DRAWING CAP OPSI 2 (approve)","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET CAP\\\\BUCKET CAP OPSI 2 (APPROVE)","4,5","Selesai"],
    ["6","DESIGN/06.01/MMXXVI/006","6 JAN 26","CAP","PT YIMM","2-DJ-F8349-00","JIG PLATING (BLACK CHROME & SILVER CHROME)","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\CAP\\\\DRAWING JIG CAP","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\CAP\\\\JIG ASSEMBLY CAP","5","Selesai"],
    ["7","DESIGN/07.01/MMXXVI/007","07 JAN 26","COVER METER","PT YIMM","2DJ-H3559-00-0","JIG PLATING","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\COVER METER\\\\DRAWING JIG COVER METER","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\COVER METER\\\\JIG COVER METER ASSEMBLY","6","Selesai"],
    ["8","DESIGN/15.01/MMXXVI/008","15 JAN 26","PROTECTOR MUFFLER","PT SMAP INDONESIA","2BD-E4728-10-0-002","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PROTECTION MUFFLER\\\\DRAWING BUCKET PROTECTION MUFFLER","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PROTECTION MUFFLER\\\\ASSEMBLY","4","Selesai"],
    ["9","DESIGN/19.01/MMXXVI/009","19 JAN 26","PROTECTOR MUFFLER","PT SMAP INDONESIA","2BD-E4728-10-0-002","JIG PLATING","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\PROTECTOR MUFFLER\\\\DRAWING JIG PROTECTOR MUFFLER","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\PROTECTOR MUFFLER\\\\JIG PROTECTOR MUFFLER ASSEMBLY","7","Belum Selesai"],
    ["10","DESIGN/21.01/MMXXVI/010","21 JAN 26","PLUG OIL DB31","PT YIMM","DB2-E5362-00","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLUG OIL DB31\\\\DRAWING 2D PLUG OIL DB31","D:\\\\8. Shaila\\\\Drawing\\\\Bucket\\\\BUCKET PLUG OIL DB31\\\\ASSEMBLY BUCKET PLUG OIL DB31","6","Selesai"],
    ["11","DESIGN/22.01/MMXXVI/011","22 JAN 26","COVER HANDLE FRONT","PT YIMM","5DJ-F6231-P0-2","JIG PLATING","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\COVER HANDLE FRONT\\\\DRAWING JIG COVER HANDLE FRONT","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\COVER HANDLE FRONT\\\\JIG COVER HANDLE FRONT ASSEMBLY","7","Selesai"],
    ["12","DESIGN/02.02/MMXXVI/012","02 FEB 26","CAP","PT YIMM","2-DJ-F8349-00","JIG PLATING BLACK SATIN","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) CAP\\\\DRAWING JIG CAP SATIN","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) CAP\\\\JIG CAP SATIN ASSEMBLY","7","Selesai"],
    ["13","DESIGN/02.02/MMXXVI/013","02 FEB 26","COVER METER","PT YIMM","2DJ-H3559-00-0","JIG PLATING BLACK SATIN","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) COVER METER\\\\DRAWING JIG COVER METER","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) COVER METER\\\\JIG COVER METER ASSEMBLY","7","Belum Selesai"],
    ["14","DESIGN/04.02/MMXXVI/014","04 FEB 26","COVER HANDLE FRONT","PT YIMM","5DJ-F6231-P0-2","JIG PLATING BLACK SATIN","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) COVER HANDLE FRONT\\\\DRAWING JIG COVER HANDLE FRONT","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\(SATIN) COVER HANDLE FRONT\\\\JIG COVER HANDLE FRONT ASSEMBLY 1","7","Selesai"],
    ["15","DESIGN/04.02/MMXXVI/015","04 FEB 26","EMBLEM FILANO DB3 KECIL","PT YIMM","DB3-F839B-00","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\PACKING\\\\2D PACKING FILANO KECIL","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\PACKING\\\\ASSEMBLY PACKING EMBLEM FILANO KECIL DB3","5","Selesai"],
    ["16","DESIGN/06.02/MMXXVI/016","06 FEB 26","EMBLEM FILANO DB3 BESAR","PT YIMM","DB3-F174B-00","BUCKET","√","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\PACKING\\\\2D PACKING FILANO BESAR","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\PACKING\\\\ASSEMBLY PACKING EMBLEM FILANO BESAR 2","5","Selesai"],
    ["17","DESIGN/09.02/MMXXVI/017","09 FEB 26","WASHER PLAIN","PT YIMM","90202-05X07","JIG HITUNG  PART WASHER PLAIN","√","√","√","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\JIG PENGHITUNG WASHER\\\\2D JIG PENGHITUNG WASHER","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\JIG PENGHITUNG WASHER\\\\ASSEMBLY JIG PENGHITUNG WASHER","7,5","Selesai"],
    ["18","DESIGN/12.02/MMXXVI/018","12 FEB 26","COVER HANDLE FRONT","PT YIMM","5DJ-F6231-P0-2","TOGGLE CLAMP PUSH PULL","-","√","√","","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\JIG COOLING COVER HANDLE FRONT\\\\jig push pull\\\\assembly toggle push pull","20","Belum Selesai"],
    ["19","DESIGN/12.02/MMXXVI/019","12 FEB 26","COVER HANDLE FRONT","PT YIMM","5DJ-F6231-P0-2","TOGGLE CLAMP VERTIKAL","-","√","√","","","","D:\\\\8. Shaila\\\\Drawing\\\\Jig\\\\JIG COOLING COVER HANDLE FRONT\\\\jig vertikal\\\\assembly toggle vertikal","20","Belum Selesai"],
    ["20","DESIGN/16.02/MMXXVI/020","16 FEB 26","PROTECTOR MUFFLER","PT SMAP INDONESIA","2BD-E4728-10-0-002","BUCKET KUNING REGULER HORIZONTAL","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\PACKING\\\\BUCKET PROTECTION MUFFLER\\\\BUCKET KUNING BESAR\\\\2D PACKING PROTECTOR MUFFLER HORIZONTAL","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\PACKING\\\\BUCKET PROTECTION MUFFLER\\\\BUCKET KUNING BESAR\\\\PACKING PROTECTOR MUFFLER HORIZONTAL ASSEMBLY (BUCKET KUNING)","6","Selesai"],
    ["21","DESIGN/16.02/MMXXVI/021","16 FEB 26","PROTECTOR MUFFLER","PT SMAP INDONESIA","2BD-E4728-10-0-002","BUCKET KUNING REGULER VERTIKAL","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\PACKING\\\\BUCKET PROTECTION MUFFLER\\\\BUCKET KUNING BESAR\\\\2D PACKING PROTECTOR MUFFLER VERTIKAL","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\PACKING\\\\BUCKET PROTECTION MUFFLER\\\\BUCKET KUNING BESAR\\\\PACKING PROTECTOR MUFFLER VERTIKAL ASSEMBLY (BUCKET KUNING)","6","Selesai"],
    ["22","DESIGN/16.02/MMXXVI/022","16 FEB 26","COVER METER","PT YIMM","2DJ-H3559-00-0","BUCKET OPSI 1","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\COVER METER\\\\DRAWING\\\\INTERNAL\\\\BUCKET\\\\BUCKET COVER METER\\\\2D BUCKET COVER METER","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\COVER METER\\\\DRAWING\\\\INTERNAL\\\\BUCKET\\\\BUCKET COVER METER\\\\BUCKET COVER METER OPSI 5","7","Selesai"],
    ["23","DESIGN/19.02/MMXXVI/023","19 FEB 26","EMBLEM FILANO DB3 KECIL","PT YIMM","DB3-F839B-00","JIG INSPECTION","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\2D JIG INSPECTION EMBLEM FILANO KECIL","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\jig inspection filano kecil","7","Belum Selesai"],
    ["24","DESIGN/19.02/MMXXVI/024","19 FEB 26","EMBLEM FILANO DB3 BESAR","PT YIMM","DB3-F174B-00","JIG INSPECTION","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\2D JIG INSPECTION EMBLEM FILANO BESAR","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\FILANO DB31\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\jig inspection filano besar 2","7","Belum Selesai"],
    ["25","DESIGN/23.02/MMXXVI/025","23 FEB 26","PROTECTOR MUFFLER","PT SMAP INDONESIA","2BD-E4728-10-0-002","JIG INSPECTION","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\PROTECTOR MUFFLER\\\\2D JIG INSPECTION PROTECTOR MUFFLER","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT SMAP INDONESIA\\\\Protector muffler\\\\DRAWING\\\\INTERNAL\\\\JIG\\\\PROTECTOR MUFFLER\\\\JIG INSPECTION\\\\ASSEMBLY BASE JIG PROTECTOR MUFFLER","8","Selesai"],
    ["26","DESIGN/25.02/MMXXVI/026","25 FEB 26","COVER METER","PT YIMM","2DJ-H3559-00-0","BUCKET OPSI 2","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\COVER METER\\\\DRAWING\\\\INTERNAL\\\\BUCKET\\\\BUCKET COVER METER\\\\2D BUCKET COVER METER + EVAFOAM","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\COVER METER\\\\DRAWING\\\\INTERNAL\\\\BUCKET\\\\BUCKET COVER METER\\\\BUCKET COVER METER+EVA FOAM","7","Belum Selesai"],
    ["27","DESIGN/27.02/MMXXVI/027","27 FEB 26","HOLDER BRAKE HOSE","PT YIMM","DC1-F587A-00","JIG INSPECTION","-","√","√","","","D:\\\\8. Shaila\\\\CUSTOMER\\\\PT YAMAHA\\\\HOLDER BRAKE HOSE 4\\\\DRAWING\\\\INTERNAL\\\\2D JIG INSPECTION HOLDER BRAKE HOSE","","8","Belum Selesai"],
]

# VBA Module code (standard module)
VBA_MODULE_CODE = r'''
Sub RefreshSheets()
    Dim wsMaster As Worksheet
    Dim lastRow As Long
    Dim customers As Object
    Dim processes As Object
    Dim cell As Range
    Dim sheetName As String
    Dim ws As Worksheet
    Dim filterFormula As String
    Dim headerNames As Variant
    Dim i As Long
    Dim newSheetsCreated As Long
    
    Application.ScreenUpdating = False
    Application.DisplayAlerts = False
    Application.EnableEvents = False
    
    Set wsMaster = ThisWorkbook.Sheets("MASTER DATA")
    lastRow = wsMaster.Cells(wsMaster.Rows.Count, "A").End(xlUp).Row
    
    If lastRow < 4 Then
        Application.EnableEvents = True
        Application.ScreenUpdating = True
        Application.DisplayAlerts = True
        Exit Sub
    End If
    
    headerNames = Array("No", "No Dokumen", "Tanggal", "Nama Part", "Customer", _
        "No Part", "Proses", "2D", "3D", "Rev 0", "Rev 1", "Rev 2", _
        "Path 2D", "Path 3D", "Durasi (Jam)", "Status")
    
    Set customers = CreateObject("Scripting.Dictionary")
    For Each cell In wsMaster.Range("E4:E" & lastRow)
        If Len(Trim(cell.Value)) > 0 Then
            If Not customers.Exists(Trim(cell.Value)) Then
                customers.Add Trim(cell.Value), 1
            End If
        End If
    Next cell
    
    Set processes = CreateObject("Scripting.Dictionary")
    For Each cell In wsMaster.Range("G4:G" & lastRow)
        If Len(Trim(cell.Value)) > 0 Then
            If Not processes.Exists(Trim(cell.Value)) Then
                processes.Add Trim(cell.Value), 1
            End If
        End If
    Next cell
    
    newSheetsCreated = 0
    
    Dim custName As Variant
    For Each custName In customers.Keys
        sheetName = "By " & CStr(custName)
        If Len(sheetName) > 31 Then sheetName = Left(sheetName, 31)
        
        Dim sheetExists As Boolean
        sheetExists = False
        For Each ws In ThisWorkbook.Sheets
            If ws.Name = sheetName Then
                sheetExists = True
                Exit For
            End If
        Next ws
        
        If Not sheetExists Then
            Set ws = ThisWorkbook.Sheets.Add(After:=ThisWorkbook.Sheets(ThisWorkbook.Sheets.Count))
            ws.Name = sheetName
            newSheetsCreated = newSheetsCreated + 1
            
            ws.Range("A1:P1").Merge
            ws.Range("A1").Value = "DRAWING NPD - " & CStr(custName)
            With ws.Range("A1")
                .Font.Bold = True: .Font.Color = RGB(255, 255, 255)
                .Font.Size = 14: .Font.Name = "Calibri"
                .Interior.Color = RGB(31, 78, 121)
                .HorizontalAlignment = xlCenter: .VerticalAlignment = xlCenter
            End With
            
            ws.Range("A2:P2").Merge
            ws.Range("A2").Value = "AUTO-UPDATE: Data otomatis update dari MASTER DATA"
            With ws.Range("A2")
                .Font.Italic = True: .Font.Color = RGB(243, 156, 18)
                .Font.Size = 10: .Font.Bold = True: .Font.Name = "Calibri"
                .Interior.Color = RGB(255, 243, 224): .HorizontalAlignment = xlCenter
            End With
            
            For i = 0 To UBound(headerNames)
                ws.Cells(3, i + 1).Value = headerNames(i)
                With ws.Cells(3, i + 1)
                    .Font.Bold = True: .Font.Color = RGB(255, 255, 255)
                    .Font.Size = 11: .Font.Name = "Calibri"
                    .Interior.Color = RGB(31, 78, 121)
                    .HorizontalAlignment = xlCenter: .VerticalAlignment = xlCenter
                    .Borders.LineStyle = xlContinuous
                End With
            Next i
            
            Dim colWidths As Variant
            colWidths = Array(5, 30, 14, 25, 22, 22, 32, 6, 6, 7, 7, 7, 55, 55, 14, 16)
            For i = 0 To UBound(colWidths)
                ws.Columns(i + 1).ColumnWidth = colWidths(i)
            Next i
        Else
            Set ws = ThisWorkbook.Sheets(sheetName)
        End If
        
        filterFormula = "=IFERROR(FILTER('MASTER DATA'!A4:P" & lastRow & ",'MASTER DATA'!E4:E" & lastRow & "=""" & CStr(custName) & """),""Tidak ada data"")"
        ws.Range("A4").ClearContents
        ws.Range("A4").Formula2 = filterFormula
    Next custName
    
    ' Update By Proses
    Dim wsProses As Worksheet
    Dim prosesExists As Boolean
    prosesExists = False
    For Each ws In ThisWorkbook.Sheets
        If ws.Name = "By Proses" Then
            prosesExists = True
            Set wsProses = ws
            Exit For
        End If
    Next ws
    
    If prosesExists Then
        Dim clearLastRow As Long
        clearLastRow = wsProses.Cells(wsProses.Rows.Count, "A").End(xlUp).Row
        If clearLastRow > 2 Then
            wsProses.Range("A3:P" & clearLastRow + 50).ClearContents
            wsProses.Range("A3:P" & clearLastRow + 50).ClearFormats
            On Error Resume Next
            wsProses.Range("A3:P" & clearLastRow + 50).UnMerge
            On Error GoTo 0
        End If
        
        Dim currentRow As Long
        currentRow = 3
        Dim procName As Variant
        For Each procName In processes.Keys
            wsProses.Range("A" & currentRow & ":P" & currentRow).Merge
            wsProses.Cells(currentRow, 1).Value = "  " & CStr(procName)
            With wsProses.Cells(currentRow, 1)
                .Font.Bold = True: .Font.Color = RGB(255, 255, 255)
                .Font.Size = 11: .Font.Name = "Calibri"
                .Interior.Color = RGB(46, 117, 182)
                .HorizontalAlignment = xlLeft: .VerticalAlignment = xlCenter
            End With
            currentRow = currentRow + 1
            
            For i = 0 To UBound(headerNames)
                wsProses.Cells(currentRow, i + 1).Value = headerNames(i)
                With wsProses.Cells(currentRow, i + 1)
                    .Font.Bold = True: .Font.Color = RGB(255, 255, 255)
                    .Font.Size = 11: .Font.Name = "Calibri"
                    .Interior.Color = RGB(31, 78, 121)
                    .HorizontalAlignment = xlCenter: .VerticalAlignment = xlCenter
                    .Borders.LineStyle = xlContinuous
                End With
            Next i
            currentRow = currentRow + 1
            
            filterFormula = "=IFERROR(FILTER('MASTER DATA'!A4:P" & lastRow & ",'MASTER DATA'!G4:G" & lastRow & "=""" & CStr(procName) & """),""Tidak ada data"")"
            wsProses.Cells(currentRow, 1).Formula2 = filterFormula
            currentRow = currentRow + 20
        Next procName
    End If
    
    wsMaster.Activate
    
    Application.EnableEvents = True
    Application.ScreenUpdating = True
    Application.DisplayAlerts = True
End Sub
'''

# VBA code for the MASTER DATA sheet (auto-trigger on change)
VBA_SHEET_CODE = r'''
Private Sub Worksheet_Change(ByVal Target As Range)
    ' Only trigger if changes are in data area (row 4+, columns A-P)
    If Target.Row < 4 Then Exit Sub
    If Target.Column > 16 Then Exit Sub
    
    ' Debounce: only run if the change involves key columns (Customer=E or Proses=G or Status=P)
    Dim needsRefresh As Boolean
    needsRefresh = False
    
    Dim cell As Range
    For Each cell In Target
        If cell.Column = 5 Or cell.Column = 7 Or cell.Column = 16 Then
            needsRefresh = True
            Exit For
        End If
    Next cell
    
    ' Also trigger if a new row is being filled (column A has a value)
    If Not needsRefresh Then
        For Each cell In Target
            If cell.Column >= 1 And cell.Column <= 16 Then
                ' Check if this row has data in column A (new row)
                If Len(Trim(Me.Cells(cell.Row, 1).Value)) > 0 And _
                   Len(Trim(Me.Cells(cell.Row, 5).Value)) > 0 Then
                    needsRefresh = True
                    Exit For
                End If
            End If
        Next cell
    End If
    
    If needsRefresh Then
        Call RefreshSheets
    End If
End Sub
'''


# ============================================================
# GET DATA (embedded)
# ============================================================
def get_records():
    return EMBEDDED_RECORDS


# ============================================================
# CREATE BASE XLSX
# ============================================================
def create_base_xlsx(records):
    customers = sorted(set(r[4] for r in records))
    processes = sorted(set(r[6] for r in records))

    wb = xlsxwriter.Workbook(TEMP_XLSX)

    # Formats
    fmt_title = wb.add_format({'bold': True, 'font_color': 'white', 'bg_color': '#1F4E79', 'font_size': 14, 'align': 'center', 'valign': 'vcenter', 'font_name': 'Calibri', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_subtitle = wb.add_format({'italic': True, 'font_color': '#F39C12', 'bg_color': '#FFF3E0', 'font_size': 10, 'align': 'center', 'valign': 'vcenter', 'font_name': 'Calibri', 'bold': True, 'border': 1, 'border_color': '#BDC3C7'})
    fmt_header = wb.add_format({'bold': True, 'font_color': 'white', 'bg_color': '#1F4E79', 'font_size': 11, 'align': 'center', 'valign': 'vcenter', 'text_wrap': True, 'font_name': 'Calibri', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_data = wb.add_format({'font_color': '#333333', 'font_size': 10, 'font_name': 'Calibri', 'valign': 'vcenter', 'text_wrap': True, 'border': 1, 'border_color': '#BDC3C7'})
    fmt_dc = wb.add_format({'font_color': '#333333', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'text_wrap': True, 'border': 1, 'border_color': '#BDC3C7'})
    fmt_alt = wb.add_format({'font_color': '#333333', 'font_size': 10, 'font_name': 'Calibri', 'bg_color': '#D6E4F0', 'valign': 'vcenter', 'text_wrap': True, 'border': 1, 'border_color': '#BDC3C7'})
    fmt_altc = wb.add_format({'font_color': '#333333', 'font_size': 10, 'font_name': 'Calibri', 'bg_color': '#D6E4F0', 'align': 'center', 'valign': 'vcenter', 'text_wrap': True, 'border': 1, 'border_color': '#BDC3C7'})
    fmt_green = wb.add_format({'font_color': '#27AE60', 'font_size': 10, 'font_name': 'Calibri', 'bold': True, 'bg_color': '#E8F5E9', 'align': 'center', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_red = wb.add_format({'font_color': '#E74C3C', 'font_size': 10, 'font_name': 'Calibri', 'bold': True, 'bg_color': '#FFEBEE', 'align': 'center', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_filter = wb.add_format({'font_color': '#333333', 'font_size': 10, 'font_name': 'Calibri', 'valign': 'vcenter'})
    fmt_cl = wb.add_format({'font_color': '#666666', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#F8F9FA', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_cn = wb.add_format({'bold': True, 'font_color': '#1F4E79', 'font_size': 20, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#F8F9FA', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_gl = wb.add_format({'font_color': '#666666', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#E8F5E9', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_gn = wb.add_format({'bold': True, 'font_color': '#27AE60', 'font_size': 20, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#E8F5E9', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_rl = wb.add_format({'font_color': '#666666', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#FFEBEE', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_rn = wb.add_format({'bold': True, 'font_color': '#E74C3C', 'font_size': 20, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#FFEBEE', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_ol = wb.add_format({'font_color': '#666666', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#FFF3E0', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_on = wb.add_format({'bold': True, 'font_color': '#F39C12', 'font_size': 20, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'bg_color': '#FFF3E0', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_sec = wb.add_format({'bold': True, 'font_color': 'white', 'bg_color': '#1F4E79', 'font_size': 11, 'font_name': 'Calibri', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_sp = wb.add_format({'bold': True, 'font_color': 'white', 'bg_color': '#8E44AD', 'font_size': 11, 'font_name': 'Calibri', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_th = wb.add_format({'bold': True, 'font_color': '#1F4E79', 'bg_color': '#F2F2F2', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_thp = wb.add_format({'bold': True, 'font_color': '#8E44AD', 'bg_color': '#F3E5F5', 'font_size': 10, 'font_name': 'Calibri', 'align': 'center', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})
    fmt_pg = wb.add_format({'bold': True, 'font_color': 'white', 'bg_color': '#2E75B6', 'font_size': 11, 'font_name': 'Calibri', 'valign': 'vcenter', 'border': 1, 'border_color': '#BDC3C7'})

    cc = {0, 1, 7, 8, 9, 10, 11, 14}

    def wr(ws, row, data, ia=False):
        for c, v in enumerate(data):
            if c == 15:
                ws.write(row, c, v, fmt_green if str(v).lower() == "selesai" else (fmt_red if "belum" in str(v).lower() else (fmt_altc if ia else fmt_dc)))
            elif c in cc:
                ws.write(row, c, v, fmt_altc if ia else fmt_dc)
            else:
                ws.write(row, c, v, fmt_alt if ia else fmt_data)

    def sw(ws):
        for i, w in enumerate(COL_WIDTHS):
            ws.set_column(i, i, w)

    # MASTER DATA
    print("  MASTER DATA...")
    wm = wb.add_worksheet("MASTER DATA")
    wm.set_tab_color('#1F4E79')
    wm.merge_range('A1:P1', 'MASTER LIST DRAWING NPD DESIGN', fmt_title)
    wm.merge_range('A2:P2', 'Tambahkan data baru di bawah. Sheet lain OTOMATIS update saat kolom Customer/Proses/Status diisi.', fmt_subtitle)
    for c, h in enumerate(DISPLAY_HEADERS):
        wm.write(2, c, h, fmt_header)
    for idx, rec in enumerate(records):
        wr(wm, idx + 3, [idx + 1] + rec[1:], ia=(idx % 2 == 1))
    sw(wm)
    wm.freeze_panes(3, 0)
    wm.autofilter(2, 0, 2 + len(records), 15)
    wm.data_validation(3, 15, 1000, 15, {'validate': 'list', 'source': ['Selesai', 'Belum Selesai']})

    # DASHBOARD
    print("  DASHBOARD...")
    wd = wb.add_worksheet("DASHBOARD")
    wd.set_tab_color('#27AE60')
    wd.merge_range('A1:H1', 'DASHBOARD - MASTER LIST DRAWING NPD', fmt_title)
    wd.set_row(0, 40)
    wd.merge_range('A3:B3', 'TOTAL DRAWING', fmt_cl)
    wd.merge_range('A4:B4', '', fmt_cn)
    wd.write_formula('A4', "=COUNTA('MASTER DATA'!A4:A1000)", fmt_cn)
    wd.merge_range('C3:D3', 'SELESAI', fmt_gl)
    wd.merge_range('C4:D4', '', fmt_gn)
    wd.write_formula('C4', '=COUNTIF(\'MASTER DATA\'!P4:P1000,"Selesai")', fmt_gn)
    wd.merge_range('E3:F3', 'BELUM SELESAI', fmt_rl)
    wd.merge_range('E4:F4', '', fmt_rn)
    wd.write_formula('E4', '=COUNTIF(\'MASTER DATA\'!P4:P1000,"Belum Selesai")', fmt_rn)
    wd.merge_range('G3:H3', 'RATA-RATA DURASI', fmt_ol)
    wd.merge_range('G4:H4', '', fmt_on)
    durs = [float(r[14].replace(",", ".")) for r in records if r[14].replace(",", ".").replace(".", "").isdigit() or r[14].replace(",", ".").replace(".", "", 1).isdigit()]
    avg = sum(durs) / len(durs) if durs else 0
    wd.write('G4', f"{avg:.1f} Jam", fmt_on)
    wd.set_row(3, 45)

    wd.merge_range('A6:E6', 'RINGKASAN PER CUSTOMER', fmt_sec)
    for i, h in enumerate(["Customer", "Total", "Selesai", "Belum", "% Selesai"]):
        wd.write(6, i, h, fmt_th)
    for idx, c in enumerate(customers):
        r = 7 + idx
        t = len([x for x in records if x[4] == c])
        d = len([x for x in records if x[4] == c and x[15].lower() == "selesai"])
        wd.write(r, 0, c, fmt_data); wd.write(r, 1, t, fmt_dc)
        wd.write(r, 2, d, fmt_green); wd.write(r, 3, t - d, fmt_red)
        wd.write(r, 4, f"{d/t*100:.0f}%" if t else "0%", fmt_dc)

    ps = 7 + len(customers) + 2
    wd.merge_range(ps, 0, ps, 4, 'RINGKASAN PER PROSES', fmt_sp)
    for i, h in enumerate(["Proses", "Total", "Selesai", "Belum", "% Selesai"]):
        wd.write(ps + 1, i, h, fmt_thp)
    for idx, p in enumerate(processes):
        r = ps + 2 + idx
        t = len([x for x in records if x[6] == p])
        d = len([x for x in records if x[6] == p and x[15].lower() == "selesai"])
        wd.write(r, 0, p, fmt_data); wd.write(r, 1, t, fmt_dc)
        wd.write(r, 2, d, fmt_green); wd.write(r, 3, t - d, fmt_red)
        wd.write(r, 4, f"{d/t*100:.0f}%" if t else "0%", fmt_dc)

    for w, c in [(30,0),(12,1),(12,2),(12,3),(12,4),(15,5),(18,6),(15,7)]:
        wd.set_column(c, c, w)
    wd.freeze_panes(1, 0)

    # BY PT SHEETS
    pt_col = {"PT YIMM": "#2E75B6", "PT SMAP INDONESIA": "#E74C3C", "PT SKI": "#27AE60", "PT WANG SMU": "#F39C12"}
    for pt in customers:
        sn = f"By {pt}"[:31]
        print(f"  {sn}...")
        wp = wb.add_worksheet(sn)
        wp.set_tab_color(pt_col.get(pt, '#95A5A6'))
        wp.merge_range('A1:P1', f'DRAWING NPD - {pt}', fmt_title)
        wp.merge_range('A2:P2', 'AUTO-UPDATE: Data otomatis update dari MASTER DATA', fmt_subtitle)
        for c, h in enumerate(DISPLAY_HEADERS):
            wp.write(2, c, h, fmt_header)
        wp.write_dynamic_array_formula('A4:P4',
            f'=IFERROR(_xlfn._xlws.FILTER(\'MASTER DATA\'!A4:P1000,\'MASTER DATA\'!E4:E1000="{pt}"),"Tidak ada data")',
            fmt_filter)
        sw(wp)
        wp.freeze_panes(3, 0)

    # BY PROSES
    print("  By Proses...")
    wpr = wb.add_worksheet("By Proses")
    wpr.set_tab_color('#8E44AD')
    wpr.merge_range('A1:P1', 'DRAWING NPD - KATEGORI PER PROSES', wb.add_format({
        'bold': True, 'font_color': 'white', 'bg_color': '#8E44AD', 'font_size': 14,
        'align': 'center', 'valign': 'vcenter', 'font_name': 'Calibri', 'border': 1}))
    wpr.merge_range('A2:P2', 'AUTO-UPDATE: Data otomatis update dari MASTER DATA', fmt_subtitle)
    cr = 2
    for proc in processes:
        wpr.merge_range(cr, 0, cr, 15, f'  {proc}', fmt_pg)
        cr += 1
        for c, h in enumerate(DISPLAY_HEADERS):
            wpr.write(cr, c, h, fmt_header)
        cr += 1
        wpr.write_dynamic_array_formula(cr, 0, cr, 15,
            f'=IFERROR(_xlfn._xlws.FILTER(\'MASTER DATA\'!A4:P1000,\'MASTER DATA\'!G4:G1000="{proc}"),"Tidak ada data")',
            fmt_filter)
        cr += 15
    sw(wpr)
    wpr.freeze_panes(2, 0)

    wb.close()
    return customers, processes


# ============================================================
# ADD VBA AND SAVE AS .XLSM
# ============================================================
def add_vba_and_save():
    abs_temp = os.path.abspath(TEMP_XLSX)
    abs_output = os.path.abspath(OUTPUT_FILE)

    if os.path.exists(abs_output):
        try: os.remove(abs_output)
        except: pass

    print("\nMembuka Excel untuk menambahkan VBA...")
    excel = win32com.client.Dispatch("Excel.Application")
    excel.Visible = False
    excel.DisplayAlerts = False

    try:
        wb = excel.Workbooks.Open(abs_temp)
        time.sleep(1)

        # Add standard module with RefreshSheets sub
        print("  Menambahkan macro RefreshSheets...")
        vb_mod = wb.VBProject.VBComponents.Add(1)
        vb_mod.Name = "ModRefresh"
        vb_mod.CodeModule.AddFromString(VBA_MODULE_CODE)

        # Add Worksheet_Change to MASTER DATA sheet
        print("  Menambahkan auto-trigger di MASTER DATA...")
        # Get the CodeName of the MASTER DATA sheet, then inject into its code module
        master_sheet = wb.Sheets("MASTER DATA")
        code_name = master_sheet.CodeName
        print(f"  -> MASTER DATA CodeName: {code_name}")
        vb_comp = wb.VBProject.VBComponents(code_name)
        vb_comp.CodeModule.AddFromString(VBA_SHEET_CODE)
        print(f"  -> Worksheet_Change event berhasil ditambahkan!")

        # Save as .xlsm
        print(f"  Menyimpan: {abs_output}")
        wb.SaveAs(abs_output, FileFormat=52)
        wb.Close()

    except Exception as e:
        print(f"\nError: {e}")
        print("\nJika error terkait VBA Trust, buka Excel:")
        print("  File -> Options -> Trust Center -> Trust Center Settings")
        print("  -> Macro Settings -> centang 'Trust access to VBA project object model'")
        try:
            wb.Close(False)
        except:
            pass
        raise
    finally:
        excel.Quit()
        try: os.remove(abs_temp)
        except: pass


def main():
    print("=" * 50)
    print("MASTER LIST DRAWING NPD - REDESIGNED")
    print("=" * 50)

    print("\n[1/3] Membaca data...")
    records = get_records()
    print(f"  {len(records)} record")

    print("\n[2/3] Membuat file Excel...")
    customers, processes = create_base_xlsx(records)

    print("\n[3/3] Menambahkan VBA macro + auto-trigger...")
    add_vba_and_save()

    print("\n" + "=" * 50)
    print("SELESAI!")
    print("=" * 50)
    print(f"\nFile: {OUTPUT_FILE}")
    print(f"\nFitur Auto-Update:")
    print(f"  - Edit kolom Customer/Proses/Status di MASTER DATA")
    print(f"    -> Sheet By PT dan By Proses OTOMATIS update")
    print(f"  - Tambah PT baru -> Sheet baru otomatis dibuat")
    print(f"  - Tambah Proses baru -> By Proses otomatis update")
    print(f"  - TANPA perlu Alt+F8!")


if __name__ == "__main__":
    main()
