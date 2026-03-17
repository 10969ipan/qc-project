<?php
header('Content-Type: text/plain');
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Headers:\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
