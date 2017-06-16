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
	const SPACES = '[_ ]*';
	const NEWLINES = '[_\n ]*';
	const NEWLINES_TABS = '[ \n\t]*';

	/**
	 * «Note that / is not a special regular expression character.»
	 * — https://secure.php.net/manual/en/function.preg-quote.php
	 *
	 * asd
	 */
	static function escapeRegex($s) {
		return str_replace(
			['/'],
			['\/'],
			preg_quote($s)
		);
	}

	static function space2regex($s) {
		return str_replace(' ', '[_ ]+', self::escapeRegex( $s ) );
	}

	static function regexFirstCase($s) {
		$first = substr($s, 0, 1);
		$rest  = substr($s, 1);
		$first_up   = ucfirst($first);
		$first_down = lcfirst($first);
		$s = $first_up === $first_down ? $first_up : sprintf('[%s%s]', $first_up, $first_down);
		return $s . self::space2regex( $rest );
	}

	static function whiteRegex($s) {
		return self::SPACES . $s . self::SPACES;
	}

	static function group($s) {
		return "($s)";
	}
}

class Template {
	public $name;

	// {{Something }
	// {{Something |
	// {{Something <!-- asd --> (and then we hope the "|" or "}")
	const AFTER_NAME = Generic::NEWLINES_TABS . '[}<\|]';

	function __construct($name) {
		$this->name = $name;
	}

	private function getRegexLeft() {
		return preg_quote('{{') . Generic::group(Generic::NEWLINES_TABS) . Generic::regexFirstCase($this->name);
	}

	function getRegex() {
		return $this->getRegexLeft() . Generic::group(self::AFTER_NAME);
	}

	function getWikitextLeft() {
		//             ↓ newlines
		return '{{' . '\g<1>' . $this->name;
	}

	function getWikitext() {
		return $this->getWikitextLeft() . '\g<2>';
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

	static function space2regex($s) {
		return str_replace(' ', '[_ ]', Generic::escapeRegex( $s ) );
	}

	function getLinkRegexLeft() {
		$start = '\[\[';
		if( ! $this->normalize ) {
			return $start . Generic::whiteRegex( Generic::space2regex( $this->link ) );
		}
		return $start . Generic::whiteRegex( Generic::regexFirstCase($this->link) );
	}

	function getLinkRegexLeftPiped() {
		return $this->getLinkRegexLeft() . '\|';
	}

	function getRegex() {
		$right = $this->hasAlias() ? '\|' . Generic::whiteRegex( self::space2regex( $this->alias ) ) : '';
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
		$is_template_short =                 substr($this->a, 0, 2) === 'T:';
		$is_template = $is_template_short || substr($this->a, 0, 9) === 'Template:';
		if( $is_template ) {
			$offset = $is_template_short ? 2 : 9;
			$this->a = substr($this->a, $offset);
			$this->b = substr($this->b, $offset);
		}

		return // Direct call as wikitext
			( $is_template ? $this->operateTemplate() : $this->operateWLink() )
			. " " .
			// +
			// Indirect call under a template (as {{Vedi anche}})
			$this->operateTemplateValue();
	}

	function operateTemplate() {
		$a = new Template($this->a);
		$b = new Template($this->b);

		$all = [];
		$all[] = $a->getRegex();
		$all[] = $b->getWikitext();

		return self::spawn($all);
	}

	function operateWLink() {
		$wa    = new WLink($this->a);
		$wb    = new WLink($this->b);
		$wab   = new WLink($this->a, $this->b);
		$wbb   = new WLink($this->b, $this->b);

		$all = [];

		if( $this->normalize ) {
			// [[A]] → [[B]
			$all[] = $wa->getRegex();
			$all[] = $wb->getWikitext();

			// [[A|B]] → [[B]]
			$all[] = $wab->getRegex();
			$all[] = $wb->getWikitext();
		} else {
			$wb_u = $wb->cloneUC(); // [[B]]
			$wb_l = $wb->cloneLC(); // [[b]]

			// [[A]] → [[B]]
			$all[] = $wa->cloneUC()->getRegex();
			$all[] = $wb_u->getWikitext();

			// [[a]] → [[b]]
			$all[] = $wa->cloneLC()->getRegex();
			$all[] = $wb_l->getWikitext();

			// [[A|B]] → [[B]]
			$all[] = $wab->cloneUC()->getRegex();
			$all[] = $wb_u->getWikitext();

			// [[A|b]] → [[b]]
			$all[] = $wab->cloneLC()->getRegex();
			$all[] = $wb_l->getWikitext();
		}

		// [[A| → [[B|
		$all[] = $wa->getLinkRegexLeftPiped();
		$all[] = $wb->getWikitextLeftPiped();

		if( $this->normalize ) {
			// [[A|A]] → [[A]]
			$all[] = $wbb->getRegex();
			$all[] = $wb->getWikitext();
		} else {
			// [[A|A]] → [[A]]
			$all[] = $wbb->cloneUC()->getRegex();
			$all[] = $wb_u->getWikitext();

			// [[A|a]] → [[a]]
			$all[] = $wbb->cloneLC()->getRegex();
			$all[] = $wb_l->getWikitext();
		}

		return self::spawn($all);
	}

	function operateTemplateValue() {
		$couples = [];
		$couples[] = Generic::group('[=\|]' . Generic::NEWLINES ) .
		             Generic::group( Generic::regexFirstCase($this->a) ) .
		             Generic::group( Generic::NEWLINES . Template::AFTER_NAME );
		$couples[] = '\g<1>' . $this->b . '\g<3>';
		return self::spawn( $couples );
	}

	static function spawn($all) {
		return '"' . implode('" "', $all) . '"'; // :^)
	}
}

function convert_a_b($a, $b, $normalize = null) {
	return WLinkReplacer::factory($a, $b, $normalize)->getPywikibotCouples();
}

if( isset( $argv[1], $argv[2] ) ) {
	echo convert_a_b( ucfirst( $argv[1] ), ucfirst( $argv[2] ), @ $argv[3] );
}
