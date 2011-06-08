# Movhandler extension for MediaWiki


## What can this extension do?

* Provides thumbnails for quicktime movies in galleries, media pages etc. (See [examples](http://www.dayel.com/2010/09/quicktime-movie-thumbnailer-mediawiki/))
* Also reports the size, framerate, encoder and bitrate of the quicktime file.
* It does not embed the movies themselves in the thumbnails (that would be too slow—especially for galleries), but if you click through the media page, the movie file will play in the browser.

## Usage

Once installed, extension will automatically make thumbnails for uploaded quicktime movie (.mov) files. You can also embed thumbnails for movie files as you would images, just specifying the .mov file instead. e.g.

    [[Image:mymovie.mov|200px]]

## Installation

### 1. First Install ffmpeg and imagemagick 
E.g. on ubuntu type:

    sudo apt-get install ffmpeg imagemagick

### 2. Install the Extension

Put the downloaded files into the extensions/movhandler/ directory off your mediawiki installation, then add the following to LocalSettings.php:

    require_once("$IP/extensions/movhandler/movhandler.php");

You may also need to add '.mov' to the allowed filetypes.
