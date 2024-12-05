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

class MantisLdapAvatarPlugin extends MantisPlugin {

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get ( 'title' );
		$this->description = plugin_lang_get ( 'description' );
		$this->page = 'MantisLdapAvatarConfig';

		$this->version = '2.0.0';
		$this->requires = array (
			'MantisCore' => '2.28'
		);

		$this->author = 'Nikolay Raspopov';
		$this->contact = 'raspopov@cherubicsoft.com';
		$this->url = 'https://github.com/raspopov/MantisLdapAvatar';
	}

	/**
	 * Default plugin configuration
	 * @return array
	 */
	function config() {
		return array (
			'ldap_avatar_field' => 'jpegphoto',
			'ldap_last_modified_field' => 'modifytimestamp'
		);
	}

	/**
	 * Register event hooks for plugin
	 * @return array
	 */
	function hooks() {
		return array (
			'EVENT_USER_AVATAR' => 'user_get_avatar',
			'EVENT_LDAP_CACHE_ATTRS' => 'cache_attrs'
		);
	}

	/**
	 * Returns LDAP attribute names for caching
	 * @param string $p_event The name for the event
	 * @param array  $p_user  The username
	 * @return array
	 */
	function cache_attrs( $p_event, $p_user ) {
		return array(
			plugin_config_get( 'ldap_last_modified_field' ),
			plugin_config_get( 'ldap_avatar_field' )
		);
	}

	/**
	 * Plugin Installation
	 * @return boolean
	 */
	function install() {
		if( !extension_loaded( 'ldap' ) ) {
			error_parameters( plugin_get_current() . '. LDAP extension missing in PHP' );
			trigger_error( ERROR_PLUGIN_INSTALL_FAILED, ERROR );
			return false;
		}

		if( !extension_loaded( 'gd' ) ) {
			error_parameters( plugin_get_current() . '. GD extension missing in PHP' );
			trigger_error( ERROR_PLUGIN_INSTALL_FAILED, ERROR );
			return false;
		}

		$t_avatar_storage_path = plugin_file_path();
		if( !file_exists( $t_avatar_storage_path ) || !is_writable( $t_avatar_storage_path ) || !is_dir( $t_avatar_storage_path ) ) {
			error_parameters( plugin_get_current() . '. Invalid avatar storage path: ' . $t_avatar_storage_path . ' must be a writable directory' );
			trigger_error( ERROR_PLUGIN_INSTALL_FAILED, ERROR );
			return false;
		}

		return true;
	}

	/**
	 * Return the user avatar
	 * @param string  $p_event   The name for the event
	 * @param integer $p_user_id A valid user identifier
	 * @param integer $p_size    The required number of pixel in the image to retrieve the link for
	 * @return object An instance of class Avatar
	 */
	function user_get_avatar( $p_event, $p_user_id, $p_size = 80 ) {
		$t_avatar = new Avatar ();
		$t_ldap_last_modified_field = plugin_config_get( 'ldap_last_modified_field' );
		$last_modified = ldap_get_field_from_username( user_get_name( $p_user_id ), $t_ldap_last_modified_field );
		if( $last_modified ) {
			// Check if the avatar is already in cache
			$avatar_url = $this->get_user_avatar_from_cache( $p_user_id, $last_modified, $p_size );

			$t_avatar->image = ( $avatar_url ? $avatar_url : $this->download_user_avatar( $p_user_id, $last_modified, $p_size ) );
		}

		return $t_avatar;
	}

	/**
	 * Retrieves the user avatar from LDAP, resize it if needed then store it on disk cache
	 * @param integer $p_user_id       A valid user identifier
	 * @param string  $p_last_modified A string that tells when the user LDAP entry was last modified
	 * @param integer $p_size          The required number of pixel in the image
	 * @return string|null Avatar URL or null
	 */
	function download_user_avatar( $p_user_id, $p_last_modified, $p_size ) {
		$t_image = null;
		$t_avatar_image = ldap_get_field_from_username( user_get_name( $p_user_id ), plugin_config_get( 'ldap_avatar_field' ) );
		if( $t_avatar_image ) {

			$t_avatar_path = $this->get_avatar_path( $p_user_id, $p_last_modified, $p_size );

			list( $t_srt_width, $t_srt_height ) = getimagesizefromstring( $t_avatar_image );
			$t_ratio = $t_srt_width / $t_srt_height;
			if( $t_ratio > 1 ) {
				$t_dst_width = $p_size;
				$t_dst_height = $t_dst_width / $t_ratio;
			} else {
				$t_dst_height = $p_size;
				$t_dst_width = $t_dst_height * $t_ratio;
			}

			$t_src_img = imagecreatefromstring( $t_avatar_image );
			$t_dst_img = imagecreatetruecolor( $t_dst_width, $t_dst_height );
			imagecopyresampled( $t_dst_img, $t_src_img, 0, 0, 0, 0, $t_dst_width, $t_dst_height, $t_srt_width, $t_srt_height );
			imagejpeg( $t_dst_img, $t_avatar_path, 75 );
			imagedestroy( $t_dst_img );
			imagedestroy( $t_src_img );

			$this->delete_old_avatar( $p_user_id, $p_last_modified, $p_size );
			$t_image = plugin_file( basename( $t_avatar_path ) );
		}

		return $t_image;
	}

	/**
	 * Delete old avatar files
	 * @param integer $p_user_id       A valid user identifier
	 * @param string  $p_last_modified A string that tells when the user LDAP entry was last modified
	 * @param integer $p_size          The required number of pixel in the image
	 * @return void
	 */
	function delete_old_avatar( $p_user_id, $p_last_modified, $p_size ) {
		$t_avatar_path = $this->get_avatar_path( $p_user_id, $p_last_modified, $p_size );
		$t_search = glob( $this->get_avatar_path_base( $p_user_id, $p_size ) . '*' );
		foreach( $t_search as $t_filename ) {
			if( $t_filename != $t_avatar_path && !@unlink( $t_filename ) ) {
				error_parameters( 'Unable to delete file: ' . $t_filename, plugin_get_current() );
				trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
			}
		}
	}

	/**
	 * Check if the user avatar can be retrieved from cache
	 * @param integer $p_user_id       A valid user identifier
	 * @param string  $p_last_modified A string that tells when the user LDAP entry was last modified
	 * @param integer $p_size          The required number of pixel in the image
	 * @return string|false The user avatar file URL or false
	 */
	function get_user_avatar_from_cache( $p_user_id, $p_last_modified, $p_size ) {
		$t_avatar_path = $this->get_avatar_path( $p_user_id, $p_last_modified, $p_size );
		return ( file_exists( $t_avatar_path ) ? plugin_file( basename( $t_avatar_path ) ) : false );
	}

	/**
	 * Constructs the user avatar file absolute path
	 * @param integer $p_user_id       A valid user identifier
	 * @param string  $p_last_modified A string that tells when the user LDAP entry was last modified
	 * @param integer $p_size          The required number of pixel in the image
	 * @return string The user avatar file absolute path
	 */
	function get_avatar_path( $p_user_id, $p_last_modified, $p_size ) {
		return $this->get_avatar_path_base( $p_user_id, $p_size ) . preg_replace( '/[^a-zA-Z0-9]/', '', $p_last_modified ) . '.jpg';
	}

	/**
	 * Constructs the user avatar file absolute path base
	 * @param integer $p_user_id       A valid user identifier
	 * @param integer $p_size          The required number of pixel in the image
	 * @return string The user avatar file absolute path base
	 */
	 function get_avatar_path_base( $p_user_id, $p_size ) {
		return plugin_file_path() . $p_user_id . '-' . $p_size . '-';
	}

	/**
	 * Get all avatar files size
	 * @return integer Total size of all files (bytes).
	 */
	 public static function size() {
		$t_search = glob( plugin_file_path() . '*.jpg' );
		$t_size = 0;
		foreach( $t_search as $t_filename ) {
			$t_size += @filesize( $t_filename );
		}

		return $t_size;
	}

	/**
	 * Purge all avatar files
	 * @return void
	 */
	public static function purge() {
		$t_search = glob( plugin_file_path() . '*.jpg' );
		foreach( $t_search as $t_filename ) {
			if( !@unlink( $t_filename ) ) {
				error_parameters( 'Unable to delete file: ' . $t_filename, plugin_get_current() );
				trigger_error( ERROR_PLUGIN_GENERIC, ERROR );
			}
		}
	}
}
