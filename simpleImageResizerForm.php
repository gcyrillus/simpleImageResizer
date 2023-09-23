<?php if(!defined('PLX_ROOT')) exit; ?>
<form method="post">
	<!-- Rename File Dialog -->
	<div id="dlgRenameFile" class="dialog">
		<div class="dialog-content">
			<?php echo L_MEDIAS_NEW_NAME ?>&nbsp;:&nbsp;
			<input id="id_newname" type="text" name="newname" value="" maxlength="50" size="15" />
			<input id="id_oldname" type="hidden" name="oldname" />
			<?php echo plxToken::getTokenPostMethod() ?>
			<input type="submit" name="btn_renamefile" value="<?php echo L_MEDIAS_RENAME ?>" />
			<span class="dialog-close">&times;</span>
		</div>
	</div>
</form>

<form method="post" id="form_medias">

	<!-- New Folder Dialog -->
	<div id="dlgNewFolder" class="dialog">
		<div class="dialog-content">
			<span class="dialog-close">&times;</span>
			<?php echo L_MEDIAS_NEW_FOLDER ?>&nbsp;:&nbsp;
			<input id="id_newfolder" type="text" name="newfolder" value="" maxlength="50" size="15" />
			<input type="submit" name="btn_newfolder" value="<?php echo L_MEDIAS_CREATE_FOLDER ?>" />
		</div>
	</div>

	<div class="inline-form" id="files_manager">

		<div class="inline-form action-bar">
			<h2><?php echo L_MEDIAS_TITLE ?></h2>
			<p>
				<?php
				echo L_MEDIAS_DIRECTORY.' : <a href="javascript:void(0)" onclick="document.forms[1].folder.value=\'.\';document.forms[1].submit();return true;" title="'.L_PLXMEDIAS_ROOT.'">('.L_PLXMEDIAS_ROOT.')</a> / ';
				if($curFolders) {
					$path='';
					foreach($curFolders as $id => $folder) {
						if(!empty($folder) AND $id>1) {
							$path .= $folder.'/';
							echo '<a href="javascript:void(0)" onclick="document.forms[1].folder.value=\''.$path.'\';document.forms[1].submit();return true;" title="'.$folder.'">'.$folder.'</a> / ';
						}
					}
				}
				?>
			</p>
			<?php plxUtils::printSelect('selection', $selectionList, '', false, 'no-margin', 'id_selection') ?>
			<input type="submit" name="btn_ok" value="<?php echo L_OK ?>" onclick="return confirmAction(this.form, 'id_selection', 'delete', 'idFile[]', '<?php echo L_CONFIRM_DELETE ?>')" />
			<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
			<input type="submit" onclick="toggle_divs();return false" value="<?php echo L_MEDIAS_ADD_FILE ?>" />
			<button onclick="dialogBox('dlgNewFolder');return false;" id="btnNewFolder"><?php echo L_MEDIAS_NEW_FOLDER ?></button>
			<?php if(!empty($_SESSION['folder'])) { ?>
			<span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span><input type="submit" name="btn_delete" class="red" value="<?php echo L_DELETE_FOLDER ?>" onclick="return confirm('<?php printf(L_MEDIAS_DELETE_FOLDER_CONFIRM, $curFolder) ?>')" />
			<?php } ?>
			<input type="hidden" name="sort" value="" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>

		<div style="float:left">
			<?php echo L_MEDIAS_FOLDER ?>&nbsp;:&nbsp;<?php $plxMedias->contentFolder() ?>
			<input type="submit" name="btn_changefolder" value="<?php echo L_OK ?>" /><span class="sml-hide med-show">&nbsp;&nbsp;&nbsp;</span>
		</div>

		<div style="float:right">
			<input type="text" id="medias-search" onkeyup="plugFilter()" placeholder="<?php echo L_SEARCH ?>..." title="<?php echo L_SEARCH ?>" />
		</div>

		<div style="clear:both" class="scrollable-table">
			<table id="medias-table" class="full-width">
				<thead>
				<tr>
					<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idFile[]')" /></th>
					<th>&nbsp;</th>
					<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[1].sort.value='<?php echo $sort_title ?>';document.forms[1].submit();return true;"><?php echo L_MEDIAS_FILENAME ?></a></th>
					<th><?php echo L_MEDIAS_EXTENSION ?></th>
					<th><?php echo L_MEDIAS_FILESIZE ?></th>
					<th><?php echo L_MEDIAS_DIMENSIONS ?></th>
					<th><a href="javascript:void(0)" class="hcolumn" onclick="document.forms[1].sort.value='<?php echo $sort_date ?>';document.forms[1].submit();return true;"><?php echo L_MEDIAS_DATE ?></a></th>
				</tr>
				</thead>
				<tbody id="medias-table-tbody">
				<?php
				# Initialisation de l'ordre
				$num = 0;
				# Si on a des fichiers
				if($plxMedias->aFiles) {
					foreach($plxMedias->aFiles as $v) { # Pour chaque fichier
						$isImage = in_array(strtolower($v['extension']), $plxMedias->img_supported);
						$title = pathinfo($v['name'], PATHINFO_FILENAME);
						echo '<tr>';
						echo '<td><input type="checkbox" name="idFile[]" value="'.$v['name'].'" /></td>';
						echo '<td class="icon">';
							if(is_file($v['path']) AND $isImage) {
								echo '<a class="overlay" title="'.$title.'" href="'.$v['path'].'"><img alt="'.$title.'" src="'.$v['.thumb'].'" class="thumb" /></a>';
							}
							else
								echo '<img alt="" src="'.$v['.thumb'].'" class="thumb" />';
						echo '</td>';
						echo '<td>';
							echo '<a class="imglink" onclick="'."this.target='_blank'".'" title="'.$title.'" href="'.$v['path'].'">'.$title.$v['extension'].'</a>';
							echo '<div data-copy="'.str_replace(PLX_ROOT, '', $v['path']).'" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#128203;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							echo '<div data-rename="'.$v['path'].'" title="'.L_RENAME_FILE.'" class="ico">&#9998;</div>';
							echo '<br />';
							$href = plxUtils::thumbName($v['path']);
							if($isImage AND is_file($href)) {
								echo L_MEDIAS_THUMB.' : '.'<a onclick="'."this.target='_blank'".'" title="'.$title.'" href="'.$href.'">'.plxUtils::strCheck(basename($href)).'</a>';
								echo '<div data-copy="'.str_replace(PLX_ROOT, '', $href).'" title="'.L_MEDIAS_LINK_COPYCLP.'" class="ico">&#128203;<div>'.L_MEDIAS_LINK_COPYCLP_DONE.'</div></div>';
							}
						echo '</td>';
						echo '<td>'.strtoupper($v['extension']).'</td>';
						echo '<td>';
							echo plxUtils::formatFilesize($v['filesize']);
							if($isImage AND is_file($href)) {
								echo '<br />'.plxUtils::formatFilesize($v['thumb']['filesize']);
							}
						echo '</td>';
						$dimensions = '&nbsp;';
						if($isImage AND (isset($v['infos']) AND isset($v['infos'][0]) AND isset($v['infos'][1]))) {
							$dimensions = $v['infos'][0].' x '.$v['infos'][1];
						}
						if($isImage AND is_file($href)) {
							$dimensions .= '<br />'.$v['thumb']['infos'][0].' x '.$v['thumb']['infos'][1];
						}
						echo '<td>'.$dimensions.'</td>';
						echo '<td>'.plxDate::formatDate(plxDate::timestamp2Date($v['date'])).'</td>';
						echo '</tr>';
					}
				}
				else echo '<tr><td colspan="7" class="center">'.L_MEDIAS_NO_FILE.'</td></tr>';
				?>
				</tbody>
			</table>
		</div>
	</div>
