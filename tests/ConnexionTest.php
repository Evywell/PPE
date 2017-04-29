<?php
require_once "LocalConnection.php";

use PHPUnit\Framework\TestCase;

class ConnexionTest extends TestCase
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

    public function testGetInfosVisiteur()
    {
        $visiteur = ['login' => 'lvillachane', 'password' => '123456'];
        $infos = $this->pdogsb->getInfosVisiteur($visiteur['login'], md5($visiteur['password']));
        $this->assertEquals(['id' => 'a131', 'nom' => 'villechalane', 'prenom' => 'louis', 'type' => 'visiteur'], $infos);

        $comptable = ['login' => 'dandre', 'password' => 'oppg5'];
        $infos = $this->pdogsb->getInfosVisiteur($comptable['login'], md5($comptable['password']));
        $this->assertEquals(['id' => 'a17', 'nom' => 'andre', 'prenom' => 'david', 'type' => 'comptable'], $infos);
    }

}