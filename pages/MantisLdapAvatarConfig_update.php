<?php
/**
 * MantisLdapAvatar - A MantisBT plugin shows LDAP user avatars
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
 * Copyright (C) 2024-2025 Nikolay Raspopov <raspopov@cherubicsoft.com>
 */

form_security_validate( 'plugin_MantisLdapAvatarConfig_update' );

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

// Retrieve user input
$t_ldap_avatar_field = trim( strtolower( gpc_get_string( 'ldap_avatar_field' ) ) );
if( $t_ldap_avatar_field ) {
	plugin_config_set( 'ldap_avatar_field', $t_ldap_avatar_field );
} else {
	plugin_config_delete( 'ldap_avatar_field' );
}

$t_ldap_last_modified_field = trim( strtolower( gpc_get_string( 'ldap_last_modified_field' ) ) );
if( $t_ldap_last_modified_field ) {
	plugin_config_set( 'ldap_last_modified_field', $t_ldap_last_modified_field );
} else {
	plugin_config_delete( 'ldap_last_modified_field' );
}

// Clean-up legacy values
plugin_config_delete( 'avatar_max_width' );
plugin_config_delete( 'avatar_max_height' );

form_security_purge( 'plugin_MantisLdapAvatarConfig_update' );

print_header_redirect( plugin_page( 'MantisLdapAvatarConfig', true ) );
