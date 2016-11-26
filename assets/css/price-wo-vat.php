<?php
  header("Content-type: text/css; charset: UTF-8");

$title_description = get_option('nt_eddsdfs')['css_support_callback'];
if (empty ($title_description)){$title_description = '{font-weight:300;font-size:0.7em;}';}
echo '.title-description /*{font-weight:300;font-size:0.7em;}*/
{' . $title_description . '}';




echo '.delivery-date{font-weight:300;font-size:0.9em;color:darkgray;}
.woocommerce_td{padding:10px 0;}
.delivery--date{font-weight:600;padding:5px;font-size:20px;background-color:#404040;color:#FFF;line-height:2.1em;}
.free--shipping{font-weight:600;padding:5px;font-size:20px;background-color:#EF3F32;color:#FFF;line-height:2.1em;}
.free--shipping--catalog{font-weight:100;padding:5px;background-color:#EF3F32;color:#FFF;font-size: 14px;}
.sticky-free-shipping{font-weight:300;padding:5px;font-size:15px;background-color:#EF3F32;color:#FFF;line-height:2.1em;}
.sdz{position:absolute;top:188px;left:0;z-index:5;width:100%;}
.ui--content-box-title-text .price { display: none; }
';