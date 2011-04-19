<?php

/**
 * Image plugin.
 *
 *
 * Whip : A non-restrictive PHP framework
 * Copyright (c) 2010 Menno van Ens (codefocus.ca) and Shawn Mayzes (mayzes.org)
 * Released under the GNU General Public License, Version 3 
 *
 * @extends WhipPlugin
 */

define('IMAGE_RESIZE_FILL',         0x1001);
define('IMAGE_RESIZE_FIT',          0x1002);

class Image extends SingletonWhipPlugin {   //  uncached
    
    private $img_filename   = '';
    private $img_original   = null;
    private $img_modified   = null;
    private $dim_original;
    private $dim_modified;
    
    
    public function &getimage() {
        return $this->_getworkingimage();
    }
    public function getdimensions() {
        return $this->_getworkingdimensions();
    }

/**
*   Load image from file
*
*/
    public function load($filename) {
    #   Get supported image types
        $supported_types        = imagetypes();
    #   Try loading as JPEG
        if ($supported_types && IMG_JPG) {
            $this->img_original     = @imagecreatefromjpeg($filename);
            if ($this->img_original) return $this->_getdimensions(true);
        }
    #   Try loading as PNG
        if ($supported_types && IMG_PNG) {
            $this->img_original     = @imagecreatefrompng($filename);
            if ($this->img_original) {
                imagealphablending($this->img_original, true);
                imagesavealpha($this->img_original, true);
                return $this->_getdimensions(true);
            }
        }
    #   Try loading as GIF
        if ($supported_types && IMG_GIF) {
            $this->img_original     = @imagecreatefromgif($filename);
            if ($this->img_original) return $this->_getdimensions(true);
        }
    #   Try loading as WBMP
        if ($supported_types && IMG_WBMP) {
            $this->img_original     = @imagecreatefromwbmp($filename);
            if ($this->img_original) return $this->_getdimensions(true);
        }
    #   Try loading as XPM
        if ($supported_types && IMG_XPM) {
            $this->img_original     = @imagecreatefromxpm($filename);
            if ($this->img_original) return $this->_getdimensions(true);
        }
        
    #   Failed to load image
        //throw new WhipException('Failed to load image');
        return false;
    }   //  load
    
    
/**
*   Create new blank image
*
*/
    public function create($width, $height, $color='FFFFFF', $transparent=false) {
    //  TODO:   clear existing images
    
        $im                 = imagecreatetruecolor($width, $height);
        imagesavealpha($im, true);
        
        if ($transparent) {
            $color_bg           = $this->_getcolor($im, $color.'FF');
            imagecolortransparent($im, $color_bg);
            imagefill($im, 0, 0, $color_bg);
            imagealphablending($im, true);
            imagesavealpha($im, true);
        }
        else {
            $color_bg           = $this->_getcolor($im, $color);
            imagefill($im, 0, 0, $color_bg);
        }
        $this->img_original = &$im;
        return true;
    }   //  create
    
    
    
    
/**
*   Resize
*
*/
    public function resize($max_width, $max_height, $method=IMAGE_RESIZE_FILL) {
        $img                = $this->_getworkingimage();
        $point_dst          = $this->_point(0, 0);
        $point_src          = $this->_point(0, 0);
        $dim_dst            = $this->_point($max_width, $max_height);
        $dim_src            = $this->_getworkingdimensions();
        
    #   Calculate resize factor
        $aspect_x           = (double)($dim_src['x'] / $dim_dst['x']);
        $aspect_y           = (double)($dim_src['y'] / $dim_dst['y']);
        
        switch($method) {
        case IMAGE_RESIZE_FILL:
        #   Fill thumnail completely.
        #   Image may be cropped to fit.
            $aspect             = min($aspect_x, $aspect_y);
            $aspect             = max($aspect, 1);
            $dim_dst['x']       = min($max_width, (int)($dim_src['x'] / $aspect));
            $point_src['x']     = (int)((($dim_src['x'] / $aspect) - $dim_dst['x']) / 2 * $aspect);
            $point_src['y']     = (int)((($dim_src['y'] / $aspect) - $dim_dst['y']) / 2 * $aspect);
            $dim_src['x']       = (int)($dim_src['x'] - ($point_src['x'] * 2));
            $dim_src['y']       = (int)($dim_src['y'] - ($point_src['y'] * 2));
            break;
            
        case IMAGE_RESIZE_FIT:
        #   Fit entire image in thumbnail.
        #   Thumnail may be smaller than specified max dimensions.
            $aspect             = max($aspect_x, $aspect_y);
            $aspect             = max($aspect, 1);
            $dim_dst['x']       = (int)($dim_src['x'] / $aspect);
            $dim_dst['y']       = (int)($dim_src['y'] / $aspect);
            break;
            
        default:
        #   Unknown resize method
            return false;
            break;
        }   //  switch method
        
    #   Check if we need to resize at all
        if ($dim_dst['x'] >= $dim_src['x'] && $dim_dst['y'] >= $dim_src['y']) {
        #   Resize not necessary
            return true;
        }
    
    #   Resize
        $img_temp           = imagecreatetruecolor($dim_dst['x'], $dim_dst['y']);
        if (!imagecopyresampled(
            $img_temp, $img,
            $point_dst['x'], $point_dst['y'],
            $point_src['x'], $point_src['y'],
            $dim_dst['x'], $dim_dst['y'],
            $dim_src['x'], $dim_src['y']
        )) return false;
        
        
    #   Clean up and return
        if (is_resource($this->img_modified)) {
            imagedestroy($this->img_modified);
        }
        $this->img_modified = $img_temp;
        //$this->whip->publish(EVENT_COMMENT, 'Image processed: resize');
        return true;
    }   //  resize
    
    
/**
*   Rounded corners
*
*/
    public function roundcorners($radius=10, $color='FFFFFF') {
    #   Get image and color
        $im                 = imagecreatetruecolor($radius, $radius);//$this->_getworkingimage();
        $color_bg           = $this->_getcolor($im, $color.'FF');
        if (!$color_bg) return false;
        imagecolortransparent  ($im, $color_bg);
        imagealphablending($im, true);
        imagesavealpha  ($im, true);
        imagefill($im, 1,1, $color_bg);
        $r                  = hexdec(substr($color, 0, 2));
        $g                  = hexdec(substr($color, 2, 2));
        $b                  = hexdec(substr($color, 4, 2));
        
    #   Antialiased rim (sub-pixel!)
        $antialias_width    = 1.7;
        $radius_outer       = $radius + $antialias_width;
        $alpha_per_pixel    = (127/$antialias_width);
    #   calculate alpha for each pixel
        for ($i_y=0; $i_y<$radius; ++$i_y) {
        #   A squared
            $a_sq   = pow( ($radius - $i_y) + 1 , 2);
            for ($i_x=0; $i_x<$radius; ++$i_x) {
            #   B squared
                $b_sq   = pow( ($radius - $i_x) + 1, 2);
            #   Calc distance to center using Pythagorean theorem
            #   (Finally high school knowledge comes in handy?)
                $dist   = sqrt($a_sq + $b_sq);
            #   If past rim, continue to next line
                if ($dist<=$radius) break;
                if ($dist>$radius_outer) {
                #   Opaque
                    $a      = 0;
                }
                else {
                #   Point is within antialiased rim.
                #   Calculate opacity in a pretty sine curve
                    $a      = 127 * cos(   M_PI_2-((M_PI_2 / $antialias_width) * ($radius_outer-$dist)) );
                }
                $color  = imagecolorallocatealpha($im, $r, $g, $b, $a);
                imagesetpixel($im, $i_x, $i_y, $color);
            }   //  for x
        }   //  for y
        
    #   Apply rounded shit to working image
        $im_w               = $this->_getworkingimage();
        $dim                = $this->_getworkingdimensions();
    #   Top left
        imagecopyresampled(
            $im_w, $im,
            0, 0,
            0, 0,
            $radius, $radius,
            $radius, $radius
        );
    #   Top right
        imagecopyresampled(
            $im_w, $im,
            $dim['x']-$radius, 0,
            $radius-1, 0,
            $radius, $radius,
            -$radius, $radius
        );
    #   Bottom right
        imagecopyresampled(
            $im_w, $im,
            $dim['x']-$radius, $dim['y']-$radius,
            $radius-1, $radius-1,
            $radius, $radius,
            -$radius, -$radius
        );
    #   Bottom left
        imagecopyresampled(
            $im_w, $im,
            0, $dim['y']-$radius,
            0, $radius-1,
            $radius, $radius,
            $radius, -$radius
        );
        //$this->whip->publish(EVENT_COMMENT, 'Image processed: roundcorners');
        return true;
    }   //  roundcorners
    
    
    
