<?php

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['movhandler'] = $dir . 'movhandler_body.php';
$wgExtensionMessagesFiles['movhandler'] = $dir . 'movhandler.i18n.php';

$wgExtensionCredits['media'][] = array(
	'path' => __FILE__,
	'name' => 'movhandler',
	'author' => 'Mark J Dayel', 
	'url' => 'http://www.mediawiki.org/wiki/Extension:movhandler', 
	'description' => 'show thumbnails for quicktime mov files',
	'descriptionmsg' => 'movhandler-desc',
);

/*
 * Path to the ffmpeg executable. Download the source from 
 * <http://svn.wikimedia.org/svnroot/mediawiki/trunk/ffmpeg> or binaries from
 * <http://toolserver.org/~bryan/ffmpeg/>
 */
$egffmpegPath = '';
/*
 * If true tries to resize using the default media handler.
 * Handy as ffmpeg not support upscaling or palette images
 */
$egffmpegFallback = true;
/*
 * Minimum size in pixels for an image to be handled using PNGHandler. 
 * Smaller files will be handled using the default media handler.
 */
$egffmpegMinSize = 2000000;

$wgMediaHandlers['video/quicktime'] = 'movhandler';
$wgMediaHandlers['video/mp4'] = 'movhandler';
$wgMediaHandlers['video/x-ms-asf'] = 'movhandler';
$wgMediaHandlers['video/x-msvideo'] = 'movhandler';
$wgMediaHandlers['video/x-flv'] = 'movhandler';
$wgMediaHandlers['video/mpeg'] = 'movhandler';
$wgMediaHandlers['video/x-ms-wmv'] = 'movhandler';
$wgMediaHandlers['application/quicktime'] = 'movhandler';
