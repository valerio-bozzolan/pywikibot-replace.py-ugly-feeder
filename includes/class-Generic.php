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
