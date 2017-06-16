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
