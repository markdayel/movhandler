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
		if ($params['width'] == 0) {
			$params['width'] = 500;
		}
		
	     if ( isset( $params['physicalWidth'] ) ) 
			{
                     $width = $params['physicalWidth'];
             } 
			elseif ( isset( $params['width'] ) ) 
			{
                     $width = $params['width'];
             } else 
			{
                     throw new MWException( 'No width specified to '.__METHOD__ );
             }
             # Removed for ProofreadPage
             #$width = intval( $width );
			


			 wfDebug( __METHOD__.": width px: {$width}\n" );

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

	    	$gis = $image->getImageSize( $image, $image->getPath() );
		    		
	        $srcWidth = $gis[0];
	        $srcHeight = $gis[1];
				
			//wfDebug( __METHOD__.": srcWidth: {$srcWidth} srcHeight: {$srcHeight}\n" );
			
	        if ( isset( $params['height'] ) && $params['height'] != -1 ) {
	                if ( $params['width'] * $srcHeight > $params['height'] * $srcWidth ) {
	                        $params['width'] = wfFitBoxWidth( $srcWidth, $srcHeight, $params['height'] );
	                }
	        }
	
	        $params['height'] = File::scaleHeight( $srcWidth, $srcHeight, $params['width'] );
	        // if ( !$this->validateThumbParams( $params['width'], $params['height'], $srcWidth, $srcHeight, $mimeType ) ) {
	        //         return false;
	        // }
	
	
			//wfDebug( __METHOD__.": srcWidth: {$srcWidth} srcHeight: {$srcHeight}\n" );
			
	        return true;
	}
	
	function getPageDimensions( $image, $page ) {
    	$gis = $this->getImageSize( $image, $image->getPath() );
	    return array(
	            'width' => $gis[0],
	            'height' => $gis[1]
	    );
	}
	
	
	function getImageSize( $image, $path ) 
	{
		// ffmpeg returns the image size if you give no arguments

	 
	
		$shellret = wfShellExec( "{$egffmpegPath}ffmpeg -i ". wfEscapeShellArg( $image->getPath() ) . " 2>&1", $retval );
	
		//wfDebug( __METHOD__.": shellret: {$shellret}\n" );
	
	
		// parse output
		$result=ereg('[0-9]?[0-9][0-9][0-9]x[0-9][0-9][0-9][0-9]?', $shellret, $dimensions );
	
		$expandeddims = (explode ( 'x', $dimensions [0] ));
		
		$width = $expandeddims [0] ? $expandeddims [0] : null;
		$height = $expandeddims [1] ? $expandeddims [1] : null;
		
		//wfDebug( __METHOD__.": result: {$result}\n" );
		
		//wfDebug( __METHOD__.": width : {$width}  width : {$height} \n" );
		
		return array ($width, $height );	
    }
	

	function doTransform( $image, $dstPath, $dstUrl, $params, $flags = 0 ) 
	{
		global $egffmpegPath, $egffmpegFallback, $egffmpegMinSize;
			
		if ($params['width'] == 0) {
			$params['width'] = 500;
		}
			
		wfDebug( __METHOD__.": params['width']: {$params['width']} params['height']: {$params['height']}\n" );
		
			
		if ( !$this->normaliseParams( $image, $params ) ) {
			return new TransformParameterError( $params );
		}
		
		wfDebug( __METHOD__.": params['width']: {$params['width']} params['height']: {$params['height']}\n" );
		
		$clientWidth = $params['width'];
		$clientHeight = $params['height'];
		//$srcWidth = $image->getWidth();
		//$srcHeight = $image->getHeight();
    	$gis = $image->getImageSize( $image, $image->getPath() );
	    		
        $srcWidth = $gis[0];
        $srcHeight = $gis[1];

		$srcPath = $image->getPath();
		$retval = 0;
		

		//$outWidth=$params['physicalWidth'];
		//$outHeight=$params['physicalHeight'];
				
		$outWidth=$clientWidth;
		$outHeight=$clientHeight;
		
		// if (!is_null($egffmpegMinSize) && (($srcWidth * $srcHeight) < $egffmpegMinSize))
		// 	return parent::doTransform($image, $dstPath, $dstUrl, $params, $flags);

		if ( $outWidth == $srcWidth && $outWidth == $srcHeight ) {
			# normaliseParams (or the user) wants us to return the unscaled image
			wfDebug( __METHOD__.": returning unscaled image\n" );
			return new ThumbnailImage( $image, $image->getURL(), $clientWidth, $clientHeight, $srcPath );
		}


		wfMkdirParents( dirname( $dstPath ) );

		wfDebug( __METHOD__.": creating {$outWidth}x{$outHeight} thumbnail at $dstPath\n" );

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
