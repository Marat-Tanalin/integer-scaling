<?php

header('Content-Type: text/plain; charset=utf-8');

$fileName = 'testcases.json';

function showError(string $message) {
	echo $message;
	exit;
}

function showFileError(string $message) {
	global $fileName;
	showError('`' . $fileName . '`' . ' ' . $message);
}

if (!file_exists($fileName)) {
	showFileError('file does not exist.');
}

if (!is_file($fileName)) {
	showFileError('is not a file.');
}

$code = trim(file_get_contents($fileName));

if (!strlen($code)) {
	showFileError('does not contain data.');
}

try {
	$data = json_decode($code, null, 3, JSON_THROW_ON_ERROR);

	if (!is_array($data)) {
		showError('Unexpected JSON data. An array is expected.');
	}

	echo count($data) . ' items.' . "\n\n";
	echo json_encode($data, JSON_PRETTY_PRINT);
}
catch (JsonException $e) {
	echo 'JSON is invalid' . ' [' . $e->getMessage() . '].';
}