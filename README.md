# Avatars via LDAP

**MantisLdapAvatar** - A MantisBT plugin that shows user avatars based on LDAP.

![Avatars via LDAP Screenshot](https://raw.githubusercontent.com/raspopov/MantisLdapAvatar/master/MantisLdapAvatar.png)

## Presentation

This plugin connects to the [MantisBT globally configured LDAP](https://www.mantisbt.org/docs/master/en-US/Admin_Guide/html-single/#admin.config.auth.ldap) and cache the avatars retrieved from a configured LDAP attribute to a local storage path.

When the user's LDAP last modified attribute is modified, the avatar is retrieved again from LDAP.

## Configuration

The configuration is available via the MantisBT manage plugins admin page.

**LDAP avatar attribute**

The name of the LDAP attribute where the avatar is stored (as bytes).
Defaults to *jpegphoto*. Use *thumbnailphoto* for Active Directory.

**LDAP last modified attribute**

The name of the LDAP attribute where to check for a modification on the LDAP user.
Defaults to *modifytimestamp*.
