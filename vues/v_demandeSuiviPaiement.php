<div id="contenu">
    <h2>Liste des fiches de frais</h2>
    <form method="GET" action="index.php">
        <input type="hidden" name="uc" value="suiviPaiement">
        <input type="hidden" name="action" value="demandeSuiviPaiement">
        <select name="fiche">
            <?php foreach($fiches as $fiche): ?>
                <option <?= (isset($_GET['fiche']) && $laFiche['mois'] == $fiche['mois'] && $laFiche['visiteur'] == $fiche['idvisiteur']) ? 'selected' : ''; ?> value="<?= $fiche['mois'] . '-' . $fiche['idvisiteur'] ?>"><?=  substr($fiche['mois'], 4, 2) . ' - ' . $fiche['nom'] . ' ' . $fiche['prenom']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Envoyer</button>
    </form>
    <?php if(isset($laFiche)): ?>
        <h3>Les frais hors forfait</h3>
        <table style="width:100%">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($laFiche['hors_forfait'] as $value): ?>
                <tr>
                    <td><?= $value['libelle'] ?></td>
                    <td><?= $value['montant'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Les frais forfaitaires</h3>
        <table style="width:100%">
            <thead>
            <tr>
                <th>Libellé</th>
                <th>Quantité</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($laFiche['forfait'] as $value): ?>
                <?php if($value['id_puissance_vehicule'] !== null): ?>
                <tr>
                    <td><?= $value['libelle'] ?></td>
                    <td><?php echo $pdo->getPuissanceVehicule($value['id_puissance_vehicule']); ?>: <?= $value['quantite'] ?></td>
                </tr>
                <?php else: ?>
                <tr>
                    <td><?= $value['libelle'] ?></td>
                    <td><?= $value['quantite'] ?></td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <h3><a target="_blank" href="index.php?uc=suiviPaiement&action=generatePDF&fiche=<?= $_GET['fiche'] ?>" style="line-height:28px; padding-left:10px;"><img src="images/pdf.png" width="28px">Télécharger au format PDF</a></h3>
        <form action="index.php?uc=suiviPaiement&action=metEnPaiement" method="POST">
            <input type="hidden" name="mois" value="<?= $laFiche['mois'] ?>">
            <input type="hidden" name="visiteur" value="<?= $laFiche['visiteur']; ?>">
            <button>Mettre en Paiement</button>
        </form>
        </p>
    <?php endif; ?>
</div>
