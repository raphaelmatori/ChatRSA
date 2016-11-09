<?php
$myfile = @fopen("../pubkeys/".$_POST['public_key'], "r") or die("");
echo fread($myfile,filesize("../public_key/".$_POST['public_key']));
fclose($myfile);
?>