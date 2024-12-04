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
 * Copyright (C) 2024 Nikolay Raspopov <raspopov@cherubicsoft.com>
 */

form_security_validate( 'plugin_MantisLdapAvatarConfig_purge' );

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

helper_ensure_confirmed( plugin_lang_get( 'ensure_purge' ), plugin_lang_get( 'purge' ) );

MantisLdapAvatarPlugin::purge();

form_security_purge( 'plugin_MantisLdapAvatarConfig_purge' );
print_header_redirect( plugin_page( 'MantisLdapAvatarConfig', true ) );
