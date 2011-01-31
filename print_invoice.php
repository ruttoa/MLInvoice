<?php

require_once 'sessionfuncs.php';

sesVerifySession();

$printStyle = $printParameters;

$pdf=new PDF('P','mm','A4', _CHARSET_ == 'UTF-8', _CHARSET_, false);
$pdf->AddPage();
$pdf->SetAutoPageBreak(FALSE);
$pdf->footerLeft = $strAssocAddressLine;
$pdf->footerCenter = $strContactInfo;
$pdf->footerRight = "$strWww\n$strEmail";

//TOP

//sender
if (isset($logo_filedata))
{
  if (!isset($logo_top))
    $logo_top = $pdf->GetY()+5;
  if (!isset($logo_left))
    $logo_left = $pdf->GetX();
  if (!isset($logo_width) || $logo_width == 0)
    $logo_width = 80;
  if (!isset($logo_bottom_margin))
    $logo_bottom_margin = 5;

  $pdf->Image('@' . $logo_filedata, $logo_left, $logo_top, $logo_width, 0, '', '', 'N', false, 300, '', false, false, 0, true);
  $pdf->SetY($pdf->GetY() + $logo_bottom_margin);
}
else
{
  $pdf->SetTextColor(125);
  $pdf->SetFont('Helvetica','B',10);
  $pdf->SetY($pdf->GetY()+5);
  $pdf->Cell(120, 5, $strAssociation, 0, 1);
  $pdf->SetFont('Helvetica','',10);
  $pdf->MultiCell(120, 5, $strStreetAddress. "\n". $strZipCode. " ". $strCity,0,1);
  $pdf->SetY($pdf->GetY()+5);
}

//receiver
$pdf->SetTextColor(0);
$pdf->SetFont('Helvetica','B',14);
$pdf->Cell(120, 6, $strCompanyName,0,1);
$pdf->SetFont('Helvetica','',14);
$pdf->MultiCell(120, 6, $strCompanyAddress,0,1);
$pdf->SetFont('Helvetica','',12);
$pdf->SetY($pdf->GetY() + 4);
$pdf->Cell(120, 6, $strCompanyEmail,0,1);

$receiverMaxY = $pdf->GetY();

if ($printStyle == 'dispatch')
  $locStr = 'DispatchNote';
elseif ($printStyle == 'receipt')
  $locStr = 'Receipt';
else
  $locStr = 'Invoice';

//invoiceinfo headers
$pdf->SetXY(115,10);
$pdf->SetFont('Helvetica','B',12);
if ($printStyle == 'dispatch')
  $pdf->Cell(40, 5, $GLOBALS['locDispatchNoteHeader'], 0, 1, 'R');
elseif ($printStyle == 'receipt')
  $pdf->Cell(40, 5, $GLOBALS['locReceiptHeader'], 0, 1, 'R');
elseif ($intStateId == 5)
  $pdf->Cell(40, 5, $GLOBALS['locFIRSTREMINDERHEADER'], 0, 1, 'R');
elseif ($intStateId == 6)
  $pdf->Cell(40, 5, $GLOBALS['locSECONDREMINDERHEADER'], 0, 1, 'R');
