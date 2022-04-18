<?php

require __DIR__ . '/vendor/autoload.php';

/*
** FILTER INPUT
*/

$url = $_GET['url'] ?? null;
$limit = intval($_GET['limit'] ?? 5);

if ($url) {
  $url = filter_var($url, FILTER_SANITIZE_URL);
}

if (! filter_var($url, FILTER_VALIDATE_URL)) {
  http_response_code(400);
  echo 'Please enter a valid URL.';
  exit;
}

/*
** SET UP PDF
*/

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('John Bertola');
$pdf->SetTitle('JCB RSS to PDF');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set font
$pdf->SetFont('times', 'BI', 20);

/*
** ITERATE OVER FEED
*/

$rss = simplexml_load_file($url);

foreach ($rss->channel->item as $item) {
  $limit--;

  if ($limit < 0) {
    break;
  }

  $pdf->AddPage();
  $pdf->setJPEGQuality(50);

  $title = (string) $item->title;
  $link = (string) $item->link;
  $imageContent = $item->children('media', true)->content->attributes() ?? null;
  $imageSrc = $imageContent['url'] ?? null;

  $h1 = "<h1>$title</h1>";
  $pdf->writeHTML($h1, true, false, true, false, '');

  $link = "<a href=\"$link\" target=\"_blank\">Go to Story</a>";
  $pdf->writeHTML($link, true, false, true, false, '');

  if ($imageSrc) {
    $imageData = file_get_contents($imageSrc);
    $imageHeight = $imageContent['height'];
    $imageWidth = $imageContent['width'];
    $pdf->Image('@'.$imageData, null, null, $imageWidth, $imageHeight);
  }
}

/*
** OUTPUT INLINE PDF
*/

$pdf->Output('jcb_rss.pdf', 'I');