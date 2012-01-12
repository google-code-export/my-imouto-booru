<?php
class ResizeError extends Exception {}

class Danbooru {
  static function reduce_to($size, $max_size, $ratio = 1, $allow_enlarge = false, $min_max = false) {
    $ret = $size;

    if ($min_max) {
      if (($max_size['width'] < $max_size['height']) != ($size['width'] < $size['height']))
        list($max_size['width'], $max_size['height']) = array($max_size['height'], $max_size['width']);
    }
    
    if ($allow_enlarge) {
      if ($ret['width'] < $max_size['width']) {
        $scale = (float)$max_size['width']/(float)$ret['width'];
        $ret['width'] =$ret['width'] * $scale;
        $ret['height'] =$ret['height'] * $scale;
      }
	    
      if (($max_size['height'] && $ret['height']) < ($ratio*$max_size['height'])) {
        $scale = (float)$max_size['height']/(float)$ret['height'];
        $ret['width'] = $ret['width'] * $scale;
        $ret['height'] = $ret['height'] * $scale;
      }
    }

    if ($ret['width'] > $ratio*$max_size['width']) {
      $scale = (float)$max_size['width']/(float)$ret['width'];
      $ret['width'] = $ret['width'] * $scale;
      $ret['height'] = $ret['height'] * $scale;
    }

    if ($max_size['height'] && ($ret['height'] > $ratio*$max_size['height'])) {
      $scale = (float)$max_size['height']/(float)$ret['height'];
      $ret['width'] = $ret['width'] * $scale;
      $ret['height'] = $ret['height'] * $scale;
    }

    $ret['width'] = round($ret['width']);
    $ret['height'] = round($ret['height']);
    return $ret;
  }
  
  # If output_quality is an integer, it specifies the JPEG output quality to use.
  #
  # If it's a hash, it's of this form:
  # { :min => 90, :max => 100, :filesize => 1048576 }
  #
  # This will search for the highest quality compression under :filesize between 90 and 100.
  # This allows cleanly filtered images to receive a high compression ratio, but allows lowering
  # the compression on noisy images.
  static function resize($file_ext, $read_path, $write_path, $output_size, $output_quality) {
    if (!is_array($output_quality))
      $output_quality = array('min' => $output_quality, 'max' => $output_quality, 'filesize' => 1024*1024*1024);
    
    # Fill needed values.
    $output_size = array_merge(array('crop_top' => 0, 'crop_bottom' => 0, 'crop_left' => 0, 'crop_right' => 0), $output_size);
    
    # A binary search is a poor fit here: we'd always have to do at least two compressions
    # to find out whether the conversion we've done is the maximum fit, and most images will
    # generally fit with maximum-quality compression anyway.  Just search linearly from :max
    # down.
    $quality = $output_quality['max'];
    // begin
      // while true
        # If :crop is set, crop between [crop_top,crop_bottom) and [crop_left,crop_right)
        # before resizing.
        self::resize_image($file_ext, $read_path, $write_path, $output_size['width'], $output_size['height'],
                              $output_size['crop_top'], $output_size['crop_bottom'], $output_size['crop_left'], $output_size['crop_right'],
                              $quality);

        # If the file is small enough, or if we're at the lowest allowed quality setting
        # already, finish.
        // return if !output_quality[:filesize].nil? && File.size(write_path) <= output_quality[:filesize]
        // return if quality <= output_quality[:min]
        // $quality--;
      // end
    // rescue IOError
      // raise
    // rescue Exception => e
      // raise ResizeError, e.to_s
    // end
  }
  
  /**
   * $file_ext is deduced according to the mime-type when creating a post.
   */
  static function resize_image($file_ext, $read_path, $write_path,
		$output_width, $output_height,
		$crop_top, $crop_bottom, $crop_left, $crop_right,
		$output_quality)
  {
    list($input_width, $input_height) = getimagesize($read_path);
    
    $crop_width = $crop_right - $crop_left;
    !$crop_width && $crop_width = $input_width;
    
    $crop_height = $crop_bottom - $crop_top;
    !$crop_height && $crop_height = $input_height;
    
    $sample = imagecreatetruecolor($output_width, $output_height);
    
    switch($file_ext){
      case 'jpg':
        $source = imagecreatefromjpeg($read_path);
        break;
      case 'png':
        $source = imagecreatefrompng($read_path);
        break;
    }
    
    imagecopyresampled($sample, $source, 0, 0, $crop_left, $crop_top, $output_width, $output_height, $crop_width, $crop_height);
    imagejpeg($sample, $write_path, $output_quality);
  }
}
?>