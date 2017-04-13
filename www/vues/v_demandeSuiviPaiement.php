<div id="contenu">
    <h2>Liste des fiches de frais</h2>
    <form method="GET" action="index.php">
        <input type="hidden" name="uc" value="suiviPaiement">
        <input type="hidden" name="action" value="demandeSuiviPaiement">
        <select name="fiche">
            <?php foreach($fiches as $fiche): ?>
                <option value="<?= $fiche['mois'] . '-' . $fiche['idvisiteur'] ?>"><?=  substr($fiche['mois'], 4, 2) . ' - ' . $fiche['nom'] . ' ' . $fiche['prenom']; ?></option>
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
                <tr>
                    <td><?= $value['libelle'] ?></td>
                    <td><?= $value['quantite'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <h3><a href="index.php?uc=suiviPaiement&action=generatePDF&fiche=<?= $_GET['fiche'] ?>" style="line-height:28px; padding-left:10px;"><img src="images/pdf.png" width="28px">Télécharger au format PDF</a></h3>
        </p>
    <?php endif; ?>
</div>
