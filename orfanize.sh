#!/bin/sh
clear
a=$1
b=$2

echo 'Command:'
echo python pywikibot/pwb.py replace -ns:0 -ref:\'$a\' -regex `./escape.php "$a" "$b"`

echo

echo 'Message:'
echo "[[$a]]→[[$b]] [[Categoria:Redirect da orfanizzare e cancellare|orfanizzare e cancellare]]"
