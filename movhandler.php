<?php

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['movhandler'] = $dir . 'movhandler_body.php';
$wgExtensionMessagesFiles['movhandler'] = $dir . 'movhandler.i18n.php';

$wgExtensionCredits['media'][] = array(
	'path' => __FILE__,
	'name' => 'movhandler',
	'author' => 'Mark J Dayel', 
	'url' => 'http://projects.dayel.com/projects/movhandler', 
	'description' => 'show thumbnails for quicktime mov files',
	'descriptionmsg' => 'movhandler-desc',
);

/*
 *  Requires ffmpeg and imagemagick to be installed
 */

$wgMediaHandlers['video/quicktime'] = 'movhandler';
$wgMediaHandlers['video/mp4'] = 'movhandler';
$wgMediaHandlers['video/x-ms-asf'] = 'movhandler';
$wgMediaHandlers['video/x-msvideo'] = 'movhandler';
$wgMediaHandlers['video/x-flv'] = 'movhandler';
$wgMediaHandlers['video/mpeg'] = 'movhandler';
$wgMediaHandlers['video/x-ms-wmv'] = 'movhandler';
$wgMediaHandlers['application/quicktime'] = 'movhandler';
