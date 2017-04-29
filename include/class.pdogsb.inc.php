<?php
/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{   		
      	private static $serveur='mysql:host=localhost';
      	private static $bdd='dbname=aleduc';   		
      	private static $user='aleduc' ;      
      	private static $mdp='ohj4oSie' ;	
		private static $monPdo;
		private static $monPdoGsb=null;
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	private function __construct(){
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp);
        PdoGsb::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crée l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * Retourne les informations d'un visiteur
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom, le prénom et le type sous la forme d'un tableau associatif
*/
	public function getInfosVisiteur($login, $mdp){
		$req = "select utilisateur.id as id, utilisateur.nom as nom, utilisateur.prenom as prenom, utilisateur.type as type from utilisateur 
		where utilisateur.login=:login and utilisateur.mdp=:mdp";
		$rs = PdoGsb::$monPdo->prepare($req);
        $rs->execute(['login' => $login, 'mdp' => $mdp]);
		$ligne = $rs->fetch();
		return $ligne;
	}
  

    /**
     * Retourne tous les visiteurs
     * 
     * @return array
     */
    public function getVisiteurs(){
	    $req = "SELECT utilisateur.id, utilisateur.nom, utilisateur.prenom FROM utilisateur WHERE utilisateur.type = 'visiteur'";
        $rs = self::$monPdo->query($req);
        return $rs->fetchAll();
    }

    /**
     * Retourne le nom et le prénom du visiteur avec l'id $id
     * @param $id
     * @return array
     */
    public function getVisiteur($id){
        $req = "SELECT utilisateur.nom, utilisateur.prenom FROM utilisateur WHERE utilisateur.id = :id";
        $rs = self::$monPdo->prepare($req);
        $rs->execute(['id' => $id]);
        return $rs->fetch();
    }
/**
 * Retourne id, nom, et prenom d'un visiteur qui a des fiches de frais
 * à valider
 * 
 * @param type $date
 * @return type
 */
    public function getVisiteursParDate($date){
        $req = "SELECT id, nom, prenom FROM utilisateur 
                LEFT JOIN fichefrais ON fichefrais.idvisiteur = utilisateur.id 
                WHERE idetat != 'VA' AND mois = :date";
        $rs = self::$monPdo->prepare($req);
        $rs->execute(['date' => $date]);
        return $rs->fetchAll(PDO::FETCH_OBJ);
    }

/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
*/
	public function getLesFraisHorsForfait($idVisiteur,$mois){
	    $req = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur ='$idVisiteur' 
		and lignefraishorsforfait.mois = '$mois' ";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
        /**
         * Affiche tous les mois qui sont à valider
         * 
         * @return type
         */
        
        public function getLesMoisNonValides(){
            $req = "SELECT mois FROM fichefrais WHERE idetat = 'CL' ORDER BY mois ASC";
            return PdoGsb::$monPdo->query($req)->fetchAll();
        }
/**
 * Retourne le nombre de justificatif d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return le nombre entier de justificatifs 
*/
	public function getNbjustificatifs($idVisiteur, $mois){
		$req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['nb'];
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "select lignefraisforfait.id_puissance_vehicule, fraisforfait.id as idfrais, fraisforfait.libelle as libelle, fraisforfait.montant as montant, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait 
		where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois' 
		order by lignefraisforfait.idfraisforfait";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
 * @return un tableau associatif 
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec($req);
		}
		
	}
/**
 * met à jour le nombre de justificatifs de la table ficheFrais
 * pour le mois et le visiteur concerné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs){
		$req = "update fichefrais set nbjustificatifs = $nbJustificatifs 
		where fichefrais.idvisiteur = '$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);	
	}
/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un visiteur
 
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idVisiteur){
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');
				
		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idVisiteur','$mois',0,0,now(),'CR')";
		PdoGsb::$monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values('$idVisiteur','$mois','$unIdFrais',0)";
			PdoGsb::$monPdo->exec($req);
		 }
	}
/**
 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
		$dateFr = dateFrancaisVersAnglais($date);
		$req = "insert into lignefraishorsforfait 
		values('','$idVisiteur','$mois','$libelle','$dateFr','$montant')";
		PdoGsb::$monPdo->exec($req);
	}
/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 
 * @param $idFrais 
*/
	public function supprimerFraisHorsForfait($idFrais){
		$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =$idFrais ";
		PdoGsb::$monPdo->exec($req);
	}
/**
 * Retourne les mois pour lesquel un visiteur a une fiche de frais
 
 * @param $idVisiteur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idVisiteur){
		$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' 
		order by fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query($req);
		$lesMois =array();
		$laLigne = $res->fetch();
		while($laLigne != null)	{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
		     "mois"=>"$mois",
		    "numAnnee"  => "$numAnnee",
			"numMois"  => "$numMois"
             );
			$laLigne = $res->fetch(); 		
		}
		return $lesMois;
	}
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "select fichefrais.idetat as idEtat, fichefrais.datemodif as dateModif, fichefrais.nbjustificatifs as nbJustificatifs, 
			fichefrais.montantvalide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idetat = etat.id 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "update fichefrais set idEtat = '$etat', dateModif = now() 
		where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}

    /**
     * Retourne l'id, le nom et le prenom d'un visiteur dont sa fiche de frais n'est pas valider du mois passé en paramètre
     * @param $anneeMois
     * @return mixed
     */
    public function getVisiteurFraisNonValides($anneeMois){
            
        $req = "SELECT DISTINCT nom,prenom,id FROM fichefrais LEFT JOIN utilisateur ON utilisateur.id = idvisiteur WHERE idetat ='CR' AND mois = '$anneeMois'";
        $fichefrais = PdoGsb::$monPdo->query($req);
           
        return $fichefrais->fetchAll(PDO::FETCH_OBJ);
            
            
    }

    /**
     * Met à jour une ligne frais hors forfait avec le label [REFUSER]
     * @param $id_frais
     * @return bool
     */
    public function majFraisHorsForfait($id_frais){
        $q = self::$monPdo->prepare("SELECT libelle FROM lignefraishorsforfait WHERE id=:id");
        $q->execute(['id' => $id_frais]);
        $item = $q->fetch(PDO::FETCH_OBJ);
        $libelle = $item->libelle;
        $libelle = "[REFUSER]" . $libelle;
        if(strlen($libelle) > 100){
            $libelle = substr($libelle, 0, 97) . '...';
        }
        $q = self::$monPdo->prepare("UPDATE lignefraishorsforfait SET libelle=:libelle WHERE id=:id");
        $q->execute(['libelle' => $libelle, 'id' => $id_frais]);
        return true;
    }

    /**
     * Créé une nouvelle fiche frais
     * @param $visiteur_id
     * @param $mois
     */
    public function creeNouvelleFicheFrais($visiteur_id, $mois){
        $q = self::$monPdo->prepare("INSERT INTO fichefrais SET idvisiteur=:idvisiteur, mois=:mois, nbjustificatifs=0, montantvalide=0, datemodif=:datemodif, idetat=:etat");
        $q->execute(['idvisiteur' => $visiteur_id, 'mois' => $mois, 'datemodif' => date('Y-m-d'), 'etat' => 'CR']);
        $frais = self::$monPdo->query("SELECT * FROM fraisforfait")->fetchAll(PDO::FETCH_OBJ);
        foreach ($frais as $unFrais){
            $q = self::$monPdo->prepare("INSERT INTO lignefraisforfait SET idvisiteur=:id_visiteur, mois=:mois, idfraisforfait=:idfraisforfait, quantite=0");
            $q->execute(['id_visiteur' => $visiteur_id, 'mois' => $mois, 'idfraisforfait' => $unFrais->id]);
        }
    }

    /**
     * Reporte les frais hors forfait
     * @param $idFrais
     * @param $visiteur
     * @param $mois
     */
    public function reporterHorsForfait($idFrais, $visiteur, $mois, $libelle, $montant){
        $moisDisponibles = $this->dernierMoisSaisi($visiteur);

        $month = substr($mois, 4, 2);
        $year = substr($mois, 0, 4);

        $moisSuivant = date('Ym', strtotime("$year-$month-01 + 1 month"));
        if($moisDisponibles === $mois || !$this->ficheExists($visiteur, $moisSuivant)){
            $this->creeNouvelleFicheFrais($visiteur, $moisSuivant);
        }

        $this->creeNouveauFraisHorsForfait($visiteur, $moisSuivant, $libelle, date('d/m/Y'), $montant);
        $this->supprimerFraisHorsForfait($idFrais);
    }

    /**
     * Valide une fiche de frais
     * @param $visiteur_id
     * @param $mois
     */
    public function validerFicheFrais($visiteur_id, $mois){
        $q = self::$monPdo->prepare("UPDATE fichefrais SET idetat='VA' WHERE idvisiteur=:idvisiteur AND mois=:mois");
        $q->execute(['idvisiteur' => $visiteur_id, 'mois' => $mois]);
    }

    /**
     * Récupère un tableau de mois avec l'état 'VA'
     * @return array
     */
    public function getFichesValides(){
        $q = self::$monPdo->query("SELECT * FROM fichefrais LEFT JOIN utilisateur ON fichefrais.idvisiteur = utilisateur.id WHERE idetat = 'VA' ORDER BY mois ASC");
        return $q->fetchAll();
    }

    /**
     * Met une fiche de frais en paiement (état 'MP')
     * @param $idVisiteur
     * @param $mois
     */
    public function mettreEnPaiement($idVisiteur, $mois){
        $q = self::$monPdo->prepare("UPDATE fichefrais SET idetat = 'MP' WHERE idvisiteur=:idvisiteur AND mois=:mois");
        $q->execute(['idvisiteur' => $idVisiteur, 'mois' => $mois]);
    }
    
    /**
     * Récupère les puissances des véhicules
     * @return array
     */
    public function getLesPuissances(){
        $q = self::$monPdo->query("SELECT * FROM puissance_vehicule");
        return $q->fetchAll();
    }

    /**
     * Ajoute la puissance du véhicule
     * @param type $idFrais
     * @param type $qte
     */
    public function addKm($idFrais, $qte, $mois, $idvisiteur){
        $sql = "UPDATE lignefraisforfait SET id_puissance_vehicule=:idfrais, quantite=:qte WHERE mois=:mois AND idvisiteur=:idvisiteur AND idfraisforfait='km'";
        $q = self::$monPdo->prepare($sql);
        $q->execute(['idfrais' => $idFrais, 'qte' => $qte, 'mois' => $mois, 'idvisiteur' => $idvisiteur]);
        
    }
    
    public function getPuissanceVehicule($id){
        $q = self::$monPdo->prepare("SELECT libelle FROM puissance_vehicule WHERE id=:id");
        $q->execute(['id' => $id]);
        return $q->fetch()['libelle'];
    }

    /**
     * @param $visiteur
     * @param $mois
     * @return bool
     */
    private function ficheExists($visiteur, $mois)
    {
        $q = self::$monPdo->prepare("SELECT * FROM fichefrais WHERE idvisiteur=:visiteur AND mois=:mois");
        $q->execute(['visiteur' => $visiteur, 'mois' => $mois]);
        return ($q->rowCount() > 0);
    }
}
?>