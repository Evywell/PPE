    <!-- Division pour le sommaire -->
    <div id="menuGauche">
     <div id="infosUtil">
    
        <h2>
    
</h2>
    
      </div>  
        <ul id="menuList">
			<li >
                <?php echo ucfirst($_SESSION['type']); ?>:<br>
				<?php echo $_SESSION['prenom']."  ".$_SESSION['nom']  ?>
			</li>
            <?php if($_SESSION['type'] === "comptable"): ?>
                <li class="smenu">
                    <a href="index.php?uc=gererValidationFrais&action=demandeValiderFrais">Valider les fiches de frais</a>
                </li>
                <li class="smanu">
                    <a href="index.php?uc=suiviPaiement&action=demandeSuiviPaiement">Suivi de paiement</a>
                </li>
            <?php else: ?>
               <li class="smenu">
                  <a href="index.php?uc=gererFrais&action=saisirFrais" title="Saisie fiche de frais ">Saisie fiche de frais</a>
               </li>
               <li class="smenu">
                  <a href="index.php?uc=etatFrais&action=selectionnerMois" title="Consultation de mes fiches de frais">Mes fiches de frais</a>
               </li>
            <?php endif; ?>
            <li class="smenu">
              <a href="index.php?uc=connexion&action=deconnexion" title="Se déconnecter">Déconnexion</a>
           </li>
         </ul>
        
    </div>
    