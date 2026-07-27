<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class M_helper extends CI_Model {
    function __construct()
    {
        parent::__construct();
    }
 
    function to_time_ago( $time ) { 
          
        // Calculate difference between current 
        // time and given timestamp in seconds 
        $diff = time() - $time; 
          
        if( $diff < 1 ) {  
            return 'less than 1 second ago';  
        } 
          
        $time_rules = array (  
                    12 * 30 * 24 * 60 * 60 => 'tahun', 
                    30 * 24 * 60 * 60       => 'bulan', 
                    24 * 60 * 60           => 'hari', 
                    60 * 60                   => 'jam', 
                    60                       => 'menit', 
                    1                       => 'detik'
        ); 
      
        foreach( $time_rules as $secs => $str ) { 
              
            $div = $diff / $secs; 
      
            if( $div >= 1 ) { 
                  
                $t = round( $div ); 
                  
                return $t . ' ' . $str . ' yang lalu'; 
            } 
        } 
    }
 
    public function substrwords($text, $maxchar, $end='...') {
      if (strlen($text) > $maxchar || $text == '') {
          $words = preg_split('/\s/', $text);      
          $output = '';
          $i      = 0;
          while (1) {
              $length = strlen($output)+strlen($words[$i]);
              if ($length > $maxchar) {
                  break;
              } 
              else {
                  $output .= " " . $words[$i];
                  ++$i;
              }
          }
          $output .= $end;
      } 
      else {
          $output = $text;
      }
      return $output;
    }
}