    public function paste(&$image, $pct=33, $x=null, $y=null) {
    #   Get working image
        $im                 = $this->_getworkingimage();
        $dim                = $this->_getworkingdimensions();
    #   Get pastable image
        $im_paste           = $image->getimage();
        $dim_paste          = $image->getdimensions();
        
    #   Transparage
        //$color_bg           = $this->_getcolor($im, '000000');
        //imagecolortransparent  ($im, $color_bg);
        //imagealphablending($im, false);
        //imagesavealpha($im, true);
        //$color_bg           = $this->_getcolor($im_paste, '000000');
        //imagecolortransparent  ($im_paste, $color_bg);
        //imagealphablending($im_paste, true);
        //imagesavealpha($im_paste, true);
        
    #   Paste position
        $paste_pos          = $this->_point(0, 0);
        if ($x!=null) {
            if ($x>0) {
                $paste_pos['x']     = $x;
            }
            else {
                $paste_pos['x']     = $dim['x'] - $dim_paste['x'] + $x;
            }
        }
        if ($y!=null) {
            if ($y>0) {
                $paste_pos['y']     = $y;
            }
            else {
                $paste_pos['y']     = $dim['y'] - $dim_paste['y'] + $y;
            }
        }
    #   Paste!
        //imagecopymerge($im, $im_paste, $paste_pos['x'], $paste_pos['y'], 0, 0, $dim['x'], $dim['y'], $pct);
        imagecopy($im, $im_paste, $paste_pos['x'], $paste_pos['y'], 0, 0, $dim_paste['x'], $dim_paste['y']);
        
    }   //  paste
    
    
    
