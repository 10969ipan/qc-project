(function () {
    'use strict';

    function initFileInputs() {
        const fileInputs = document.querySelectorAll('.custom-file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function (e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    const nextSibling = e.target.nextElementSibling;
                    if (nextSibling) {
                        nextSibling.innerText = fileName;
                    }
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initFileInputs);
})();
