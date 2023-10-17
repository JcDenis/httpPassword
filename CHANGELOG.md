httpPassword 1.5 - 2023.10.17
===========================================================
* Require Dotclear 2.28
* Require PHP 8.1
* Upgrade to Dotclear 2.28

httpPassword 1.4 - 2023.08.12
===========================================================
* Require Dotclear 2.27
* Require PHP 7.4+
* Upgrade to Dotclear 2.27
* Remove custom permission, only admin can handle httpPassword
* Move third party repository
* Use Dotclear style for CHANGELOG

httpPassword 1.3 - 2023.05.13
===========================================================
* require dotclear 2.26
* fix type hint and nullsafe warnings

httpPassword 1.2 - 2023.04.22
===========================================================
* require dotclear 2.26
* add plugin Uninstaller features
* use latest dotclear namespace
* fix static init
* fix permission
* code doc and review

httpPassword 1.1 - 2023.03.25
===========================================================
* require dotclear 2.26
* use namespace

httpPassword 1.0 - 2022.12.30
===========================================================
* update to dotclear 2.24
* change settings names
* remove debug mode
* use dcLog table for last logins

httpPassword 0.5.10
===========================================================
* fix typo
* fix for PHP 5.3 compliance

httpPassword 0.5.9
===========================================================
* fix bug in history page (PHP errors when history was empty)
* add page "debug" in plugin page in order to get infos about hosting setup (when plugins fails to authenticate users)

httpPassword 0.5
===========================================================
* deep rewrite : HTTP auth is not directly handled by apache anymore but by a dotclear behavior
* add support of multiblog installation (thanks to Stephanie "piloue" and Gabriel for being so patient and their helpfull tests)
* add support PHP running as CGI (tested with OVH hosting services)
* auto-detect crypt functions available and let user choose it
* HTTP auth is not required in order to access to blog admin
* plugin admin page with tabs
* plugin imported to dotclear lab

Known issues : This plugin does not protect non-php files (images, css, js)

httpPassword 0.4
===========================================================
* check filepermission when running
* add free.fr support

httpPassword 0.3
===========================================================
* add last connection tracker

httpPassword 0.2 - 2008.11.22
===========================================================
* _install.php added
* password crypt function setting added
* password crypt function can be choose within trim, crypt, md5, sha1
* remonte crypt function added as rcrypt rmd5 and rsha1 : crypt function
  is called over http://frederic.ple.name/....
  This feature can ensure plugin running on php restricted environment
  (ex: OVH.COM) 

httpPassword 0.1 : 2008-11-17
===========================================================
* INITIAL public release
