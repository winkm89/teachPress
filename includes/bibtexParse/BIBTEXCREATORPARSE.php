<?php
/**
 * This file contains the external class BIBTEXCREATORPARSE which is original published in WIKINDX4
 * @package teachpress/includes/bibtexParse
 */

/**
WIKINDX: Bibliographic Management system.
Copyright (C)

This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program;
if not, write to the
Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

The WIKINDX Team 2012
sirfragalot@users.sourceforge.net
*/
/**
* Parse BibTeX authors
* 
* @version	1
*
*	@package teachpress\includes\bibtexParse
*	@author Daniel Reidsma/Mark Grimshaw <sirfragalot@users.sourceforge.net>
*
*/
class BIBTEXCREATORPARSE
{
/** boolean If true, separate initials from firstname */
public $separateInitials = FALSE;
/** boolean */
public $removeBraces = TRUE;
/** boolean */
public $removeTilde = TRUE;
/** boolean */
public $removeEtAl = TRUE;
/** string */
private $prefix;

/**
* BIBTEXCREATORPARSE
*/
	public function __construct()
	{
	}
	
/**
* Create writer arrays from bibtex input.
*
* 'author field can be (delimiters between authors are 'and' or '&'):
* There are three possible cases:
* 1: First von Last
* 2: von Last, First
* 3: von Last, Jr, First
* @param string $input
* @return mixed FALSE|array (firstname, initials, surname, jr, von)
*/
	public function parse($input)
	{
		if($this->removeEtAl)
			$input = str_replace("et al.", '', $input);
		$input = trim($input);
		if($this->removeBraces)
			$input = str_replace('{', '', str_replace('}', '', $input));
		if($this->removeTilde)
			$input = str_replace('~', ' ', $input);
		//remove linebreaks
		$input = preg_replace('/[\r\n\t]/', ' ', $input);
		
		if (preg_match('/\s&\s/', $input))
		{
			$authorArray = $this->explodeString(" & ", $input);
			$input = implode(" and ", $authorArray);
		}
		// split on ' and '
		$authorArray = $this->explodeString(" and ", $input);
		foreach($authorArray as $value)
		{
			$firstname = $initials = $von = $surname = $jr = "";
			$this->prefix = array();
	
			//get rid of multiple spaces
			$value = preg_replace("/\s{2,}/", ' ', trim($value));

			$commaAuthor = $this->explodeString(",", $value);
			$size = sizeof($commaAuthor);
			if ($size == 1) //First von Last
			{
				// First: longest sequence of white-space separated words starting with an uppercase and that is not the whole string.
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty

				$author = $this->explodeString(" ", $value);
				if (count($author) == 1)
					$surname = $author[0];

				else
				{
					$tempFirst = array();

					$case = $this->getStringCase($author[0]);
					while ((($case == "upper") || ($case == "none")) && (count($author) > 0))
					{
						$tempFirst[] = array_shift($author);
						if(!empty($author))
							$case = $this->getStringCase($author[0]);
					}
					
					list($von, $surname) = $this->getVonLast($author);

					if ($surname == "")
						$surname = array_pop($tempFirst);
					$firstname = implode(" ", $tempFirst);
				}
			}
			elseif ($size == 2)
			{
				// we deal with von Last, First
				// First: Everything after the comma
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty
				$author = $this->explodeString(" ", $commaAuthor[0]);
				if (count($author) == 1)
					$surname = $author[0];

				else
					list($von, $surname) = $this->getVonLast($author);
				$firstname = $commaAuthor[1];
			}
			else
			{
				// we deal with von Last, Jr, First
				// First: Everything after the comma
				// von: longest sequence of whitespace separated words whose last word starts with lower case and that is not the whole string.
				// Then Last is everything else.
				// Lastname cannot be empty
				$author = $this->explodeString(" ", $commaAuthor[0]);
				if (count($author) == 1)
				$surname = $author[0];

				else
					list($von, $surname) = $this->getVonLast($author);
				$jr = $commaAuthor[1];
				$firstname = $commaAuthor[2];
			}

			$firstname = trim($firstname);
			$von = trim($von);
			$surname = trim($surname);
			$jr = trim($jr);
			
			$firstname = $this->formatFirstname($firstname);
			if($this->separateInitials)
				list($firstname, $initials) = $this->separateInitialsFunc($firstname);
			
			$creators[] = array($firstname, $initials, $surname, $jr, $von);
		}
		if(isset($creators))
			return $creators;
		return FALSE;
	}
/**
* gets the "von" and "last" part from the author array
*
* @param string $author
* @return array (von, surname)
*/
	private function getVonLast($author)
	{
		$surname = $von = "";
		$tempVon = array();
		$count = 0;
		$bVon = false;
		foreach ($author as $part)
		{
			$case = $this->getStringCase($part);
			if ($count == 0)
			{
				if ($case == "lower")
				{
					$bVon = true;
					if ($case == "none")
					$count--;
				}
			}

			if ($bVon)
			$tempVon[] = $part;

			else
			$surname = $surname." ".$part;

			$count++;
		}

		if (count($tempVon) > 0)
		{
			//find the first lowercase von starting from the end
			for ($i = (count($tempVon)-1); $i > 0; $i--)
			{
				if ($this->getStringCase($tempVon[$i]) == "lower")
					break;
				else
				$surname = array_pop($tempVon)." ".$surname;
			}

			if ($surname == "") // von part was all lower chars, the last entry is surname
				$surname = array_pop($tempVon);

			$von = implode(" ", $tempVon);
		}
		return array(trim($von), trim($surname));
	}
/**
* Explodes a string but not when the delimiter occurs within a pair of braces
*
* @param string $delimiter
* @param string $val
* @return array
*/
	private function explodeString($delimiter, $val)
	{
		$bracelevel = $i = $j = 0;
		$len = strlen($val);
		if (strlen($delimiter) > 1)
		{
			$long = true;
			$dlen = strlen($delimiter);
		}
		else
			$long = false;
			
		$strings = array();
		while ($i < $len)
		{
			if ($val[$i] == '{')
			$bracelevel++;
			elseif ($val[$i] == '}')
			$bracelevel--;
			elseif (!$bracelevel)
			{
				if ($long)
				{
					if (substr($val, $i, $dlen) == $delimiter)
					{
						$strings[] = substr($val,$j,$i-$j);
						$j=$i+$dlen;
						$i += ($dlen - 1);
					}						
				}
				else
				{
					if ($val[$i] == $delimiter)
					{
						$strings[] = substr($val,$j,$i-$j);
						$j=$i+1;
					}
				}
			}
			$i++;
		}
		$strings[] = substr($val,$j);
		return $strings;
	}
/**
* returns the case of a string
*
* Case determination:
* non-alphabetic chars are caseless
* the first alphabetic char determines case
* if a string is caseless, it is grouped to its neighbour string.
* @param string $string
* @return string
*/
	private function getStringCase($string)
	{
		$caseChar = "";
//		$string = preg_replace("/\d/", "", $string);
		$string = preg_replace("/\p{N}/u", "", $string);
		if (preg_match("/{/", $string))
			$string = preg_replace("/({[^\\\\.]*})/", "", $string);

//		if (preg_match("/\w/", $string, $caseChar))
		if (preg_match("/\p{L}/u", $string, $caseChar))
		{
			if (is_array($caseChar))
				$caseChar = $caseChar[0];

//			if (preg_match("/[a-z]/", $caseChar))
			if (preg_match("/\p{Ll}/u", $caseChar))
				return "lower";

//			else if (preg_match("/[A-Z]/", $caseChar))
			else if (preg_match("/\p{Lu}/u", $caseChar))
				return "upper";

			else
				return "none";

		}
		else
			return "none";
	}
/** 
* converts a first name to initials -- not currently used
*
* @param string $firstname
* @return string
*/
	public function getInitials($firstname)
	{
		$initials = '';
		$name = explode(' ', $firstname);
		foreach ($name as $part)
		{
			$size = strlen($part);
			if (($part{($size-1)} == ".") && ($size < 4))
				$initials .= $part;
//			elseif (preg_match("/([A-Z])/", $part, $firstChar))
			elseif (preg_match("/(\p{Lu})/u", $part, $firstChar))
				$initials .= $firstChar[0].". ";
		}
		return trim($initials);
	}
/** 
* separates initials from a firstname
*
* @param string $firstname
* @return array (firstname, initials)
*/
	private function separateInitialsFunc($firstname)
	{
		$name = $this->explodeString(" ", $firstname);
		$initials = array();
		$remain = array();
		foreach ($name as $part)
		{
			$size = strlen($part);
			
//			if(preg_match("/[a-zA-Z]{2,}/u", trim($part)))
			if(preg_match("/\.$/u", trim($part))) // find initials indicated by '.' at the end
				$initials[] = str_replace(".", " ", trim($part));
//			if(preg_match("/\p{L}{2,}/u", trim($part))) // match unicode characters
			else
				$remain[] = trim($part);
//			else
//				$initials[] = str_replace(".", " ", trim($part));
		}
		if(isset($initials))
		{
			$initials_ = '';
			foreach($initials as $initial)
				$initials_ .= ' ' . trim($initial);
				
			$initials = $initials_;
		}
		$firstname = str_replace('.', '', implode(" ", $remain));
		return array($firstname, $initials);
	}
/** 
* Format firstname
* 
* @param string $firstname
* @return string
*/	
	private function formatFirstname($firstname)
	{
		if ($firstname == "")
			return "";
		$name = $this->explodeString(".", $firstname);
		$formatName = "";
		$count = 1;
		$size = count($name);
		foreach ($name as $part)
		{
			$part = trim($part);
			
			if ($part != "")
			{
				$formatName .= $part;
				if ($count < $size)
				{
//if the end of part contains an escape character (either just \ or \{, we do not add the extra space
				  if (($part{strlen($part)-1} == "\\") || ($part{strlen($part)-1} == "{"))
				    $formatName.=".";
				  else
					  $formatName.=". ";
				}
			}
			$count++;
		}
		return $formatName;
	}
}
?>
