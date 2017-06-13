#!/bin/sh
clear
a=$1
b=$2
c=$3

path=`dirname "$0"`

echo 'Command:'
echo python pywikibot/pwb.py replace -ns:0 -ref:\"$a\" \
	-regex `php $path/escape.php "$a" "$b" "$c" ` \
	"-summary:\"[[$a]]→[[$b]]"' [[Categoria:Redirect da orfanizzare e cancellare|orfanizzare e cancellare]]"'
