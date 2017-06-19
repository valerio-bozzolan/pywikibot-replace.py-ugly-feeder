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

defined('API_URL')
	or define('API_URL', 'https://it.wikipedia.org/w/api.php');

defined('CAT_TITLE')
	or define('CAT_TITLE', 'Categoria:Redirect da orfanizzare e cancellare');

$api = new APIRequest(API_URL, [
	'action'  => 'query',
	'list'    => 'categorymembers',
	'cmtitle' => CAT_TITLE,
	'titles'  => CAT_TITLE
] );

$ns_from_to = [];

while( $api->hasNext() ) {
	$next = $api->getNext();

	foreach( $next->query->categorymembers as $categorymember ) {
		$ns     = $categorymember->ns;
		$pageid = $categorymember->pageid;
		$title  = $categorymember->title;

		switch($ns) {
			case 0:
			case 10:
				break;
		}

		echo "[RETRIEVED] \t $title\n";

		$redirects = APIRequest::factory(API_URL, [
			'action'    => 'query',
			'pageids'   => $pageid,
			'redirects' => 1
		] )->fetch();

		if( ! $redirects || ! $redirects->query->pages ) {
			var_dump($categorymember);
			die("No redirect?");
		}

		foreach($redirects->query->pages as $redirect) {
			$redirect_title = $redirect->title;
			$redirect_ns    = $redirect->ns;

			if($redirect_ns !== $ns) {
				var_dump($redirect);
				die("No same namespace?");
			}

			$ns_from_to[$ns][] = sprintf('%s;%s', $title, $redirect_title);
		}
	}
}

file_put_contents('move_a-b_article.csv',
	implode("\n", $ns_from_to[0] )
);

file_put_contents('move_a-b_template.csv',
	implode("\n", $ns_from_to[10] )
);
