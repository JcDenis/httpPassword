1.3 - 2023.05.13
- require dotclear 2.26
- fix type hint and nullsafe warnings

1.2 - 2023.04.22
- require dotclear 2.26
- add plugin Uninstaller features
- use latest dotclear namespace
- fix static init
- fix permission
- code doc and review

1.1 - 2023.03.25
- require dotclear 2.26
- use namespace

1.0 - 2022.12.30
- update to dotclear 2.24
- change settings names
- remove debug mode
- use dcLog table for last logins

0.5.10
- fix typo
- fix for PHP 5.3 compliance

0.5.9
- fix bug in history page (PHP errors when history was empty)
- add page "debug" in plugin page in order to get infos about hosting setup (when plugins fails to authenticate users)

0.5
- deep rewrite : HTTP auth is not directly handled by apache anymore but by a dotclear behavior
- add support of multiblog installation (thanks to Stephanie "piloue" and Gabriel for being so patient and their helpfull tests)
- add support PHP running as CGI (tested with OVH hosting services)
- auto-detect crypt functions available and let user choose it
- HTTP auth is not required in order to access to blog admin
- plugin admin page with tabs
- plugin imported to dotclear lab

Known issues : This plugin does not protect non-php files (images, css, js)

0.4
- check filepermission when running
- add free.fr support

0.3
- add last connection tracker

0.2 - 2008.11.22
- _install.php added
- password crypt function setting added
- password crypt function can be choose within trim, crypt, md5, sha1
- remonte crypt function added as rcrypt rmd5 and rsha1 : crypt function
  is called over http://frederic.ple.name/....
  This feature can ensure plugin running on php restricted environment
  (ex: OVH.COM) 

0.1 : 2008-11-17
- INITIAL public release