else
  $pdf->Cell(40, 5, $GLOBALS['locINVOICEHEADER'], 0, 1, 'R');
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(115, $pdf->GetY()+5);
if ($intCustomerNo != 0)
{
  $pdf->Cell(40, 5, $GLOBALS['locCUSTOMERNUMBER'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, $intCustomerNo, 0, 1);
}
$pdf->SetX(115);
$pdf->Cell(40, 5, $GLOBALS["loc${locStr}Number"] . ': ', 0, 0, 'R');
$pdf->Cell(60, 5, $strInvoiceNo, 0, 1);
$pdf->SetX(115);
$pdf->Cell(40, 5, $GLOBALS["locPDF${locStr}Date"] . ': ', 0, 0, 'R');
$pdf->Cell(60, 5, $strInvoiceDate, 0, 1);
if ($printStyle == 'invoice')
{
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locPDFDUEDATE'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, $strDueDate, 0, 1);
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locTERMSOFPAYMENT'] .": ", 0, 0, 'R');
  $paymentDays = strDate2UnixTime($strDueDate)/3600/24 - strDate2UnixTime($strInvoiceDate)/3600/24;
  if ($paymentDays < 0) //weird
    $paymentDays = getSetting('invoice_payment_days');
  $pdf->Cell(60, 5, sprintf(getSetting('invoice_terms_of_payment'), $paymentDays), 0, 1);
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locPERIODFORCOMPLAINTS'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, getSetting('invoice_period_for_complaints'), 0, 1);
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locPENALTYINTEREST'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, miscRound2OptDecim(getSetting('invoice_penalty_interest'), 1) . ' %', 0, 1);
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locPDFINVREFNO'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, $strRefNumber, 0, 1);
}
if ($strReference && $printStyle != 'dispatch')
{
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locYOURREFERENCE'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, $strReference, 0, 1);
}

if ($strRefundedInvoiceNo)
{
  $pdf->SetX(115);
  $pdf->Cell(40, 5, sprintf($GLOBALS['locREFUNDSINVOICE'], $strRefundedInvoiceNo), 0, 1, 'R');
}

if ($intStateId == 5)
{
  $pdf->SetX(60);
  $pdf->SetFont('Helvetica','B',10);
  $pdf->MultiCell(150, 5, sprintf($GLOBALS['locFIRSTREMINDERNOTE'], $strRefundedInvoiceNo), 0, 'L', 0);
  $pdf->SetFont('Helvetica','',10);
}
elseif ($intStateId == 6)
{
  $pdf->SetX(60);
  $pdf->SetFont('Helvetica','B',10);
  $pdf->MultiCell(150, 5, sprintf($GLOBALS['locSECONDREMINDERNOTE'], $strRefundedInvoiceNo), 0, 'L', 0);
  $pdf->SetFont('Helvetica','',10);
}

$pdf->SetY(max($pdf->GetY(), $receiverMaxY) + 5);
$pdf->Line(5, $pdf->GetY(), 202, $pdf->GetY());
$pdf->SetY($pdf->GetY()+5);

$intStartY = 187;
$intMaxRowsY = $intStartY - 35;

if ($printStyle != 'invoice' || (!getSetting('invoice_separate_statement') && !isset($boolSeparateStatement)))
{
  if ($printStyle != 'invoice')
  {
    $pdf->SetAutoPageBreak(TRUE, 22);
    $pdf->printFooterOnFirstPage = true;
  }

  if ($printStyle == 'dispatch')
    $nameColWidth = 120;
  else
    $nameColWidth = 80;

  //middle - invoicerows
  //invoiceinfo headers
  $pdf->SetX(7);
  if( getSetting('invoice_show_row_date') ) {
      $pdf->Cell($nameColWidth - 20, 5, $GLOBALS['locROWNAME'], 0, 0, "L");
      $pdf->Cell(20, 5, $GLOBALS['locDATE'], 0, 0, "L");
  }
  else {
      $pdf->Cell($nameColWidth, 5, $GLOBALS['locROWNAME'], 0, 0, "L");
  }
  if ($printStyle != 'dispatch')
    $pdf->Cell(15, 5, $GLOBALS['locROWPRICE'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locPCS'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locUNIT'], 0, 0, "R");
  if ($printStyle != 'dispatch')
  {
    $pdf->Cell(20, 5, $GLOBALS['locROWTOTAL'], 0, 0, "R");
    $pdf->Cell(15, 5, $GLOBALS['locVATPERCENT'], 0, 0, "R");
    $pdf->Cell(15, 5, $GLOBALS['locTAX'], 0, 0, "R");
    $pdf->Cell(20, 5, $GLOBALS['locROWTOTAL'], 0, 1, "R");
  }
  //rows
  $pdf->SetY($pdf->GetY()+5);
  for( $i = 0; $i < $intNRes; $i++ ) {
      if ($printStyle == 'invoice' && $pdf->GetY() > $intMaxRowsY)
      {
        $boolSeparateStatement = 1;
        require 'print_invoice.php';
        exit;
      }
  
      if( $astrRowPrice[$i] == 0 && $astrPieces[$i] == 0 ) {
          $pdf->SetX(7);
          $pdf->MultiCell(0, 5, $astrDescription[$i], 0, 'L');
      }
      else {
          if( getSetting('invoice_show_row_date') ) {
              $pdf->SetX($nameColWidth - 20 + 7);
              $pdf->Cell(20, 5, $astrRowDate[$i], 0, 0, "L");
          }
          else {
              $pdf->SetX($nameColWidth + 7);
          }
          if ($printStyle != 'dispatch')
            $pdf->Cell(15, 5, miscRound2Decim($astrRowPrice[$i]), 0, 0, "R");
          $pdf->Cell(15, 5, miscRound2Decim($astrPieces[$i]), 0, 0, "R");
          $pdf->Cell(15, 5, $astrRowType[$i], 0, 0, "R");
          if ($printStyle != 'dispatch')
          {
            $pdf->Cell(20, 5, miscRound2Decim($intRowSum[$i]), 0, 0, "R");
            $pdf->Cell(15, 5, miscRound2OptDecim($astrVAT[$i], 1), 0, 0, "R");
            $pdf->Cell(15, 5, miscRound2Decim($intRowVAT[$i]), 0, 0, "R");
            $pdf->Cell(20, 5, miscRound2Decim($intRowSumVAT[$i]), 0, 0, "R");
          }
          $pdf->SetX(7);
          if( getSetting('invoice_show_row_date') ) {
              $pdf->MultiCell($nameColWidth - 20, 5, $astrDescription[$i], 0, 'L');
          }
          else {
              $pdf->MultiCell($nameColWidth, 5, $astrDescription[$i], 0, 'L');
          }
      }
  }
  if ($printStyle != 'dispatch')
  {
    $pdf->SetFont('Helvetica','',10);
    $pdf->SetY($pdf->GetY()+10);
    $pdf->Cell(162, 5, $GLOBALS['locTOTALEXCLUDINGVAT'] .": ", 0, 0, "R");
    $pdf->SetX(182);
    $pdf->Cell(20, 5, miscRound2Decim($intTotSum), 0, 0, "R");
    
    $pdf->SetFont('Helvetica','',10);
    $pdf->SetY($pdf->GetY()+5);
    $pdf->Cell(162, 5, $GLOBALS['locTOTALVAT'] .": ", 0, 0, "R");
    $pdf->SetX(182);
    $pdf->Cell(20, 5, miscRound2Decim($intTotVAT), 0, 0, "R");
    
    $pdf->SetFont('Helvetica','B',10);
    $pdf->SetY($pdf->GetY()+5);
    $pdf->Cell(162, 5, $GLOBALS['locTOTALINCLUDINGVAT'] .": ", 0, 0, "R");
    $pdf->SetX(182);
    $pdf->Cell(20, 5, miscRound2Decim($intTotSumVAT), 0, 1, "R");
  }
}
else {
    $pdf->SetFont('Helvetica','B',20);
    $pdf->SetXY(20, $pdf->GetY()+40);
    $pdf->MultiCell(180, 5, $GLOBALS['locSEESEPARATESTATEMENT'], 0, "L", 0);
}

if ($printStyle == 'invoice')
{
  //bottom - paymentinfo
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY(7, $intStartY);
  $pdf->MultiCell(120, 5, $strAssocAddressLine, 0, "L", 0);
  $pdf->SetXY(75, $intStartY);
  $pdf->MultiCell(65, 5, $strContactInfo, 0, "C", 0);
  $pdf->SetXY(140, $intStartY);
  $pdf->MultiCell(60, 5, "$strEmail\n$strWww", 0, "R", 0);
  
  
  // Invoice form
  $intStartY = $intStartY + 8;
  $intStartX = 7;

  $intMaxX = 200;
  //1. hor.line - full width
  $pdf->SetLineWidth(0.13);
  $pdf->Line($intStartX, $intStartY - 0.5, $intMaxX, $intStartY - 0.5);
  $pdf->SetLineWidth(0.50);
  //2. hor.line - full width
  $pdf->Line($intStartX, $intStartY+16, $intMaxX, $intStartY+16);
  //3. hor.line - start-half page
  $pdf->Line($intStartX, $intStartY+32, $intStartX+111.4, $intStartY+32);
  //4. hor.line - half-end page
  $pdf->Line($intStartX+111.4, $intStartY+57.5, $intMaxX, $intStartY+57.5);
  //5. hor.line - full width
  $pdf->Line($intStartX, $intStartY+66, $intMaxX, $intStartY+66);
  //6. hor.line - full width
  $pdf->Line($intStartX, $intStartY+74.5, $intMaxX, $intStartY+74.5);
  
  //1. ver.line - 1.hor - 3.hor
  $pdf->Line($intStartX+20, $intStartY, $intStartX+20, $intStartY+32);
  //2. ver.line - 5.hor - 6.hor
  $pdf->Line($intStartX+20, $intStartY+66, $intStartX+20, $intStartY+74.5);
  //3. ver.line - 1.hor - 2.hor
  $pdf->SetLineWidth(0.13);
  $pdf->Line($intStartX+162, $intStartY, $intStartX+162, $intStartY+16);
  $pdf->SetLineWidth(0.50);
  //4. ver.line - full height
  $pdf->Line($intStartX+111.4, $intStartY, $intStartX+111.4, $intStartY+74.5);
  //5. ver.line - 4.hor - 6. hor
  $pdf->Line($intStartX+130, $intStartY+57.5, $intStartX+130, $intStartY+74.5);
  //6. ver.line - 5.hor - 6. hor
  $pdf->Line($intStartX+160, $intStartY+66, $intStartX+160, $intStartY+74.5);
  
  //underscript
  $pdf->SetLineWidth(0.13);
  $pdf->Line($intStartX+23, $intStartY+63, $intStartX+90, $intStartY+63);
  
  //receiver bank
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX, $intStartY + 1);
  $pdf->MultiCell(19, 2.8, "Saajan\ntilinumero", 0, "R", 0);
  $pdf->SetXY($intStartX, $intStartY + 8);
  $pdf->MultiCell(19, 2.8, "Mottagarens\nkontonummer", 0, "R", 0);
  $pdf->SetXY($intStartX + 112.4, $intStartY + 0.5);
  $pdf->Cell(10, 2.8, "IBAN", 0, 1, "L");
  $pdf->SetXY($intStartX + 162.4, $intStartY + 0.5);
  $pdf->Cell(10, 2.8, "BIC", 0, 1, "L");
  
  // account 1
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 22, $intStartY + 1);
  $pdf->Cell(15, 4, $strBankName1, 0, 0, "L");
  $pdf->SetX($intStartX + 65);
  $pdf->Cell(40, 4, $strBankAccount1, 0, 0, "L");
  $pdf->SetX($intStartX + 120.4);
  $pdf->Cell(50, 4, $strBankIBAN1, 0, 0, "L");
  $pdf->SetX($intStartX + 170.4);
  $pdf->Cell(15, 4, $strBankSWIFTBIC1, 0, 0, "L");
  
  // account 2
  $pdf->SetXY($intStartX + 22, $intStartY + 5);
  $pdf->Cell(15, 4, $strBankName2, 0, 0, "L");
  $pdf->SetX($intStartX + 65);
  $pdf->Cell(40, 4, $strBankAccount2, 0, 0, "L");
  $pdf->SetX($intStartX + 120.4);
  $pdf->Cell(50, 4, $strBankIBAN2, 0, 0, "L");
  $pdf->SetX($intStartX + 170.4);
  $pdf->Cell(15, 4, $strBankSWIFTBIC2, 0, 0, "L");
  
  // account 3
  $pdf->SetXY($intStartX + 22, $intStartY + 9);
  $pdf->Cell(15, 4, $strBankName3, 0, 0, "L");
  $pdf->SetX($intStartX + 65);
  $pdf->Cell(40, 4, $strBankAccount3, 0, 0, "L");
  $pdf->SetX($intStartX + 120.4);
  $pdf->Cell(50, 4, $strBankIBAN3, 0, 0, "L");
  $pdf->SetX($intStartX + 170.4);
  $pdf->Cell(15, 4, $strBankSWIFTBIC3, 0, 0, "L");
  
  //receiver
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX, $intStartY + 18);
  $pdf->Cell(19, 5, "Saaja", 0, 1, "R");
  $pdf->SetXY($intStartX, $intStartY + 22);
  $pdf->Cell(19, 5, "Mottagare", 0, 1, "R");
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 22,$intStartY + 18);
  $pdf->MultiCell(100, 4, $strAssocAddress,0,1);
  
  //payer
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX, $intStartY + 35);
  $pdf->MultiCell(19, 2.8, "Maksajan\nnimi ja\nosoite", 0, "R", 0);
  $pdf->SetXY($intStartX, $intStartY + 45);
  $pdf->MultiCell(19, 2.8, "Betalarens\nnamn och\naddress", 0, "R", 0);
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 22, $intStartY + 35);
  $pdf->MultiCell(100, 4, $strBillingAddress,0,1);
  
  //underscript
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX, $intStartY + 60);
  $pdf->Cell(19, 5, "Allekirjoitus", 0, 1, "R");
  //from account
  $pdf->SetXY($intStartX, $intStartY + 68);
  $pdf->Cell(19, 5, cond_utf8_encode('Tililt�'), 0, 1, "R");
  
  //info
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 112.4, $intStartY + 20);
  $pdf->Cell(70, 5, "Laskunumero ".$strInvoiceNo, 0, 1, "L");
  $pdf->SetXY($intStartX + 112.4, $intStartY + 30);
  $pdf->Cell(70, 5, "Viitenumero on aina mainittava maksettaessa.", 0, 1, "L");
  $pdf->SetXY($intStartX + 112.4, $intStartY + 35);
  $pdf->Cell(70, 5, cond_utf8_encode('Referensnumret b�r alltid anges vid betalning.'), 0, 1, "L");
  //terms
  $pdf->SetFont('Helvetica','',5);
  $pdf->SetXY($intStartX + 133, $intStartY + 85);
  $pdf->MultiCell(70, 2, cond_utf8_encode("Maksu v�litet��n saajalle maksujenv�lityksen ehtojen mukaisesti ja vain\nmaksajan ilmoittaman tilinumeron perusteella"),0,1);
  $pdf->SetXY($intStartX + 133, $intStartY + 90);
  $pdf->MultiCell(70, 2, cond_utf8_encode("Betalningen f�rmedlas till mottagaren enligt villkoren f�r betalnings-\nf�rmedling och endast till det kontonummer som betalaren angivit"),0,1);
  $pdf->SetFont('Helvetica','',6);
  $pdf->SetXY($intStartX + 133, $intStartY + 95);
  $pdf->Cell($intMaxX + 1 - 133 - $intStartX, 5, "PANKKI BANKEN", 0, 1, "R");
  
  
  $pdf->SetFont('Helvetica','',7);
  //refno
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX + 112.4, $intStartY + 59);
  $pdf->Cell(15, 5, "Viitenro", 0, 1, "L");
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 131, $intStartY + 59);
  $pdf->Cell(15, 5, $strRefNumber, 0, 1, "L");
  
  //duedate
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX + 112.4, $intStartY + 68);
  $pdf->Cell(15, 5, cond_utf8_encode('Er�p�iv�'), 0, 1, "L");
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 131.4, $intStartY + 68);
  $pdf->Cell(25, 5, $strFormDueDate, 0, 1, "L");
  
  //eur
  $pdf->SetFont('Helvetica','',7);
  $pdf->SetXY($intStartX + 161, $intStartY + 68);
  $pdf->Cell(15, 5, "Euro", 0, 1, "L");
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetXY($intStartX + 151, $intStartY + 68);
  $pdf->Cell(40, 5, miscRound2Decim($intTotSumVAT), 0, 1, "R");
  
  //barcode
  /*
  1  	Currency (1=FIM, 2=EURO. EURO must not be used before 1.1.1999!)
  14 	Zero-padded account number. The zeroes are added after the sixth number except in numbers that begin with 4 or 5. Those are padded after the seventh number.
  8 	Amount. The format is xxxxxx.xx, so you can't charge your customers millions ;)
  20 	Reference Number
  6 	Due Date. Format is YYMMDD.
  4 	Zero padding
  1 	Check code 1
  */
  if( getSetting('invoice_show_barcode') && $intTotSumVAT > 0) 
  {
    if (strpos($strBankAccount1, '-') === false)
    {
      error_log('No dash in account number, barcode not created');
    }
    else
    {
      if ($intTotSumVAT >= 1000000)
      {
        error_log('Sum too large, barcode not created');
      }
      else
      {
        $tmpAccount = str_replace("-", str_repeat('0', 14 -(strlen($strBankAccount1)-1)),$strBankAccount1);
        $tmpSum = str_replace(",", "", miscRound2Decim($intTotSumVAT));
        $tmpSum = str_repeat('0', 8 - strlen($tmpSum)). $tmpSum;
        $tmpRefNumber = str_replace(" ", "", $strRefNumber);
        $tmpRefNumber = str_repeat('0', 20 - strlen($tmpRefNumber)). $tmpRefNumber;
        $atmdDueDate = explode(".", $strDueDate);
        $tmpDueDate = substr($atmdDueDate[2], -2). $atmdDueDate[1]. $atmdDueDate[0];
        
        $code_string = "2". $tmpAccount. $tmpSum. $tmpRefNumber. $tmpDueDate. "0000";
        $code_string = $code_string. miscCalcCheckNo($code_string);
    
        $style = array(
          'position' => '',
          'align' => 'C',
          'stretch' => true,
          'fitwidth' => true,
          'cellfitalign' => '',
          'border' => false,
          'hpadding' => 'auto',
          'vpadding' => 'auto',
          'fgcolor' => array(0,0,0),
          'bgcolor' => false, //array(255,255,255),
          'text' => false,
          'font' => 'helvetica',
          'fontsize' => 8,
          'stretchtext' => 4
        );
        $pdf->write1DBarcode($code_string, 'C128C', 20, 284, 105, 11, 0.34, $style, 'N');
      }
    }
  }
}

