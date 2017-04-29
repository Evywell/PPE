<?php


use PHPUnit\Framework\TestCase;

class GestionFraisTest extends TestCase
{

    private $db;
    private $pdogsb;

    protected function setUp()
    {
        $this->pdogsb = LocalConnection::getPdoGsb();
        $this->db = $this->pdogsb->getDb();
        $this->db->exec("SET AUTOCOMMIT=0;");
        $this->db->beginTransaction();
    }

    protected function tearDown()
    {
        $this->db->rollBack();
        $this->db->exec("SET AUTOCOMMIT=1;");
    }

    public function testLesMoisNonValides()
    {
        $mois = [['mois' => '201608'],['mois' => '201608'],['mois' => '201609'],['mois' => '201610'],['mois' => '201612']];
        $this->assertEquals($mois, $this->pdogsb->getLesMoisNonValides());
    }


    public function testGetVisiteursParDate()
    {
        $visiteurs = [];
        $lesVisiteurs = [];
        $visiteurs[] = ['id' => 'a17', 'nom' => 'andre', 'prenom' => 'david'];
        $visiteurs[] = ['id' => 'a93', 'nom' => 'tusseau', 'prenom' => 'louis'];
        $visiteurs[] = ['id' => 'b16', 'nom' => 'bioret', 'prenom' => 'luc'];
        $visiteurs[] = ['id' => 'b19', 'nom' => 'bunisset', 'prenom' => 'francis'];
        $visiteurs[] = ['id' => 'b25', 'nom' => 'bunisset', 'prenom' => 'denise'];
        $visiteurs[] = ['id' => 'b28', 'nom' => 'cacheux', 'prenom' => 'bernard'];
        $visiteurs[] = ['id' => 'b34', 'nom' => 'cadic', 'prenom' => 'eric'];

        foreach ($visiteurs as $visiteur) {
            $unVisiteur = new stdClass();
            $unVisiteur->id = $visiteur['id'];
            $unVisiteur->nom = $visiteur['nom'];
            $unVisiteur->prenom = $visiteur['prenom'];
            $lesVisiteurs[] = $unVisiteur;
        }

        $visiteursParDate = $this->pdogsb->getVisiteursParDate('201608');
        $this->assertEquals(24, count($visiteursParDate));
        $this->assertEquals($lesVisiteurs, array_slice($visiteursParDate, 0, 7));
    }


    public function testMajFraisForfait()
    {
        $q = $this->db->prepare("SELECT * FROM lignefraisforfait WHERE idvisiteur = ? AND mois = ?");
        $q->execute(['a131', '201608']);
        $infos = [
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'etp', 'quantite' => 20, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'km', 'quantite' => 380, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'nui', 'quantite' => 2, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'rep', 'quantite' => 5, 'id_puissance_vehicule' => null],
        ];
        // Premier test avant modifications
        $this->assertEquals($infos, $q->fetchAll());

        $this->pdogsb->majFraisForfait('a131', '201608', ['etp' => 40, 'km' => 228, 'nui' => 8, 'rep' => 7]);

        $q = $this->db->prepare("SELECT * FROM lignefraisforfait WHERE idvisiteur = ? AND mois = ?");
        $q->execute(['a131', '201608']);
        $infos = [
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'etp', 'quantite' => 40, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'km', 'quantite' => 228, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'nui', 'quantite' => 8, 'id_puissance_vehicule' => null],
            ['idvisiteur' => 'a131', 'mois' => '201608', 'idfraisforfait' => 'rep', 'quantite' => 7, 'id_puissance_vehicule' => null],
        ];
        // Second test après modifications
        $this->assertEquals($infos, $q->fetchAll());
    }

}