# httpPassword

Notes for AI assistants working on the Dotclear plugin httpPassword.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Help to manage .httppassword files to make a blog private.

Admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/Install.php`: install and update, default settings.
* `src/Manage.php`: admin page of the module.
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `src/Utils.php`: helpers.
* `js/`: `backend.js`.
* Behaviors used: `publicPrependV2`.

## Pitfalls

* None recorded yet: add here the traps met while working on the module.

## Testing

* Check the admin page, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