if ($printStyle == 'invoice' && (getSetting('invoice_separate_statement') || isset($boolSeparateStatement))) 
{
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(TRUE, 22);
  //middle - invoicerows
  //invoiceinfo headers
  
  $pdf->SetFont('Helvetica','B',20);
  $pdf->SetXY(7, $pdf->GetY());
  $pdf->Cell(80, 5, $GLOBALS['locINVOICESTATEMENT'], 0, 0, "L");
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetX(115);
  $pdf->Cell(40, 5, $GLOBALS['locInvoiceNumber'] .": ", 0, 0, 'R');
  $pdf->Cell(60, 5, $strInvoiceNo, 0, 1);
  $pdf->SetXY(7, $pdf->GetY()+10);
  if( getSetting('invoice_show_row_date') ) {
      $pdf->Cell(60, 5, $GLOBALS['locROWNAME'], 0, 0, "L");
      $pdf->Cell(20, 5, $GLOBALS['locDATE'], 0, 0, "L");
  }
  else {
      $pdf->Cell(80, 5, $GLOBALS['locROWNAME'], 0, 0, "L");
  }
  $pdf->Cell(15, 5, $GLOBALS['locPRICE'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locPCS'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locUNIT'], 0, 0, "R");
  $pdf->Cell(20, 5, $GLOBALS['locTOTAL'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locVATPERCENT'], 0, 0, "R");
  $pdf->Cell(15, 5, $GLOBALS['locTAX'], 0, 0, "R");
  $pdf->Cell(20, 5, $GLOBALS['locTOTAL'], 0, 1, "R");
  
  //rows
  $pdf->SetY($pdf->GetY()+5);
  for( $i = 0; $i < $intNRes; $i++ ) {
      if( $astrRowPrice[$i] == 0 && $astrPieces[$i] == 0 ) {
          $pdf->SetX(7);
          $pdf->MultiCell(0, 5, $astrDescription[$i], 0, 'L');
      }
      else {
          if( getSetting('invoice_show_row_date') ) {
              $pdf->SetX(67);
              $pdf->Cell(20, 5, $astrRowDate[$i], 0, 0, "L");
          }
          else {
              $pdf->SetX(87);
          }
          $pdf->Cell(15, 5, miscRound2Decim($astrRowPrice[$i]), 0, 0, "R");
          $pdf->Cell(15, 5, miscRound2Decim($astrPieces[$i]), 0, 0, "R");
          $pdf->Cell(15, 5, $astrRowType[$i], 0, 0, "R");
          $pdf->Cell(20, 5, miscRound2Decim($intRowSum[$i]), 0, 0, "R");
          $pdf->Cell(15, 5, miscRound2OptDecim($astrVAT[$i], 1), 0, 0, "R");
          $pdf->Cell(15, 5, miscRound2Decim($intRowVAT[$i]), 0, 0, "R");
          $pdf->Cell(20, 5, miscRound2Decim($intRowSumVAT[$i]), 0, 0, "R");
          $pdf->SetX(7);
          if( getSetting('invoice_show_row_date') ) {
              $pdf->MultiCell(60, 5, $astrDescription[$i], 0, 'L');
          }
          else {
              $pdf->MultiCell(80, 5, $astrDescription[$i], 0, 'L');
          }
          
          
      }
  }
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetY($pdf->GetY()+10);
  $pdf->Cell(162, 5, $GLOBALS['locTOTALEXCLUDINGVAT'] .": ", 0, 0, "R");
  $pdf->SetX(182);
  $pdf->Cell(20, 5, miscRound2Decim($intTotSum), 0, 0, "R");
  
  $pdf->SetFont('Helvetica','',10);
  $pdf->SetY($pdf->GetY()+5);
  $pdf->Cell(162, 5, $GLOBALS['locTOTALVAT'] .": ", 0, 0, "R");
  $pdf->SetX(182);
  $pdf->Cell(20, 5, miscRound2Decim($intTotVAT), 0, 0, "R");
  
  $pdf->SetFont('Helvetica','B',10);
  $pdf->SetY($pdf->GetY()+5);
  $pdf->Cell(162, 5, $GLOBALS['locTOTALINCLUDINGVAT'] .": ", 0, 0, "R");
  $pdf->SetX(182);
  $pdf->Cell(20, 5, miscRound2Decim($intTotSumVAT), 0, 1, "R");

}

$filename = $printOutputFileName ? $printOutputFileName : getSetting('invoice_pdf_filename');
$pdf->Output(sprintf($filename, $strInvoiceNo), 'I');