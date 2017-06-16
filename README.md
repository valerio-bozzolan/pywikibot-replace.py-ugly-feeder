# Pywikibot – `replace.py` ugly feeder

This script feeds the [replace.py](https://www.mediawiki.org/wiki/Manual:Pywikibot/replace.py) script of [Pywikibot](https://www.mediawiki.org/wiki/Manual:Pywikibot) in order to orphanize redirects.

## Cloning

    git clone --recursive https://github.com/valerio-bozzolan/pywikibot-replace.py-ugly-feeder.git

## Installation

    cp pywikibot-pre-template-example.txt pywikibot-pre-template.txt
    touch    pywikibot-ready.sh
    chmod +x pywikibot-ready.sh

## Usage

First fetch wikilinks from the [Categoria:Redirect da orfanizzare e cancellare](https://it.wikipedia.org/wiki/Categoria:Redirect_da_orfanizzare_e_cancellare):

    php fetch-redirects-in-csv.php

After verified the generated `CSV` files then:

    cp pywikibot-pre-template.txt        pywikibot-ready.sh
    php generate-regexes-from-csv.php >> pywikibot-ready.sh

Now simply:

    sh pywikibot-ready.sh

Have fun!

# License
Copyright (C) 2017 Valerio Bozzolan & contributors.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
