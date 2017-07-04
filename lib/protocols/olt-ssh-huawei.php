<?php
   $ont_info_array = array('F/S/P' => FALSE, 'ONT-ID' => FALSE, 'SN' => FALSE);
   $ont_info_lines = explode(PHP_EOL, $olt->output);
   foreach($ont_info_lines as $info_line) {
      $ont_info = explode(':', $info_line);
      switch(trim($ont_info[0])) {
         case 'F/S/P': $ont_info_array['F/S/P'] = trim($ont_info[1]); break;
         case 'ONT-ID': $ont_info_array['ONT-ID'] = trim($ont_info[1]); break;
         case 'SN': $ont_info_array['SN'] = substr(trim($ont_info[1]), 0, 16); break;
      }
   }
   /*
   F/S/P                   : 0/1/0
   ONT-ID                  : 0
   SN                      : 4857544391C59F88 (HWTC-91C59F88)
   */
?>
