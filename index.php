<?php
/*******************************************************************************
VLLasku: web-based invoicing application.
Copyright (C) 2010 Ere Maijala

This program is free software. See attached LICENSE.

*******************************************************************************/

/*******************************************************************************
VLLasku: web-pohjainen laskutusohjelma.
Copyright (C) 2010 Ere Maijala

Tämä ohjelma on vapaa. Lue oheinen LICENSE.

*******************************************************************************/

require_once 'sessionfuncs.php';
require_once 'navi.php';
require_once 'list.php';
require_once 'form.php';
require_once 'open_invoices.php';
require_once 'settings.php';
require_once 'localize.php';

if (!getRequest('ses', ''))
{
  header("Location: ". _PROTOCOL_ . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/login.php");
  exit;
}

$strSesID = sesVerifySession();

$strFunc = getRequest('func', 'open_invoices');
$strList = getRequest('list', '');
$strForm = getRequest('form', '');

if (!$strFunc)
  $strFunc = 'open_invoices';

if ($strFunc == 'logout')
{
  header("Location: ". _PROTOCOL_ . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/logout.php?ses=$strSesID");
  exit;
}

if (!$strFunc && $strForm)
  $strFunc = 'invoices';

$title = getPageTitle($strFunc, $strList, $strForm);

echo htmlPageStart(_PAGE_TITLE_ . " - $title");

$astrMainButtons = array (
    array("name" => "invoice", "title" => "locSHOWINVOICENAVI", 'action' => 'open_invoices', "levels_allowed" => array(1) ),
    array("name" => "archive", "title" => "locSHOWARCHIVENAVI", 'action' => 'archived_invoices', "levels_allowed" => array(1) ),
    array("name" => "company", "title" => "locSHOWCOMPANYNAVI", 'action' => 'companies', "levels_allowed" => array(1) ),
    array("name" => "reports", "title" => "locSHOWREPORTNAVI", 'action' => 'reports', "levels_allowed" => array(1) ),
    array("name" => "settings", "title" => "locSHOWSETTINGSNAVI", 'action' => 'settings', "action" => "settings", "levels_allowed" => array(1) ),
    array("name" => "system", "title" => "locSHOWSYSTEMNAVI", 'action' => 'system', "levels_allowed" => array(99) ),
    array("name" => "logout", "title" => "locLOGOUT", 'action' => 'logout', "levels_allowed" => array(1) )
);

?>

<body>
  <div class="navi">
<?php
for( $i = 0; $i < count($astrMainButtons); $i++ ) {
    $strButton = '<a class="functionlink'; 
    if ($astrMainButtons[$i]['action'] == $strFunc || ($astrMainButtons[$i]['action'] == 'open_invoices' && $strFunc == 'invoices'))
      $strButton .= ' selected';
    $strButton .= '" href="?ses=' . $GLOBALS['sesID'] . '&amp;func=' . $astrMainButtons[$i]['action'] . '">';
    $strButton .= $GLOBALS[$astrMainButtons[$i]['title']] . '</a>';
        
    if( in_array($GLOBALS['sesACCESSLEVEL'], $astrMainButtons[$i]['levels_allowed']) || $GLOBALS['sesACCESSLEVEL'] == 99 ) {
      echo "    $strButton\n";
    }
}

$level = 1;
if ($strList)
  ++$level;
if ($strForm) 
  ++$level;
$arrHistory = sesUpdateHistory($strSesID, $title, $_SERVER['QUERY_STRING'], $level);
$strBreadcrumbs = '';
foreach ($arrHistory as $arrHE)
{
  if ($strBreadcrumbs)
    $strBreadcrumbs .= '&gt; ';
  $strBreadcrumbs .= '<a href="index.php?' . str_replace('&', '&amp;', $arrHE['url']) . '">' . $arrHE['title'] . '</a>&nbsp;';
}

?>
  </div>
  <div class="breadcrumbs">
    <?php echo $strBreadcrumbs?>
  </div>

<?php
switch ($strFunc)
{
case 'open_invoices':
  createFuncMenu('open_invoices');
  createOpenInvoiceList();
  break;
case 'reports':
  createFuncMenu($strFunc);
  switch ($strForm)
  {
  case 'invoice': require_once 'invoice_report.php'; createInvoiceReport('report'); break;
  case 'product': require_once 'product_report.php'; createProductReport('report'); break;
  }
  break;
default:
  if ($strForm)
  {
    if ($strFunc == 'settings')
      createFuncMenu($strFunc);
    createForm($strFunc, $strForm);
  }
  else
  {
    createFuncMenu($strFunc);
    createList($strList ? $strList : $strFunc, $strFunc);
  }
}
?>
</body>
</html>
