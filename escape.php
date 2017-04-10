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

class Generic {
	static function regexSpace($s) {
		return str_replace(' ', '[_ ]', preg_quote( $s ) );
	}

	static function regexFirstCase($s) {
		$first = substr($s, 0, 1);
		$rest  = substr($s, 1);
		$first_up   = ucfirst($first);
		$first_down = lcfirst($first);
		$s = $first_up === $first_down ? $first_up : sprintf('[%s%s]', $first_up, $first_down);
		return $s . self::regexspace( $rest );
	}

	static function whiteRegex() {
		return '[_ ]*';
	}
}

class Template {
	public $name;

	function __construct($name) {
		$this->name = $name;
	}

	private function getRegexLeft() {
		return preg_quote('{{') .
			Generic::whiteRegex() .
			Generic::regexFirstCase($this->name);
	}

	function getRegex() {
		return $this->getRegexLeft() . Generic::whiteRegex() . preg_quote('}}');
	}

	function getRegexLeftPiped() {
		return $this->getRegexLeft() . Generic::whiteRegex() . preg_quote('|');
	}

	function getWikitextLeft() {
		return '{{' . $this->name;
	}

	function getWikitext() {
		return $this->getWikitextLeft() . '}}';
	}

	function getWikitextLeftPiped() {
		return $this->getWikitextLeft() . '|';
	}
}

class WLink {
	public $link;
	public $alias;
	public $normalize;

	function __construct($link, $alias = null, $normalize = null) {
		$this->link  = self::filter( $link );
		$this->alias = isset($alias) ? self::filter( $alias ) : null;
		$this->normalize = isset( $normalize ) ? $normalize : true;
	}

	function cloneForcingCallback($callback) {
		if( $this->hasAlias() ) {
			return new self( $this->link, $callback( $this->alias ), false );
		}
		return new self( $callback( $this->link ), null, false );
	}

	function cloneLC() {
		return $this->cloneForcingCallback('lcfirst');
	}

	function cloneUC() {
		return $this->cloneForcingCallback('ucfirst');
	}

	function getAlias() {
		return $this->hasAlias() ? $this->alias: $this->link;
	}

	static function filter($s) {
		return str_replace('_', ' ', trim( $s ) );
	}

	static function regexspace($s) {
		return str_replace(' ', '[_ ]', preg_quote( $s ) );
	}

	function getLinkRegexLeft() {
		$start = '\[\[';
		if( ! $this->normalize ) {
			return $start . Generic::regexSpace( $this->link );
		}
		return $start . Generic::regexFirstCase($this->link);
	}

	function getLinkRegexLeftPiped() {
		return $this->getLinkRegexLeft() . '\|';
	}

	function getRegex() {
		$right = $this->hasAlias() ? '\|' . self::regexspace( $this->alias ) : '';
		return $this->getLinkRegexLeft() . $right . '\]\]';
	}

	function getWikitextLeft() {
		return "[[{$this->link}";
	}

	function getWikitextLeftPiped() {
		return $this->getWikitextLeft() . '|';
	}

	function getWikitextRight() {
		$s = $this->hasAlias() ? '|' . $this->alias : '';
		return $s . ']]';
	}

	function hasAlias() {
		return isset( $this->alias );
	}

	function getWikitext() {
		return $this->getWikitextLeft() . $this->getWikitextRight();
	}
}

class WLinkSearchReplace {
	public $a;
	public $b;

	function construct($a, $b) {
		$this->a = $a;
		$this->b = $b;
	}
}

class WLinkReplacer {
	public $a;
	public $b;
	public $normalize;

	function __construct($a, $b, $normalize = null) {
		$this->a = $a;
		$this->b = $b;
		$this->normalize = isset( $normalize ) ? $normalize : false;
	}

	static function factory($a, $b, $opts = null) {
		return new self($a, $b, $opts);
	}

	function getPywikibotCouples() {
		if( substr($this->a, 0, 9) === 'Template:' ) {
			$this->a = substr($this->a, 9);
			$this->b = substr($this->b, 9);
			return $this->operateTemplate();
		}
		return $this->operateWLink();
	}

	function operateTemplate() {
		$a = new Template($this->a);
		$b = new Template($this->b);

		$all = [];
		$all[] = $a->getRegex();
		$all[] = $b->getWikitext();

		$all[] = $a->getRegexLeftPiped();
		$all[] = $b->getWikitextLeftPiped();

		return self::spawn($all);
	}

	function operateWLink() {
		$wa    = new WLink($this->a);
		$wb    = new WLink($this->b);
		$wab   = new WLink($this->a, $this->b);

		$all = [];

		if( $this->normalize ) {
			$all[] = $wa->getRegex();
			$all[] = $wb->getWikitext();

			$all[] = $wab->getRegex();
			$all[] = $wb->getWikitext();
		} else {
			$wb_u = $wb->cloneUC();
			$wb_l = $wb->cloneLC();

			$all[] = $wa->cloneUC()->getRegex();
			$all[] = $wb_u->getWikitext();

			$all[] = $wa->cloneLC()->getRegex();
			$all[] = $wb_l->getWikitext();

			$all[] = $wab->cloneUC()->getRegex();
			$all[] = $wb_u->getWikitext();

			$all[] = $wab->cloneLC()->getRegex();
			$all[] = $wb_l->getWikitext();
		}

		$all[] = $wa->getLinkRegexLeftPiped();
		$all[] = $wb->getWikitextLeftPiped();

		return self::spawn($all);
	}

	static function spawn($all) {
		return "'" . implode("' '", $all) . "'"; // :^)
	}
}

function convert_a_b($a, $b, $normalize = null) {
	return WLinkReplacer::factory($a, $b, $normalize)->getPywikibotCouples();
}

echo convert_a_b( ucfirst( $argv[1] ), ucfirst( $argv[2] ), @ $argv[3] ) . "\n";
