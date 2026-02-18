<?php
$file = 'c:\laragon\www\qc-project\resources\views\calibration\tools\index.blade.php';
$content = file_get_contents($file);

// Add Merk to modalTambahAlat
$tambahSearch = '/(<input type="text" name="name_alat" class="form-control form-control-sm" required>)\s+<\/div>/';
$tambahReplace = "$1\n                                </div>\n                                <div class=\"form-group mb-2\">\n                                    <label class=\"small font-weight-bold\">Merk</label>\n                                    <input type=\"text\" name=\"merk\" class=\"form-control form-control-sm\">\n                                </div>";
$content = preg_replace($tambahSearch, $tambahReplace, $content);

// Add Merk to modalEditAlat
$editSearch = '/(<input type="text" name="name_alat" id="edit_name_alat"\s+class="form-control form-control-sm" required>)\s+<\/div>/';
$editReplace = "$1\n                                </div>\n                                <div class=\"form-group mb-2\">\n                                    <label class=\"small font-weight-bold\">Merk</label>\n                                    <input type=\"text\" name=\"merk\" id=\"edit_merk\"\n                                        class=\"form-control form-control-sm\">\n                                </div>";
$content = preg_replace($editSearch, $editReplace, $content);

// Add JS population
$jsSearch = '/(\$\(\'#edit_name_alat\'\)\.val\(tool\.name_alat\);)/';
$jsReplace = "$1\n                    $('#edit_merk').val(tool.merk);";
$content = preg_replace($jsSearch, $jsReplace, $content);

if (file_put_contents($file, $content)) {
    echo "SUCCESS: File updated.\n";
} else {
    echo "ERROR: Failed to update file.\n";
}
