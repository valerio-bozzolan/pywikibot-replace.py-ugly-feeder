# Pywikibot – `replace.py` ugly feeder

This script feeds the [replace.py](https://www.mediawiki.org/wiki/Manual:Pywikibot/replace.py) script of [Pywikibot](https://www.mediawiki.org/wiki/Manual:Pywikibot) in order to orphanize redirects.

## Cloning

    git clone --recursive https://github.com/valerio-bozzolan/pywikibot-replace.py-ugly-feeder.git

## Installation

    cp pywikibot-pre-template-example.txt pywikibot-pre-template.txt

## Usage

First fetch wikilinks from the [Categoria:Redirect da orfanizzare e cancellare](https://it.wikipedia.org/wiki/Categoria:Redirect_da_orfanizzare_e_cancellare):

    php fetch-redirects-in-csv.php

After verified the generated `CSV` files then:

    cp pywikibot-pre-template.txt      > pywikibot-ready.sh
    php generate-regexes-from-csv.php >> pywikibot-ready.sh

Now simply:

    sh pywikibot-ready.sh

Have fun!

# License
