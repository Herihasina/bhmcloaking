<div class="overlay-h">
	<img src="<?php echo get_admin_url(); ?>/images/loading.gif" alt="loading">
</div>
<div class="wrap">
	<div class="left-part">
		
	<form method="post" action="options.php">
		<?php 
			settings_errors();
			settings_fields('hcloak_options'); 
			do_settings_sections( 'hcloak_options' );
		?>
		<h2 class="cloak-title">
			BHMCloaking : Hackez Google et votre Taux de Conversion
			<p class="not-bold">
			Envie de générer des profits grâce au Black Hat SEO ? Rendez-vous sur <a href="http://blackhat.money/" title="Black Hat SEO" target="_blank">Black Hat Money</a> !
		</p>
		</h2>
		
		<div class="form-content">
			<div class="checkbox">
				<input type="radio" class="cbox cloak_choice" name="hcloak" id="hcloak_none" value="cloak_none" <?php checked( 'cloak_none' == get_option("hcloak") ); ?> />
			</div>
			<div class="textbox">
				<label for="hcloak_none">Cloaking non activé</label>
			</div>
		</div>
		
		<div class="form-content">
			<div class="checkbox">
				<input type="radio" class="cbox alpha cloak_choice" name="hcloak" id="hcloak_301" value="cloak_301" <?php checked( 'cloak_301' == get_option("hcloak") ); ?> />
			</div>
			<div class="textbox">
				<label for="hcloak_301">Cloaking 301</label>
				<p>
					<input class="p-input" type="url" name="hcloak_301_general" value="<?php echo get_option('hcloak_301_general') ?>" placeholder="URL de redirection"/>
				</p>
			</div>
		</div>
		
		<div class="form-content">
			<div class="checkbox">
				<input type="radio" class="cbox cloak_choice" name="hcloak" id="hcloak_content" value="cloak_content" <?php checked( 'cloak_content' == get_option("hcloak") ); ?> />
			</div>
			<div class="textbox">
				<label for="hcloak_content">Cloaker le contenu</label>
				<div id="hcloak_universal_content">
					<p>Entrer le nouveau contenu des articles.</p>
					<?php
						$settings = array(
							"textarea_name" => "hcloak_content_textarea",
							"textarea_rows" => 5,
							"textarea_height" => 100);
						$content = get_option('hcloak_content_textarea');
						wp_editor($content, "universal_content", $settings);
					?>
				</div>
			</div>
		</div>
		<div class="form-content">
				<div class="checkbox">
					<input type="radio" class="cbox parent cloak_choice" name="hcloak" id="hcloak_category" value="cloak_category" <?php checked( 'cloak_category' == get_option("hcloak")); ?> />
				</div>
				<div class="textbox">
					<label for="hcloak_category">Cloaker par catégorie</label>
					<div id="hcloak_categories">
						<div id="list-cetegories">
							<?php
								$cats = get_categories(['hide_empty' => 0]);
							?>
								<p>Cocher la ou les catégories à cloaker.</p>
								<p class="info">(Seules les catégories cochées seront prises en compte)</p>
								<div class="catlist">
								<?php foreach ($cats as $key => $cat) { ?>
									<table>
										<tr>
											<td><input type="checkbox" class="cbox-cat" name="hcloak_<?php echo $key ?>" value="1" <?php checked( get_option('hcloak_'.$key), 1 ) ?> /></td>
											<td class="col"><?php echo $cat->name; ?></td>
											<td><input class="p-input" type="url" name="hcloak_url_<?php echo $key ?>" value="<?php echo get_option('hcloak_url_'.$key) ?>" placeholder="URL de redirection"/></td>					
										</tr>
									</table>
								<?php } ?>
								</div>
						</div>
					</div>
				</div>
			<div class="block_all">
				<div class="checkbox">
					<input type="checkbox" name="block_all" value="block_all" id="block_all" <?php checked( 'block_all' == get_option("block_all")); ?> >
				</div>
				<div class="textbox">
					<label for="block_all" class="block_all_label">
						Bloquer toutes les autres pages afin que l'utilisateur ne puisse pas naviguer sur le site.
					</label>
				</div>
			</div>
		</div>
		<div class="form-content">
			<div class="checkbox">
				<input class="cbox cloak_choice alpha" name="hcloak" value="hcloak_referer" type="radio" id="hcloak_referer" <?php checked( 'hcloak_referer' == get_option("hcloak")); ?>>
			</div>
			<div class="textbox">
				<label for="hcloak_referer">Cloaking sur referer</label>
					<p>
						<input class="p-input" name="hcloak_referer_domain" value="<?php echo get_option('hcloak_referer_domain') ?>" placeholder="Domaine referer (ex: wikipedia.org)">
					</p>
					<p>
						<input class="p-input" name="hcloak_referer_redirect" value="<?php echo get_option('hcloak_referer_redirect') ?>" type="url" placeholder="URL de redirection">
					</p>
			</div>
		</div>
		<hr>
		<div class="form-content">
			<div class="checkbox">
				<input type="checkbox" class="cbox" name="hcloak_visitor_google" id="hcloak_visitor_google" value="hcloak_visitor_google" <?php checked( 'hcloak_visitor_google' == get_option("hcloak_visitor_google") ); ?> />
			</div>
			<div class="textbox">
				<label for="hcloak_visitor_google">Cloaker avec les shortcodes <span class="red">[cloakGoogle][/cloakGoogle]</span> et <span class="red">[cloakVisitor][/cloakVisitor]</span></label>
			</div>
		</div>
		<div class="form-content">
			<div class="checkbox">
				<input type="checkbox" class="cbox" name="hcloak_noarchive" id="hcloak_noarchive" value="hcloak_noarchive" <?php checked( 'hcloak_noarchive' == get_option("hcloak_noarchive") ); ?> />
			</div>
			<div class="textbox">
				<label for="hcloak_noarchive">Activer la meta noarchive</label>
			</div>
		</div>
		<?php submit_button(); ?>
	</form>
	</div>
	<div class="right-part">
		<img src="<?php echo plugin_dir_url(__FILE__ ); ?>ban-logo-dark.jpg" alt="logo Black Hat Money">
	</div>
	
</div>
