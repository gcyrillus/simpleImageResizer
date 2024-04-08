<?php
	
	/* plugin pour PluXml . simplifie l'upload : pas de miniature, une configuration pour largeur maximale */
	class simpleImageResizer extends plxPlugin
	{
		# initialisation des variables - reflets des variables natives pour les fonctions copiées/réecrites
		public $path = null; 			# chemin vers les médias
		public $dir = null;	 			# chemin vers le dossier medias affiché
		public $aDirs = array(); 		# liste des dossiers et sous dossiers
		public $aFiles = array(); 		# liste des fichiers d'un dossier
		public $maxUpload = array();	# valeur upload_max_filesize
		public $maxPost = array(); 		# valeur post_max_size	
		public $img_supported = array('.png', '.gif', '.jpg', '.jpeg', '.bmp', '.webp'); # images formats supported
		public $img_exts = '/\.(jpe?g|png|gif|bmp|webp)$/i';
		public $doc_exts = '/\.(7z|aiff|asf|avi|csv|docx?|epub|fla|flv|gpx|gz|gzip|m4a|m4v|mid|mov|mp3|mp4|mpc|mpe?g|ods|odt|odp|ogg|pdf|pptx?|ppt|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|svg|swf|sxc|sxw|tar|tgz|txt|vtt|wav|webm|wma|wmv|xcf|xlsx?|zip)$/i';	
		const BEGIN_CODE = '<?php # ' . __CLASS__ . ' plugin' . PHP_EOL;
		const END_CODE = PHP_EOL . '?>';		
		
		public function __construct($default_lang) {
			# appel du constructeur de la classe plxPlugin (obligatoire)
			parent::__construct($default_lang);
			
			# Déclaration des hooks
			$this->addHook('AdminMediasPrepend','AdminPrepend');
			$this->addHook('AdminMediasFoot','AdminMediasFoot');
			$this->addHook('AdminMediasTop','AdminMediasTop');
			
			
			
			# droits pour accèder à la page admin.php du plugin
			$this->setAdminProfil(PROFIL_ADMIN);
			
			# droits pour accèder à la page config.php du plugin
			$this->setConfigProfil(PROFIL_ADMIN);
			
			# Recherche du type de medias à afficher via la session
			global $plxAdmin;
			if(empty($_SESSION['medias'])) {
				$_SESSION['medias'] = $plxAdmin->aConf['medias'];
				$_SESSION['folder'] = '';
			}
			elseif(!empty($_POST['folder'])) {
				$_SESSION['currentfolder']= (isset($_SESSION['folder'])?$_SESSION['folder']:'');
				$_SESSION['folder'] = ($_POST['folder']=='.'?'':$_POST['folder']);
			}	
			
			# Initialisation Reprises du fonctionnement des variables utilisé par PluXml à l'attention des deux fonctions de remplacements
			$this->path =PLX_ROOT.$_SESSION['medias'];
			$this->dir = $_SESSION['folder'];
			
			# Taille maxi des fichiers
			$maxUpload = strtoupper(ini_get("upload_max_filesize"));
			$this->maxUpload['display'] = str_replace('M', ' Mo', $maxUpload);
			$this->maxUpload['display'] = str_replace('K', ' Ko', $this->maxUpload['display']);
			if(substr_count($maxUpload, 'K')) $this->maxUpload['value'] = str_replace('K', '', $maxUpload) * 1024;
			elseif(substr_count($maxUpload, 'M')) $this->maxUpload['value'] = str_replace('M', '', $maxUpload) * 1024 * 1024;
			elseif(substr_count($maxUpload, 'G')) $this->maxUpload['value'] = str_replace('G', '', $maxUpload) * 1024 * 1024 * 1024;
			else $this->maxUpload['value'] = 0;
			
			# Taille maxi des données
			$maxPost = strtoupper(ini_get("post_max_size"));
			$this->maxPost['display'] = str_replace('M', ' Mo', $maxPost);
			$this->maxPost['display'] = str_replace('K', ' Ko', $this->maxPost['display']);
			if(substr_count($maxPost, 'K')) $this->maxPost['value'] = str_replace('K', '', $maxPost) * 1024;
			elseif(substr_count($maxPost, 'M')) $this->maxPost['value'] = str_replace('M', '', $maxPost) * 1024 * 1024;
			elseif(substr_count($maxPost, 'G')) $this->maxPost['value'] = str_replace('G', '', $maxPost) * 1024 * 1024 * 1024;
			else $this->maxPost['value'] = 0;
			
		}
		
		/* charge le javascript */
		public function AdminMediasTop() {
			echo self::BEGIN_CODE;
		?>
		//code injecter dans medias.php
		include(PLX_ROOT.'plugins/<?= basename(__DIR__)?>/simpleImageResizerForm.php');
		exit;//on sort maintenant
		<?php
			echo self::END_CODE;			
		}
		
		/* affiche un texte dans le pied*/
		public function AdminMediasFoot() {
			echo '<p><b>Plugin: </b>simpleImageResizer . Configurer à: <b>'.$this->getParam('maxWidth').' pixels</b> . <a href="parametres_plugin.php?p='.basename(__DIR__).'">Modifier la configuration</a></p>';
		}
		
		/* recuperation de l'envoi d'un fichier avant traitement par les fonctions natives */
		public function AdminPrepend() {
			if(!empty($_POST['btn_upload'])) {
				$this->uploadFiles($_FILES, $_POST);
				unset($_POST['btn_upload']);// on vide le champ traité
			}
		}
		/* fonction native du même nom réecrite */
		public function uploadFiles($usrfiles, $post) {				
			$files = array();
			if(isset($post['myfiles'])) {
				foreach($post['myfiles'] as $key => $val) {
					list($selnum, $selval) = explode('_', $val);
					$files[] = array(
					'name'      => $usrfiles['selector_'.$selnum]['name'][$selval],
					'size'      => $usrfiles['selector_'.$selnum]['size'][$selval],
					'tmp_name'  => $usrfiles['selector_'.$selnum]['tmp_name'][$selval]
					);
				}
			}
			
			$count=0;
			foreach($files as $file) {
				$thumb = false;
				$resize = false;
				$max_width =  $this->getParam('maxWidth')=='' ? 1000 : $this->getParam('maxWidth');
				
				// Récupération du width et height de l'image
				if (getimagesize($file['tmp_name'])) {
					list($width, $height) = getimagesize($file['tmp_name']);
				}
				
				$new_width = $width < $max_width ? $width : $max_width;
				$new_height = $width != $new_width ? $new_width * $height / $width : $height;
				if ($width != $new_width || $height != $new_height) {
					$resize = array('width' => $new_width, 'height' => $new_height);
				}		
				
				if($res=$this->_uploadFile($file, $resize, $thumb)) {
					switch($res) {
						case L_PLXMEDIAS_WRONG_FILESIZE:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILESIZE);
						break;
						case L_PLXMEDIAS_WRONG_FILEFORMAT:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILEFORMAT);
						break;
						case L_PLXMEDIAS_UPLOAD_ERR:
						return plxMsg::Error(L_PLXMEDIAS_UPLOAD_ERR);
						break;
						case L_PLXMEDIAS_UPLOAD_SUCCESSFUL:
						$count++;
						break;
					}
				}
			}
			
			if($count==1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOAD_SUCCESSFUL);
			elseif($count>1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOADS_SUCCESSFUL);
		}
		/* fonction native du même nom ,  modifiable aux besoins */
		public function _uploadFile($file, $resize, $thumb) {
			
			$i = 1;
			$filename = array();
			
			if($file['name'] == '')
			return false;
			
			if($file['size'] > $this->maxUpload['value'])
			return L_PLXMEDIAS_WRONG_FILESIZE;
			
			if(!preg_match($this->img_exts, $file['name']) AND !preg_match($this->doc_exts, $file['name']))
			return L_PLXMEDIAS_WRONG_FILEFORMAT;
			
			// On teste l'existence du fichier et on formate son nom pour éviter les doublons
			$filename = pathinfo($file['name']);
			$filename['filename'] = plxUtils::urlify($filename['filename']);
			$upFile = $this->path.$this->dir.$filename['filename'].".".$filename['extension'];
			while(file_exists($upFile)) {
				$upFile = $this->path.$this->dir.$filename['filename'].'-'.$i++.".".$filename['extension'];
			}
			
			if(!move_uploaded_file($file['tmp_name'],$upFile)) { # Erreur de copie
				return L_PLXMEDIAS_UPLOAD_ERR;
				} else { # Ok
				if(preg_match($this->img_exts, $file['name'])) {
					plxUtils::makeThumb($upFile, $this->path.'.thumbs/'.$this->dir.basename($upFile), 48, 48);
					if($resize)
					plxUtils::makeThumb($upFile, $upFile, $resize['width'], $resize['height'], 80);
					if($thumb)
					plxUtils::makeThumb($upFile, plxUtils::thumbName($upFile), $thumb['width'], $thumb['height'], 80);
				}
			}
			return L_PLXMEDIAS_UPLOAD_SUCCESSFUL;
		}		
		
	}							
