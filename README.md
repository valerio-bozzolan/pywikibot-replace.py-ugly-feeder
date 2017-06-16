# Pywikibot – `replace.py` ugly feeder

This script feeds the [replace.py](https://www.mediawiki.org/wiki/Manual:Pywikibot/replace.py) script of [Pywikibot](https://www.mediawiki.org/wiki/Manual:Pywikibot) in order to orphanize redirects.

## Why I use it

* It fetches all the redirects (as `[[ASD]]`→`[[LOL]]`) from a certain category
* It helps me orphanizing them with a regex generator

## What it does

* [X] Correction of underscores as whitespaces (`[[Banana_(vegetable)]]` is seen as `[[Banana (vegetable)]]`)
* [X] Correction of starting/ending whitespaces (`[[  Banana (vegetable) ]]` is seen as `[[Banana (vegetable)]]`)
* [X] First character is maintained as default
** `[[A]]` → `[[B]]`
** `[[a]]` → `[[b]]`
* [X] Piped title is maintained by default
** `[[A|C]]` → `[[B|C]]`
** `[[a|C]]` → `[[B|C]]`
** `[[A|c]]` → `[[B|c]]`
** `[[a|c]]` → `[[B|c]]`
* [X] Omissing the piped title if can be omitted (`[[A|b]]` → `[[B|b]]` → `[[b]]`)
* [X] Maintaining of starting/ending newlines for templates
* [x] Understanding template values as possible wikilinks
** `{{Something|A}}` → `{{Something|B}}`
** `{{Something|something = a }}` → `{{Something|something = b }}` ecc.

Optionally:
* [X] Proper names can be maintained in upper case

## Cloning

    git clone --recursive https://github.com/valerio-bozzolan/pywikibot-replace.py-ugly-feeder.git

## Installation

    cp pywikibot-pre-template-example.txt pywikibot-pre-template.txt
    touch    pywikibot-ready.sh
    chmod +x pywikibot-ready.sh

## Usage

First fetch wikilinks from the it.wiki [Categoria:Redirect da orfanizzare e cancellare](https://it.wikipedia.org/wiki/Categoria:Redirect_da_orfanizzare_e_cancellare):

    php fetch-redirects-in-csv.php

This will fetch all the redirects ([example output](https://paste.debian.net/971728/)) in two files:
* `move_a-b_article.csv` ([example](https://paste.debian.net/971729/))
* `move-a-b-template.csv` ([example](https://paste.debian.net/971730/)).

After verified the generated `CSV` files, read all the lines from `move_a-b_article.csv` looking for proper names (like "Jhon Foo", and not like "Banana"). Move these proper names rows in a file called `move_a-b_article_propername.csv`.

Then do:

    cp pywikibot-pre-template.txt        pywikibot-ready.sh
    php generate-regexes-from-csv.php >> pywikibot-ready.sh

Well done! You have just generated a clean and so-loooong Pywikibot replace.py command file ([example](https://paste.debian.net/971735/)).

Now run your so-loooong Pywikibot command:

    sh pywikibot-ready.sh

Have fun!

# Disclaimer
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
