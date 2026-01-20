# Avatars via LDAP

**MantisLdapAvatar** - A MantisBT plugin shows LDAP user avatars.

![Avatars via LDAP Screenshot](https://raw.githubusercontent.com/raspopov/MantisLdapAvatar/master/MantisLdapAvatar.png)

## Presentation

This plugin connects to the [MantisBT globally configured LDAP](https://mantisbt.org/docs/master/en-US/Admin_Guide/html-desktop/#admin.config.auth.ldap) and cache the avatars retrieved from a configured LDAP attribute to a local storage path.

When the user's LDAP last modified attribute is modified, the avatar is retrieved again from LDAP.

## Installation

- Download and extract the plugin files to your computer.
- Copy the MantisLdapAvatar catalogue into the MantisBT plugin directory.
- In MantisBT, go to the Manage -> Manage Plugins page. You will see a list of installed and currently not installed plugins.
- Click the Install button next to "Avatars via LDAP" to install a plugin.

## Configuration

The configuration is available via the MantisBT manage plugins admin page.

**LDAP avatar attribute**

The name of the LDAP attribute where the avatar is stored (as bytes).

Defaults to *jpegphoto*. Use *thumbnailphoto* for Active Directory.

**LDAP last modified attribute**

The name of the LDAP attribute where to check for a modification on the LDAP user.

Defaults to *modifytimestamp*.

**Note:** To enable avatars in MantisBT, don't forget to set `$g_show_avatar = ON;` and adjust `$g_show_avatar_threshold` options.
