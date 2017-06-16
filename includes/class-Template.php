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
