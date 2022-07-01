<?php

namespace App\Traits;

trait LogTrait
{
    public function writeToLog($data, $title = '', $logFileName) {
		$log = "\n------------------------\n";
		$log .= date("Y.m.d G:i:s") . "\n";
		$log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
		$log .= print_r($data, 1);
		$log .= "\n------------------------\n";
		file_put_contents(storage_path() . "/$logFileName.log", $log, FILE_APPEND);
		return true;
	}
}
