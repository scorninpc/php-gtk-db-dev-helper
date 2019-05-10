<?php


$global_lang = include(APPLICATION_PATH . "/langs/" . $lang . ".php");

function _t($msg)
{
	global $global_lang;

	if(isset($global_lang[$msg])) {
		$msg = $global_lang[$msg];
	}

	return $msg;
}