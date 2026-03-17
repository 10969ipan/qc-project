const fs = require('fs');
const assert = require('assert');

const filesToTest = [
    { path: 'public/js/checksheet/fpa.js', type: 'class' },
    { path: 'public/js/checksheet/in-process.js', type: 'class' },
    { path: 'public/js/checksheet/sub-assy.js', type: 'class' },
    { path: 'public/js/checksheet/plating.js', type: 'class' },
    { path: 'public/js/checksheet/incoming-create.js', type: 'iife' }
];

let allPassed = true;

const normalizePartNumberTests = [
    { input: "123-ABC", expected: "123-ABC" },
    { input: "  123 - abc ", expected: "123-ABC" },
    { input: "123\tabc", expected: "123ABC" },
    { input: null, expected: "" },
    { input: undefined, expected: "" }
];

const sampleSizeTests = [
    { lot: 1, expected: 0 },
    { lot: 5, expected: 2 },
    { lot: 10, expected: 3 },
    { lot: 20, expected: 5 },
    { lot: 30, expected: 8 },
    { lot: 60, expected: 13 },
    { lot: 100, expected: 20 },
    { lot: 200, expected: 32 },
    { lot: 300, expected: 50 },
    { lot: 600, expected: 80 },
    { lot: 1500, expected: 125 },
    { lot: 4000, expected: 200 },
    { lot: 12000, expected: 315 },
    { lot: 40000, expected: 500 },
    { lot: 200000, expected: 800 },
    { lot: 600000, expected: 1250 },
];

const aqlLimitsTests = [
    { sample: 0, acc: 0, rej: 1 },
    { sample: 2, acc: 0, rej: 1 },
    { sample: 50, acc: 0, rej: 1 },
    { sample: 80, acc: 1, rej: 2 },
    { sample: 125, acc: 2, rej: 3 },
    { sample: 200, acc: 3, rej: 4 },
    { sample: 315, acc: 5, rej: 6 },
    { sample: 500, acc: 7, rej: 8 },
    { sample: 800, acc: 10, rej: 11 },
    { sample: 1250, acc: 14, rej: 15 },
];

console.log("Starting Automated Verification of AQL 0.65 & Normalization Logic...\n");

filesToTest.forEach(fileInfo => {
    console.log(`Verifying ${fileInfo.path}...`);
    try {
        const code = fs.readFileSync(fileInfo.path, 'utf8');
        
        let normalizeFn = null;
        let sampleSizeFn = null;
        let aqlLimitsFn = null;

        if (fileInfo.type === 'class') {
            const normMatch = code.match(/normalizePartNumber\s*\([^)]*\)\s*{([^{}]*)}/);
            if (normMatch) {
                normalizeFn = new Function('pn', normMatch[1] + (!normMatch[1].includes('return') ? '\nreturn "";' : ''));
            }

            const sampleMatch = code.match(/getSampleSize\s*\([^)]*\)\s*{([^{}]*)}/);
            if (sampleMatch) {
                sampleSizeFn = new Function('lot', sampleMatch[1].replace(/lotSize/g, 'lot'));
            }

            const limitMatch = code.match(/getAqlLimits\s*\([^)]*\)\s*{([^{}]*)}/);
            if (limitMatch) {
                aqlLimitsFn = new Function('sample', limitMatch[1].replace(/sampleSize/g, 'sample'));
            }
        } else if (fileInfo.type === 'iife') {
            const tableMatch = code.match(/const\s+AQL_TABLE\s*=\s*(\[[\s\S]*?\]);/m);
            if (tableMatch) {
                const AQL_TABLE = eval(tableMatch[1]);
                sampleSizeFn = (lot) => {
                    const matched = AQL_TABLE.find(row => lot >= row.lot_min && lot <= row.lot_max);
                    return matched ? matched.sample : 0;
                };
                aqlLimitsFn = (sample) => {
                    const matched = AQL_TABLE.find(row => row.sample === sample);
                    return matched ? { acc: matched.acc, rej: matched.rej } : { acc: 0, rej: 1 };
                };
            }
            const normMatch = code.match(/function\s+normalizePartNumber\s*\([^)]*\)\s*{([^{}]*)}/);
            if (normMatch) {
                normalizeFn = new Function('pn', normMatch[1]);
            }
        }

        if (normalizeFn) {
            let pass = true;
            normalizePartNumberTests.forEach(test => {
                const res = normalizeFn(test.input);
                if (res !== test.expected) {
                    console.error(`  [FAILED] normalizePartNumber('${test.input}') expected '${test.expected}', got '${res}'`);
                    pass = false;
                    allPassed = false;
                }
            });
            if (pass) console.log("  [PASS] normalizePartNumber");
        } else {
            console.log("  [INFO] normalizePartNumber not found in this file.");
        }

        if (sampleSizeFn) {
            let pass = true;
            sampleSizeTests.forEach(test => {
                const res = sampleSizeFn(test.lot);
                if (res !== test.expected) {
                    console.error(`  [FAILED] getSampleSize(${test.lot}) expected ${test.expected}, got ${res}`);
                    pass = false;
                    allPassed = false;
                }
            });
            if (pass) console.log("  [PASS] getSampleSize (AQL 0.65)");
        } else {
            console.warn("  [WARN] getSampleSize not found in this file.");
        }

        if (aqlLimitsFn) {
            let pass = true;
            aqlLimitsTests.forEach(test => {
                const res = aqlLimitsFn(test.sample);
                if (res.acc !== test.acc || res.rej !== test.rej) {
                    console.error(`  [FAILED] getAqlLimits(${test.sample}) expected acc:${test.acc}/rej:${test.rej}, got acc:${res.acc}/rej:${res.rej}`);
                    pass = false;
                    allPassed = false;
                }
            });
            if (pass) console.log("  [PASS] getAqlLimits (AQL 0.65)");
        } else {
            console.warn("  [WARN] getAqlLimits not found in this file.");
        }

        console.log("");
    } catch (e) {
        console.error(`  [ERROR] Failed processing ${fileInfo.path}: ${e.message}`);
        allPassed = false;
    }
});

if (allPassed) {
    console.log("=======================================");
    console.log("ALL TESTS PASSED! Verification Successful.");
    console.log("=======================================");
} else {
    console.log("=======================================");
    console.log("SOME TESTS FAILED. Please review the output above.");
    console.log("=======================================");
    process.exit(1);
}
