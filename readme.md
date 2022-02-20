# Bundled commands

Inside composer several extra tools have been added:

Code formatting:
- `composer run phpcbf`                  Run the phpcbf, an autoformatter.
- `composer run phpcs`                   Run phpcs, Checks style and syntax agianst theh WordPress coding stadard.
- `composer run lint`                    Run php linter, Checks syntax.
- `composer run phpstan`                 Run phpstan, Checks styntax, docblock, non existing functions/classes.
- `composer run ci`                      Run all the above syntax checkers at once.

Creating plugin zip:
- `composer run createzip`               Will create a zip named 'plugin-slug.zip' in the plugin folder.
- `composer run createzip-in-downloads`  Will create a zip named 'plugin-slug-0.1.0.zip' in the plugin folder.
- `composer run createzip-with-version`  Will create a zip named 'plugin-slug-0.1.0.zip' in the Downloads folder.
