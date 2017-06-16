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
