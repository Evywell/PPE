<?php
if(isset($_GET['action']) && $_GET['action'] !== 'generatePDF') {
    include("vues/v_sommaire.php");
}
$action = $_REQUEST['action'];
$idUtilisateur = $_SESSION['idUtilisateur'];
switch($action){

    case 'demandeSuiviPaiement':
        $fiches = $pdo->getFichesValides();

        // Une demande de suivi de fiche a été saisie
        if(isset($_GET['fiche'])){
            $infos = explode('-', $_GET['fiche']);
            if(isset($infos[0]) && isset($infos[1])){
                $laFiche['forfait'] = $pdo->getLesFraisForfait($infos[1], $infos[0]);
                $laFiche['hors_forfait'] = $pdo->getLesFraisHorsForfait($infos[1], $infos[0]);
                $laFiche['mois'] = $infos[0];
                $laFiche['visiteur'] = $infos[1];
            }else {
                $laFiche['forfait'] = $laFiche['hors_forfait'] = [];
            }

            if(empty($laFiche['forfait']) && empty($laFiche['hors_forfait'])){
                setFlash("La fiche demandée n'existe pas");
                header('location:index.php?uc=suiviPaiement&action=demandeSuiviPaiement');
                die();
            }
        }

        require "vues/v_demandeSuiviPaiement.php";
        break;

    case 'generatePDF':
        if(isset($_GET['fiche'])){
            $infos = explode('-', $_GET['fiche']);
            if(isset($infos[0]) && isset($infos[1])){
                $laFiche['visiteur'] = $pdo->getVisiteur($infos[1]);
                $laFiche['forfait'] = $pdo->getLesFraisForfait($infos[1], $infos[0]);
                $laFiche['hors_forfait'] = $pdo->getLesFraisHorsForfait($infos[1], $infos[0]);
                $vehicules = $pdo->getLesPuissances();
            }else {
                $laFiche['forfait'] = $laFiche['hors_forfait'] = [];
            }

            if(empty($laFiche['forfait']) && empty($laFiche['hors_forfait'])){
                setFlash("La fiche demandée n'existe pas");
                header('location:index.php?uc=suiviPaiement&action=demandeSuiviPaiement');
                die();
            }
        }
        require "vues/generatePDF.php";
        creerPDFFiche($laFiche, $vehicules);
        break;

    case 'metEnPaiement':
        $pdo->mettreEnPaiement($_POST['visiteur'], $_POST['mois']);
        setFlash("La fiche a bien été mise en paiement");
        header('location:index.php?uc=suiviPaiement&action=demandeSuiviPaiement');
        break;
}