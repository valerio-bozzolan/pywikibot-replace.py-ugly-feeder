#!/usr/bin/php
<?php
# Copyright (C) 2017 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

require 'autoload.php';

function a_b($file) {
	if( ! file_exists($file) ) {
		return [];
	}

	$rows = file_get_contents($file);

	$a_b = [];

	$rows = explode("\n", $rows);
	array_walk($rows, 'trim');

	foreach($rows as $row) {
		if( empty($row) ) {
			continue;
		}

		$a_b_candidate = explode(';', $row);
		array_walk($a_b_candidate, 'trim');

		if( count($a_b_candidate) === 2 ) {
			$a_b[] = $a_b_candidate;
		} else {
			var_dump($a_b_candidate);
			die("Not in A;B format.");
		}
	}

	return $a_b;
}

function print_row($what) {
	echo $what . " \\\n";
}

function generic_a_b($a_b, $propername = false) {
	$a = ucfirst( $a_b[0] );
	$b = ucfirst( $a_b[1] );
	print_row( WLinkReplacer::factory($a, $b, $propername)->getPywikibotCouples() );
}

$a_b_article            = a_b('move_a-b_article.csv');
$a_b_article_propername = a_b('move_a-b_article_propername.csv');
$a_b_template           = a_b('move_a-b_template.csv');
$a_b_simple             = a_b('move_a-b_simple.csv');

// Query

foreach($a_b_template as $a_b) {
	print_row( sprintf(
		'-transcludes:"%s"',
		$a_b[0]
	) );
}

foreach($a_b_article as $a_b) {
	print_row( sprintf(
		'-ref:"%s"',
		$a_b[0]
	) );
}

foreach($a_b_article_propername as $a_b) {
	print_row( sprintf(
		'-ref:"%s"',
		$a_b[0]
	) );
}

// Replacers

foreach($a_b_template as $a_b) {
	generic_a_b($a_b);
}

foreach($a_b_article as $a_b) {
	generic_a_b($a_b);
}

foreach($a_b_article_propername as $a_b) {
	generic_a_b($a_b, true);
}

foreach($a_b_simple as $a_b) {
	print_row( sprintf(
		'"%s" "%s"',
		$a_b[0],
		$a_b[1]
	) );
}
