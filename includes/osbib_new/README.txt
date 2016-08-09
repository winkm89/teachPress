OSBib Version 4.0

OSBib - An open source collection of PHP classes to manage bibliographic formatting for OS bibliography software using the OSBib standard. Taken from WIKINDX (http://wikindx.sourceforge.net).

This is an updated and remixed version of the original OSBib collection which was written by Mark Grimshaw and released through http://bibliophile.sourceforge.net under the GPL licence. Works now with PHP 5.4 or higher.

-------------------------------------------------------------------

Changelog:
----------

09/Aug/2016 -- Michael Winkler
v4.0
1/ Deprecated PHP methods replaced
2/ Dependencies replaced
3/ Wikindx specific code replaced
4/ osbib prefix for all classes introduced
5/ function deleted, which were not needed for teachPress integration

21/Nov/2005 -- Mark Grimshaw
v3.0
Major feature enhancements and minor bug fixes.

FEATURES:
1/ Added citation formatting.  Developers can now send a body of text with citation markup to the citation formatting engine for either in-text and endnote-style (endnotes or footnotes) citation formatting and appending of bibliographies.  The citation engine requires the installation of the bibliographic engine.
2/ Bibliographic formatting: further options for edition and day fields.
3/ It is now possible to further define creator strings in resource style templates to cope with more complex styles such as DIN1505.
4/ Bibliographic/citation formatting now adds footnote templates to the bibliographic templates for each resource type.  This is for styles such as Chicago which use a different format for footnotes and for the complete bibliography.

BUG FIXES:
1/ Tidied up detection of multiple punctuation in bibliographies.
Various other minor fixes.

17/July/2005 -- Mark Grimshaw
v2.1
1/  Added further date formatting in the general bibliographic options.
2/  Multiple spaces in bibliographic style templates are now correctly rendered in html.

30/June/2005 - Mark Grimshaw & Christian Boulanger
v2.0
1/ A web interface OSBib-Create for creation and editing of XML style files has been added to the package.
2/ The bibtexParse package is now included.
3/ Users should note that this OSBib package replaces OSBib-Format and OSBib-Create which are deprecated and no longer supported.
4/ A preview link is displayed next to each resource type template when editing a style.  (Requires JavaScript and, currently, does not work with Internet Explorer due to query string limits.)

17/June/2005 - Mark Grimshaw
v1.7
1/  Date ranges are now supported in bibliographic styles.
2/  User-defined strings for each of the 12 months may now be supplied in the bibliographic styles.
NB - an upgrade of the bibtexparse package is also required since handling of month fields has been improved in bibtexparse::PARSEMONTHS

08/June/2005
v1.6
Some debugging of creator list formatting in bibliographic styles.  Multiple punctuation following a 
name is now allowed if the punctuation characters are different.

19/May/2005
v1.5
1/ Removed a typo.
2/ Reorganised export filters in preparation for work on citation formatting.
3/ Added OpenOffice 'sxw' format for export.
4/ Added bibliography_xml.html describing the structure of the bibliography section of the XML files.

15/May/2005
v1.4
1/ Better support for UTF-8 multibyte strings provided by Andrea Rossato.
2/ Correction of bibtex solution @inproceedings bug.

6/May/2005
v1.3
1/ Removed some WIKINDX-specific code for bibtex parsing.
2/ Fixed a bug with bibtex 'misc' reference types.
(The above two affect those using STYLEMAPBIBTEX.)
3/ Some error checking code for file paths added by Guillaume Gardey.

5/May/2005
v1.2
1/ Corrected an error in the incorrect formatting of author names for the bibtex solution.
2/ Based on modifications suggested by Christian Boulanger, changed path information to make setting of flags easier or redundant and made the storing and loading of XML files more flexible:
	a) Changed BIBFORMAT constructor call to:
	$bibformat = new BIBFORMAT(STRING: $pathToOsbibClasses = FALSE [, BOOLEAN: $useBibtex = FALSE]);
	By default, $pathToOsbibClasses will be the same directory as BIBFORMAT.php is in.
	b) $bibformat->bibtexParsePath by default is now a bibtexParse/ directory in the same directory as BIBFORMAT.php is
	in. This path is where PARSECREATORS, PARSEMONTH and PARSEPAGE classes can be found if you wish to use
	STYLEMAPBIBTEX.
	c) The XML files are downloaded from bibliophile in uppercase format (e.g. APA.xml).  If you wish to store them in
	lowercase (e.g. apa.xml), BIBF0RMAT::loadStyle() now automatically detects this.
Unless you store PARSECREATORS, PARSEMONTH and PARSEPAGE classes elsewhere, there is now no need to set 
$bibformat->bibtexParsePath.
3/  Added an osbib.html page as a more easily navigable verion of README.

29/April/2005
v1.1
1/  Added an (almost) 'out-of-the-box' BibTeX solution.
2/  Added the method BIBFORMAT::addAllOtherItems().

28/April/2005
v1.0
Initial release