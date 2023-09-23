<?php if(!defined('PLX_ROOT')) exit; ?>
<?php
	/**
		* Plugin for Pluxml
	**/	
	# Control du token du formulaire
	plxToken::validateFormToken($_POST);
	
	if(!empty($_POST)) {		
		$plxPlugin->setParam('maxWidth', $_POST['maxWidth'], 'numeric');		
		$plxPlugin->saveParams();
		header('Location: parametres_plugin.php?p='. basename(__DIR__));
		exit;
	}
	
	#init var defaut 1000 pixels
	$maxWidth =  $plxPlugin->getParam('maxWidth')=='' ? 1000 : $plxPlugin->getParam('maxWidth');
?>

<form  id="form_max_width" action="parametres_plugin.php?p=<?php echo basename(__DIR__) ;?>" method="post">
	<fieldset><legend><label for="maxWidth"><?php echo $plxPlugin->lang('L_ALLOW_MAX_WIDTH') ?>&nbsp;:</label></legend>
		<?php plxUtils::printSelect('maxWidth',array(
			'250'	=> '250 pixels',
			'500'	=> '500 pixels',
			'750'	=> '750 pixels',
			'1000'	=> '1000 pixels',
			'1200'	=> '1200 pixels'),
			$maxWidth
			); 
		echo plxToken::getTokenPostMethod() ?>
		<input type="submit" name="submit" value="<?php $plxPlugin->lang('L_SAVE') ?>"  />
	</fieldset>
</form>
<p><a href="medias.php">↩ page medias</a></p>