<?php
require_once 'init.php';
$theme = 'frontend';
new TSession;

if ( TSession::getValue('logged') )
{
    $content     = file_get_contents("app/templates/{$theme}/layoutManual.html");
}
else
{
    $content = file_get_contents("app/templates/{$theme}/login.html");
}

$content  = str_replace('{LIBRARIES}', file_get_contents("app/templates/{$theme}/libraries.html"), $content);
$content  = str_replace('{class}', isset($_REQUEST['class']) ? $_REQUEST['class'] : '', $content);
$content  = str_replace('{template}', $theme, $content);
$content  = str_replace('{username}', TSession::getValue('username'), $content);
$content  = str_replace('{frontpage}', TSession::getValue('frontpage'), $content);
$content  = str_replace('{query_string}', $_SERVER["QUERY_STRING"], $content);
$css      = TPage::getLoadedCSS();
$js       = TPage::getLoadedJS();
$content  = str_replace('{HEAD}', $css.$js, $content);

if (isset($_REQUEST['changeContent'])) {
  $contenToChange = $_REQUEST['changeContent'];
  switch ($contenToChange) {
      case 'clients':
          $insideContent = file_get_contents("app/resources/manual/clients.html");
          break;
      case 'calendar':
          $insideContent = file_get_contents("app/resources/manual/calendar.html");
          break;
      case 'notes':
          $insideContent = file_get_contents("app/resources/manual/notes.html");
          break;
      case 'stock':
          $insideContent = file_get_contents("app/resources/manual/stock.html");
          break;
      case 'sales':
          $insideContent = file_get_contents("app/resources/manual/sales.html");
          break;
      case 'employees':
          $insideContent = file_get_contents("app/resources/manual/employees.html");
          break;
      case 'invoices':
          $insideContent = file_get_contents("app/resources/manual/invoices.html");
          break;
      case 'payments':
          $insideContent = file_get_contents("app/resources/manual/payments.html");
          break;
      case 'charge':
          $insideContent = file_get_contents("app/resources/manual/charge.html");
          break;
  }
} else {
  $insideContent = file_get_contents("app/resources/indexManual.html");
}
$content = str_replace('{CONTENT}', $insideContent, $content);
echo $content;
?>