    public function twopointo() {
        
        
    #   Get working image
        $im                 = $this->_getworkingimage();
        $dim                = $this->_getworkingdimensions();
        
    #   Do circly things
        $color              = $this->_getcolor($im, 'FFFFFFDD');
        
        $cx                 = $dim['x']/2;
        $cy                 = 0;//$dim['y'];
        $w                  = $dim['x'];
        $h                  = $dim['y'];
        
        for ($i_h=0; $i_h<0.3; $i_h+=0.015) {
            imagefilledellipse($im, $cx, $cy, $w, $h+($h*($i_h)), $color);
        }
        
        
    }   //  rename this shit
    
    
    
    
    
    
    
    public function jpg($filename=null, $quality=90) {
        $im     = $this->_getworkingimage();
        if (!@imagejpeg($im, $filename, $quality)) {
            throw new Exception('Could not save '.$filename);
        }
    }   //  jpg
    
    public function png($filename=null) {
        $im     = $this->_getworkingimage();
        if (!@imagepng($im, $filename)) {
            throw new Exception('Could not save '.$filename);
        }
    }   //  png
    
    
    
    
    
	private function _getcolor(&$image, $color) {
		if (strlen($color) == 6) {
			$r = hexdec(substr($color, 0, 2));
			$g = hexdec(substr($color, 2, 2));
			$b = hexdec(substr($color, 4, 2));
			return imagecolorallocate($image, $r, $g, $b);
		}
		elseif (strlen($color) == 8) {
			$r = hexdec(substr($color, 0, 2));
			$g = hexdec(substr($color, 2, 2));
			$b = hexdec(substr($color, 4, 2));
			$a = (int)(hexdec(substr($color, 6, 2))/2);
			return imagecolorallocatealpha($image, $r, $g, $b, $a);
		}
		else {
			return false;
		}
	}  //  _getcolor
    
    
    private function _point($x, $y) {
        return array(
            'x'     => $x,
            'y'     => $y,
        );
    }   //  _point
    
    private function _getdimensions($original=false) {
        if ($original) {
            if (!is_resource($this->img_original)) {
                throw new Exception('Not a valid image');
            }
            $this->dim_original['x']    = imagesx($this->img_original);
            $this->dim_original['y']    = imagesy($this->img_original);
        }
        else {
            if (!is_resource($this->img_modified)) {
                throw new Exception('Not a valid image');
            }
            $this->dim_modified['x']    = imagesx($this->img_modified);
            $this->dim_modified['y']    = imagesy($this->img_modified);
        }
        return true;
    }   //  _getdimensions
    
    
    private function &_getworkingimage() {
        if (is_resource($this->img_modified)) {
            return $this->img_modified;
        }
        else {
            return $this->img_original;
        }
    }   //  _getworkingimage
    
    private function _getworkingdimensions() {
        if (is_resource($this->img_modified)) {
            $this->_getdimensions();
            return $this->dim_modified;
        }
        else {
            $this->_getdimensions(true);
            return $this->dim_original;
        }
    }   //  _getworkingimage    
    
    
    
}   //  class Image

