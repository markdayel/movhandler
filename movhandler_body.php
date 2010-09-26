<?php

// MediaHandler::getParamMap, MediaHandler::validateParam, MediaHandler::makeParamString
// MediaHandler::parseParamString, MediaHandler::normaliseParams, MediaHandler::getImageSize

class movhandler extends MediaHandler
{
	function canRender( $file ) { return true; }
	function mustRender( $file ) { return true; }
	
	function getThumbType( $ext, $mime ) {
		return array( 'png', 'image/png' );
	}
	
	function getMetadataType( $image ) {
		return 'mov';
	}
	

	 function getParamMap() {                       	
	         return array( 'img_width' => 'width' );	
	 }                                              

	 function validateParam( $name, $value ) 
	 {
		if ( in_array( $name, array( 'width', 'height' ) ) ) {
		        if ( $value <= 0 ) {
		                return false;
		        } else {
		                return true;
		        }
		} else {
		        return false;
		}
	}

	function makeParamString( $params ) 
	{
	     if ( isset( $params['physicalWidth'] ) ) {
                     $width = $params['physicalWidth'];
             } elseif ( isset( $params['width'] ) ) {
                     $width = $params['width'];
             } else {
                     throw new MWException( 'No width specified to '.__METHOD__ );
             }
			 $width = 300;
 			 $height = 300;
             # Removed for ProofreadPage
             #$width = intval( $width );
             return "{$width}px";
     }

	function parseParamString( $str ) {
	          $m = false;
            if ( preg_match( '/^(\d+)px$/', $str, $m ) ) {
                    return array( 'width' => $m[1] );
            } else {
                    return false;
            }
    }

	function normaliseParams( $image, &$params ) {
	        $mimeType = $image->getMimeType();

	        if ( !isset( $params['width'] ) ) {
	                return false;
	        }

	        if ( !isset( $params['page'] ) ) {
	                $params['page'] = 1;
	        } else  {
	                if ( $params['page'] > $image->pageCount() ) {
	                        $params['page'] = $image->pageCount();
	                }

	                if ( $params['page'] < 1 ) {
	                        $params['page'] = 1;
	                }
	        }

	        $srcWidth = $image->getWidth( $params['page'] );
	        $srcHeight = $image->getHeight( $params['page'] );
	        if ( isset( $params['height'] ) && $params['height'] != -1 ) {
	                if ( $params['width'] * $srcHeight > $params['height'] * $srcWidth ) {
	                        $params['width'] = wfFitBoxWidth( $srcWidth, $srcHeight, $params['height'] );
	                }
	        }
	        $params['height'] = File::scaleHeight( $srcWidth, $srcHeight, $params['width'] );
	        // if ( !$this->validateThumbParams( $params['width'], $params['height'], $srcWidth, $srcHeight, $mimeType ) ) {
	        //         return false;
	        // }
	        return true;
	}
	
	function getImageSize( $image, $path ) {
	    wfSuppressWarnings();
	    $gis = getimagesize( $path );
	    wfRestoreWarnings();
	    return $gis;
    }
	

	function doTransform( $image, $dstPath, $dstUrl, $params, $flags = 0 ) 
	{
		global $egffmpegPath, $egffmpegFallback, $egffmpegMinSize;
			
		if ( !$this->normaliseParams( $image, $params ) ) {
			return new TransformParameterError( $params );
		}
		
		$clientWidth = 300;//$params['width'];
		$clientHeight = 300;//$params['height'];
		//$srcWidth = $image->getWidth();
		//$srcHeight = $image->getHeight();
		$srcWidth = 800;
		$srcHeight = 600;
		$srcPath = $image->getPath();
		$retval = 0;
		

		//$outWidth=$params['physicalWidth'];
		//$outHeight=$params['physicalHeight'];
				
		$outWidth=300;
		$outHeight=300;
		
		// if (!is_null($egffmpegMinSize) && (($srcWidth * $srcHeight) < $egffmpegMinSize))
		// 	return parent::doTransform($image, $dstPath, $dstUrl, $params, $flags);

		if ( $outWidth == $srcWidth && $outWidth == $srcHeight ) {
			# normaliseParams (or the user) wants us to return the unscaled image
			wfDebug( __METHOD__.": returning unscaled image\n" );
			return new ThumbnailImage( $image, $image->getURL(), $clientWidth, $clientHeight, $srcPath );
		}


		wfMkdirParents( dirname( $dstPath ) );

		wfDebug( __METHOD__.": creating {$outWidth}x{$outHeight} thumbnail at $dstPath\n" );

		// // this command tries to resize directly in ffmpeg
		// $cmd = "{$egffmpegPath}ffmpeg ".
		// 	"-y -itsoffset 1 ".
		// 	"-i ". wfEscapeShellArg( $srcPath )." ".
		// 	"-s {$outWidth}x{$outHeight} ".
		// 	"-ss 1 -vframes 1 -vcodec png -an ".
		// 	wfEscapeShellArg( $dstPath );

		// $cmd1 = "{$egffmpegPath}ffmpeg ".
		// 	"-y -itsoffset 0 ".
		// 	"-i ". wfEscapeShellArg( $srcPath )." ".
		// 	"-ss 1 -vframes 1 -vcodec png -an ".
		// 	wfEscapeShellArg( $dstPath );

		$cmd1 = "{$egffmpegPath}ffmpeg ".
			"-y -itsoffset 0.1 ".
			"-i ". wfEscapeShellArg( $srcPath )." ".
			"-ss 1 -vframes 1 -vcodec png -an ".
			wfEscapeShellArg( $dstPath );

		$cmd2 = "/usr/bin/mogrify -quality 2 -resize {$outWidth}x{$outHeight} ". wfEscapeShellArg( $dstPath );


		//echo ": Running ffmpeg: $cmd\n";
		wfDebug( __METHOD__.": Running ffmpeg: $cmd\n" );
		wfProfileIn( 'convert' );
		$err = wfShellExec( $cmd1, $retval );
		if ( $retval == 0 )
		{
			$err = wfShellExec( $cmd2, $retval );
		}
		wfProfileOut( 'convert' );

		// if ($err !== 0 && $egffmpegFallback)
		// 	return parent::doTransform($image, $dstPath, $dstUrl, $params, $flags);

		$removed = $this->removeBadFile( $dstPath, $retval );

		if ( $retval != 0 || $removed ) {
			wfDebugLog( 'thumbnail',
				sprintf( 'thumbnail failed on %s: error %d "%s" from "%s"',
					wfHostname(), $retval, trim($err), $cmd ) );
			return new MediaTransformError( 'thumbnail_error', $clientWidth, $clientHeight, $err );
		} 
		else 
		{
			// get the width and height using php's getimagesize (is there a way to get these from the mogrify command?)
 			list($clientWidth, $clientHeight, $type, $attr) = getimagesize($dstPath);

			return new ThumbnailImage( $image, $dstUrl, $clientWidth, $clientHeight, $dstPath );
		}
	}
}
