<?php
function creerPDFFiche($laFiche){
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . "fpdf" . DIRECTORY_SEPARATOR . "fpdf.php";
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->Image("images/logo.jpg", 77, 10, 50, 36);
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->Cell(500, 100, "REMBOURSEMENT DE FRAIS ENGAGES", 0, 0, 'center');
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(10,10,"Visiteur",0,0,'left');
    // Faire le reste
    ob_end_clean();
    
    $pdf->Output();
}