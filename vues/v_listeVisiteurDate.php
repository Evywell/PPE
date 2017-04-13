<form method="POST" action="">
    
    <select name="nomvisiteur">
      <?php foreach($visiteurs as $visiteur): ?>
        <option value="<?php echo $visiteur->id ;?>"><?php echo $visiteur->nom .' '.$visiteur->prenom;?></option>
      <?php endforeach; ?>
         
    </select>
    <input type='hidden' name='anneeMois' value='<?php echo $_POST['lstmois']; ?>'><button type='submit'>Valider</button>
</form>

