<?php
/**
 * MantisLdapAvatar - A MantisBT plugin that shows user avatars based on LDAP
 *
 * MantisLdapAvatar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisLdapAvatar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisLdapAvatar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2017 Romain Cabassot <romain.cabassot@gmail.com>
 * Copyright (C) 2024 Nikolay Raspopov <raspopov@cherubicsoft.com>
 */

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );

if( !config_get( 'show_avatar' ) ) {
	?>
	<div class="alert alert-warning">
		<ul>
			<li><?php echo plugin_lang_get( 'avatar_disabled' ); ?></li>
		</ul>
	</div>
	<?php
}

$t_user_name = user_get_name( auth_get_current_user_id() );
if( !ldap_get_field_from_username( $t_user_name, plugin_config_get( 'ldap_last_modified_field' ) ) ) {
	?>
	<div class="alert alert-warning">
		<ul>
			<li><?php echo plugin_lang_get( 'last_modified_error' ); ?></li>
		</ul>
	</div>
	<?php
}
if( !ldap_get_field_from_username( $t_user_name, plugin_config_get( 'ldap_avatar_field' ) ) ) {
	?>
	<div class="alert alert-warning">
		<ul>
			<li><?php echo plugin_lang_get( 'ldap_avatar_field_error' ); ?></li>
		</ul>
	</div>
	<?php
}

$t_storage_path = plugin_file_path();
if( !file_exists( $t_storage_path )
	|| !is_writable( $t_storage_path )
	|| !is_dir( $t_storage_path ) ) {
	?>
	<div class="alert alert-warning">
		<ul>
			<li><?php echo plugin_lang_get( 'storage_path_error' ), $t_storage_path; ?></li>
		</ul>
	</div>
	<?php
}

print_manage_menu( 'manage_plugin_page.php' );
?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
		<form action="<?php echo plugin_page( 'MantisLdapAvatarConfig_update' ); ?>" method="post">
			<?php echo form_security_field( 'plugin_MantisLdapAvatarConfig_update' ); ?>

			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter"><?php print_icon( 'fa-sliders', 'ace-icon' ); ?> <?php echo plugin_lang_get( 'title'); ?></h4>
				</div>
				<div class="widget-body">
					<div class="widget-main no-padding">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-striped">
								<tbody>
									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'ldap_avatar_field_title'); ?><br/>
											<span class="small"><?php echo plugin_lang_get( 'ldap_avatar_field_details'); ?></span>
										</th>
										<td>
											<input type="text" class="input-sm" name="ldap_avatar_field" value="<?php echo plugin_config_get('ldap_avatar_field'); ?>">
										</td>
									</tr>
									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'ldap_last_modified_field_title' ); ?><br/>
											<span class="small"><?php echo plugin_lang_get( 'ldap_last_modified_field_details' ); ?></span>
										</th>
										<td>
											<input type="text" class="input-sm" name="ldap_last_modified_field" value="<?php echo plugin_config_get( 'ldap_last_modified_field' ); ?>">
										</td>
									</tr>
									<tr>
										<th class="category width-40"><?php echo plugin_lang_get( 'cache_size_title' ); ?><br/>
											<span class="small"><?php echo plugin_lang_get( 'cache_size_details' ); ?></span>
										</th>
										<td>
											<?php echo number_format( MantisLdapAvatarPlugin::size() / 1024, 1 ) . ' ' . lang_get( 'kib' ); ?>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="widget-toolbox padding-8 clearfix">
								<input class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get( 'save' ); ?>" type="submit">
								<a class="btn btn-warning btn-sm btn-white btn-round" href="<?php echo plugin_page( 'MantisLdapAvatarConfig_purge' ) . form_security_param( 'plugin_MantisLdapAvatarConfig_purge' ) ?>"><?php echo plugin_lang_get( 'purge' ) ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
layout_page_end();
