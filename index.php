<?php
  $html = file_get_contents("templateV2.html");
  $html = str_replace("{{Welcome}}", "Bienvenido", $html);
  echo $html;
?>