</form>

<form action="medias.php" method="post" id="form_uploader" class="form_uploader" enctype="multipart/form-data">

	<div id="files_uploader" style="display:none">

		<div class="inline-form action-bar">
			<h2 class="h4"><?php echo L_MEDIAS_TITLE ?></h2>
			<p>
				<?php
				echo L_MEDIAS_DIRECTORY.' : ('.L_PLXMEDIAS_ROOT.') / ';
				if($curFolders) {
					$path='';
					foreach($curFolders as $id => $folder) {
						if(!empty($folder) AND $id>1) {
							$path .= $folder.'/';
							echo $folder.' / ';
						}
					}
				}
				?>
			</p>
			<input type="submit" name="btn_upload" id="btn_upload" value="<?php echo L_MEDIAS_SUBMIT_FILE ?>" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</div>

		<p><a class="back" href="javascript:void(0)" onclick="toggle_divs();return false"><?php echo L_MEDIAS_BACK ?></a></p>

		<p>
			<?php echo L_MEDIAS_MAX_UPLOAD_NBFILE ?> : <?php echo ini_get('max_file_uploads') ?>
 		</p>
		<p>
			<?php echo L_MEDIAS_MAX_UPLOAD_FILE ?> : <?php echo $plxMedias->maxUpload['display'] ?>
			<?php if($plxMedias->maxPost['value'] > 0) echo " / ".L_MEDIAS_MAX_POST_SIZE." : ".$plxMedias->maxPost['display']; ?>
		</p>

		<div>
			<input id="selector_0" type="file" multiple="multiple" name="selector_0[]" accept="image/*,audio/*,video/*,.pdf,.zip" />
			<div class="files_list" id="files_list" style="margin: 1rem 0 1rem 0;"></div>
		</div>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminMediasUpload')) # Hook Plugins ?>
	</div>

</form>

<div class="modal">
	<input id="modal" type="checkbox" name="modal" tabindex="1">
	<div id="modal__overlay" class="modal__overlay">
		<div id="modal__box" class="modal__box">
			<img id="zoombox-img" />
			<label for="modal">&#10006;</label>
		</div>
	</div>
</div>

<input id="clipboard" type="text" value="" style="display: none;" />
<script type="text/javascript" src="<?php echo PLX_CORE ?>lib/medias.js"></script>
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminMediasFoot'));
# On inclut le footer
include PLX_ROOT.'core/admin/foot.php';