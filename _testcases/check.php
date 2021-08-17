<?php

header('Content-Type: text/plain; charset=utf-8');

function showError(string $message) {
	echo '[ERROR] ' . $message . "\n\n";
}

function showErrorAndExit(string $message) {
	showError($message);
	exit;
}

function getFileNames() {
	return glob('*.json');
}

function checkFile(string $name) {
	if (!is_file($name)) {
		showError('`' . $name . '` is not a file.');
		return;
	}

	$code = trim(file_get_contents($name));

	if (!strlen($code)) {
		showErrorAndExit('`' . $name . '` is empty.');
	}

	try {
		$data = json_decode($code, null, 3, JSON_THROW_ON_ERROR);

		if (!is_array($data)) {
			showErrorAndExit('`' . $name . '` does not contain an array.');
		}

		echo '`' . $name . '`: ' . count($data) . ' items.' . "\n\n";
		echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
	}
	catch (JsonException $e) {
		showErrorAndExit('`' . $name . '`: JSON is invalid [' . $e->getMessage() . '].');
	}
}

echo "\n";

foreach (getFileNames() as $fileName) {
	checkFile($fileName);
}