=== teachPress ===
Contributors: Michael Winkler
Tags: publications, enrollments, education, courses, BibTeX, bibliography
License: GPLv2 or later
Requires at least: 3.9
Tested up to: 5.3
Requires PHP: 5.3
Stable tag: 6.3

Manage your courses and publications with teachPress 

== Description ==
This plugin unites a course management system (with modules for enrollments, documents and assessments) and a powerful BibTeX compatible publication management. Both modules can be operated independently. teachPress is optimized for the needs of professorships and research groups. You can use it with WordPress 3.9.0 or higher.

= Features: =
* BibTeX compatible multi user publication management
* BibTeX import for publications
* BibTeX and RTF export for publications
* RSS feed for publications
* Course management with integrated modules for enrollments, assessments and documents
* XLS/CSV export for course lists
* Many shortcodes for an easy using of publication lists, publication searches, enrollments and course overviews
* Dymamic meta data system for courses, students and publications

= Supported Languages =
* English
* German
* Italian (o)
* Portuguese (Brazil) (o)
* Slovak (o)
* Slovenian (o)
* Spanish

(o) Incomplete language files

= Further information = 
* [Wiki/Documentation](https://github.com/winkm89/teachPress/wiki) 
* [teachPress on GitHub](https://github.com/winkm89/teachPress)  
* [Developer blog](https://mtrv.wordpress.com/teachpress/) 

= Disclaimer =  
Use at your own risk. No warranty expressed or implied is provided.  

== Credits ==

Copyright 2008-2019 by Michael Winkler

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

= Licence information of external resources =
* bibtexParse by Mark Grimshaw & Guillaume Gardey (Licence: GPL)
* Font Awesome Free 5.10.1 by fontawesome (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License)
* Academicons 1.8.6 by James Walsh (Font: SIL OFL 1.1, CSS: MIT License)
* jquery-ui-icons.png by The jQuery Foundation (License: MIT)

= Thanks =
I would like to thank the team of [CBIS, Chemnitz University of Technology](http://www.tu-chemnitz.de/wirtschaft/wi2/wp/en/) for the support and the collaboration during the last years.

= Translators who did a great job in translating the plugin into other languages. Thank you! =
* Alexandre Touzet (French)
* Alfonso Montejo Ráez (Spanish)
* Marcus Tavares (Portuguese-Brazil)
* [Jozef Dobos] (http://xn--dobo-j6a.eu/) (Slovak)
* Elisabetta Mancini (Italian)

== Installation ==

1. Download the plugin.
2. Extract all the files. 
3. Upload everything (keeping the directory structure) to your plugins directory.
4. Activate the plugin through the 'plugins' menu in WordPress.

**For updates:**

1. Download the plugin.
2. Delete all files in the 'plugins/teachpress/' directory.
3. Upload all files to the 'plugins/teachpress/' directory.
4. Go in the backend to Courses->Settings and click on "Update to ....".

== Screenshots ==
1. Publication overview screen
2. Add publication screen
3. Add course screen
4. Single course menu
5. Example for a publication list created with [tpcloud]
 

== Frequently Asked Questions ==

= How can I add a publication list in a page or post? = 
If you writing a post or page use the following string: [tpcloud] or [tplist]

= How can I add a course list in a page or post? = 
If you writing a post or page use the following string: [tpcourselist]

= How can I add the enrollment system in my blog? =
Create a new page or post and use the following string: [tpenrollments]

= How can I change thing on the shortcodes? =
[See teachPress shortcode reference](https://github.com/winkm89/teachPress/wiki#shortcodes)

= How can I add longer course desciptions? =
Write a long course desciption as normal WordPress pages and add this page as related content to the course.

= How can I display images in publication lists? =
An example: [tplist image="left" image_size="70"]. Important: You must specify both image parameters.

= How can I split publication lists into multiple pages? =
An example: [tpcloud pagination="1" entries_per_page="50"]. This works also for [tplist] and [tpsearch]

= How can I protect course documents? =
The plugin saves course documents in your WordPress upload directory under /teachpress/*course_id*. You can add a protection for this directory with a .htaccess file without influence to your normal media files.

[More FAQs are available on GitHub](https://github.com/winkm89/teachPress/wiki/FAQ)

== Upgrade Notice ==

= 6.0.1 =
Please note the [teachPress 6.0 Upgrade Information](https://mtrv.wordpress.com/2016/12/30/teachpress-6-0-upgrade-information/)

== Changelog ==

= 6.3 (beta) =
* New: Extra icons for URL/Files in the publication overview (#118) (Thanks to Sven Mayer)
* New: Font Awesome Fonts added (#118)
* New: Academicons Font added (#118)
* New: Menu position is now editable via TEACHPRESS_MENU_POSITION constant (#122)
* New: Various index columns for most of the database tables added 
* Changed: Mimetype icons removed 
* Bugfix: Rename form fields for publications
* Bugfix: Fix problem with not escaped authors in JavaScript on add-publication.php
* Bugfix: Fix "A variable mismatch has been detected." error on add-publication.php
* Bugfix: Fix missing output from tp_bibtex_import::parse_month()
* Bugfix: Fix missing design for disabled last page button in page menus

= 6.2.5 (07.04.2019) =
* New: [tplist]: HTML anchor added
* Changed: Handling of [tplist] shortcodes which are used multiple times in one post/page
* Changed: "Deleted" message for course terms and types in the settings added
* Changed: Overwrite publication option for the importer is now an update option 
* Bugfix: Buttons for 'next/last page' of [tplist] are not visible in responsive design (#109)
* Bugfix: bibtexParse class uses removed function split (#111)
* Bugfix: Fix array offset errors in bibtexParse class PARSEENTRIES::get_line()

= 6.2.4 (10.04.2018) =
* Changed: Requirement for PHP 5.3+ added
* Bugfix: Descending numeration starts with 0, if pagination is disabled in the shortcodes
* Bugfix: [tpsearch]: Descending numeration doesn't work
* Bugfix: Widget init uses deprecated PHP function (#97)

= 6.2.3 (11.03.2018) = 
* New: [tpcloud]: New parameter "show_type_filter" added
* New: New method has_tag() for publication templates

= 6.2.2 (04.03.2018) = 
* New: [tpcloud]: New parameter "show_in_author_filter" added (#95)
* New: Publications import: Added handling for bibtech special chars {\aa}, {\AA},'{\O}','{\o}' (Thanks to Thomas Grah)
* Bugfix: [tpcloud, tplist]: "exclude_types" parameter doesn't work with more than one value (#94)
* Bugfix: [tpcloud, tplist, tpsearch] Fix for the numeration of publications (#91)

= 6.2.1 - (02.01.2018) =
* New: [tpcloud]: New parameter "show_author_filter" (#89)
* New: [tpcloud]: New parameter "show_user_filter" (#90)
* Changed: Limit number of items in RSS feeds (#86)
* Changed: Wrap description of publications in RSS feed in CDATA blocks (#87)
* Bigfix: Fix a compatibility problem with PHP 5.3 or lower (#88, #72)

= 6.2.0 - (22.11.2017) =
* New: Ajax based bibtex key generator (#70)
* New: [tplist, tpcloud]: New parameter "exclude_types" (#75)
* New: DOI url resolver is now editable through constant TEACHPRESS_DOI_RESOLVER (#72)
* New: publication type "workshop" added (#67)
* New: Add "show_link" option for [tpref] shortcode (experimental feature)
* Bugfix: DOI included in URL list (Thanks to Sven Mayer) (#73)
* Bugfix: Infinte loop when using headlines grouped by year and then by type (headline="4") (Thanks to Abel Gómez) (#76)
* Bugfix: Wrong quote characters cause invalid generation of LaTeX accented letters (Thanks to Abel Gómez) (#77)
* Bugfix: Enable title links in inline mode if DOI is available (Thanks to Abel Gómez)  (#78)
* Bugfix: Booktitle column is too small for some publications (#80)
* Bugfix: Division through zero error in shortcodes.php (#82)
* Bugfix: Fix substitutions in convert_bibtex_functions (#83)
* Bugfix: Prevent double encodes in tags (#84)

= 6.1.0 - (14.08.2017) =
* New: List of imports is now availabe
* New: Support for returning meta data to get_publications() added (#61)
* New: [tplist, tpcloud]: New parameter "include_editor_as_author" added (#62)
* Changed: "Dynamic detection" is now the default option for author/editor detection in the publication import
* Changed: Foreign key constraints for new installations removed
* Bugfix: Fixed editor parsing in the publication import
* Bugfix: Fixed problems with some special chars in the publication import for author/editor names

= 6.0.4 - (14.05.2017) =
* New: Publication type "patent" added (#59)
* New: [tpcloud, tplist, tpsearch]: New parameters "author_separator" and "editor_separator" added (#58)
* Changed: [tpcloud]: "nofollow" attributes for tag links added
* Changed: [tpcloud]: URL values in jump menus splitted
* Changed: Expansion for special chars which will be detected during the import
* Bugfix: Fix for displaying editors as authors for periodical in RTF and text exports
* Bugfix: [tpcloud]: Fix align setting for the "show all" button
* Bugfix: [tpsingle]: Fix for displaying editors as authors for collections and periodical
* Bugfix: [template: teachPress original small]: Remove annoying extra space in bibliographic template (#54)
* Bugfix: [template: teachPress original small]: Years were displayed twice in publication lists (#10)
* Bugfix: Fixed missing tables in tp_tables::remove()

= 6.0.3 - (17.02.2017) =
* Bugfix: [tpsearch]: Fixed a PHP warning which occurs if "as_filter" is enabled.

= 6.0.2 - (22.01.2017) =
* Bugfix: UTF-8 to TeX conversion fixed for é/è and É/È (Thanks to Marcel Waldvogel) (#51)
* Bugfix: Variable initialization fixed for better PHP 7.1 compatibility (Thanks to Marcel Waldvogel) (#50)
* Bugfix: Enable german translation of "inproceedings" in singular

= 6.0.1 - (14.01.2017) =
* Bugfix: [tpcloud, tplist, tpsearch]: Fixed a problem which can lead to PHP fatal errors while generating publication lists (#49)

= 6.0.0 - (30.12.2016) =
* New: Template system for publication lists added (#26)
* New: Cite function for publications in the backend (#22)
* New: Option for dynamic author/editor format detection for bibtex imports added (#15)
* New: System for replacing BibTeX journal macros added (#46)
* New: [tpenrollments]: New parameter "order_signups" added
* New: [tpcloud]: New parameter "year" added (#34)
* New: [tpcloud, tplist, tpsearch]: New parameter "template" added (#26)
* New: [tpcloud, tplist, tpsearch]: New parameter "image_link" added (#8)
* New: [tpcloud, tplist, tpsearch]: New parameter "title_ref" added (#14)
* New: [tpcloud, tplist, tpsearch]: New parameter "show_bibtex" added (#12)
* New: [tpcloud, tplist]: Altmetric support added; New parameters: "show_altmetric_donut", "show_altmetric_entry" (#44)
* New: Colored labels for publication types added (with the new template) (#13)
* New: Option to mark publications as "forthcoming"
* New: Altmetric support added (not avaiable by default)
* Changed: RSS feeds, bibtex feeds, export streams and ajax requests now generated via WordPress APIs (#35)
* Changed: [tplist, tpcloud]: Parameter "entries_per_page" works now if pagination is disabled (#20)
* Changed: [tplist, tpcloud, tpsearch]: Default settings for author/editor name parsing changed to initials (#19)
* Changed: [tplist, tpcloud, tpsearch]: Usage of parameter "style" changed (#26)
* Changed: [tpcloud]: Show filters (year, type, authors, user) if more than one value is given via parameters (#33)
* Changed: Style improvements for better support for WordPress 4.4+
* Bugfix: Fixed the problem with including wp-load.php for generating feeds and exports (#35)
* Bugfix: Fixed a bug in the tinyMCE plugin which leads to wrong default parameters for [tplist]
* Bugfix: Fixed a optical sort order problem with assessments
* Bugfix: Inproceeding renamed to Inproceedings because the singular form doesn't exist

= 5.0.20 - (05.11.2016) =
* New: French translation added (Thanks to Alexandre Touzet)

= 5.0.19 - (01.10.2016) =
* Bugfix: Delete of publications fails if custom publication meta fields were used (#45)

= 5.0.18 - (16.09.2016) =
* New: Enroll multiple students at one time (#23)
* Changed: Calls of get_currentuserinfo() replaced with wp_get_current_user() calls (#31)
* Changed: Added class for tp_allbooks_link
* Changed: Publication search now includes the abstract (#39)
* Changed: Style improvements for better support for WordPress 4.4+
* Bugfix: Hide document manager in tinyMCE plugin for users who have no capabilities to use teachPress (#3)
* Bugfix: HTML output for proceedings shows the edition instead of volume/number (#18)
* Bugfix: Prevent a deletion of line breaks by bibtexParse in the import (#21)
* Bugfix: Fixed a variable initialization bug in core/widgets.php (#24)
* Bugfix: CSV export of enrollments has problems with the visibilty options of meta data fields (#36)
* Bugfix: Fixed a bug which leads to creation of related content (e.g. pages) for sub courses (#40)
* Bugfix: [student meta fields]: The "required" attribute could not be set for select boxes (#41)
* Bugfix: Fixed a sort order problem in the document manager after uploading files (#42)
* Bugfix: Fixed a possible security issues in enrollments.php

= 5.0.17 - (15.10.2015) =
* New: [tpenrollments]: New parameters "order_parent" and "order_child" added

= 5.0.16 - (13.10.2015) =
* New: Foreign key checks can be disabled for installs and updates (parameter TEACHPRESS_FOREIGN_KEY_CHECKS added).

= 5.0.15 - (28.09.2015) =
* New: [tpcloud]: New parameter "author" added (#16)
* Bugfix: Fixed a wrong definition of where clauses in some database functions under some conditions (Thanks for reporting to David Lahm)
* Bugfix: Fixed a bug in the tinyMCE plugin which leads to wrong parameter names in shortcodes
* Bugfix: Fixed several bugs in the copy course function

= 5.0.14 - (24.08.2015) =
* Changed: Spanish translation updated (Thanks to Alfonso Montejo)
* Bugfix: Fixed a bug which has added all default values of the plugin settings after reactivation again
* Bugfix: Edit student form doesn't display the student data

= 5.0.13 - (16.08.2015) =
* New: Optional error reporting for actviations added
* Bugfix: Descending style formats were not available via tinyMCE plugin [#11](https://github.com/winkm89/teachPress/issues/11)
* Bugfix: Fixed a bug which leads to an error message after plugin activation

= 5.0.12 - (22.07.2015) =
* Bugfix: Fixed a bug in the declaration of two parameters for the tinyMCE plugin
* Bugfix: Constructor of the books widget updated to PHP 5 style, see [Deprecating PHP4 style constructors in WordPress 4.3](https://make.wordpress.org/core/2015/07/02/deprecating-php4-style-constructors-in-wordpress-4-3/)
* Bugfix: The plural form of "Journal Articles" was not displayed 

= 5.0.11 - (17.06.2015) =
* Bugfix: Fixed a bug which prevent the visibility of some teachpress setting tabs
* Bugfix: Fixed a bug which prevent the selection of sub courses in the course overview
* Bugfix: [tpsingle]: Missing CSS declaration 

= 5.0.10 - (06.06.2015) =
* New: Support for HTML text formatting elements (b, i, u, s, sub, sup, em, small, del) in publication titles added
* Changed: Spanish language files updated (Thanks to Alfonso Montejo Ráez)
* Changed: Small improvements for character encoding in exports
* Bugfix: Fixed usage of deprecated parameter (TP_COURSE_SYSTEM, TP_PUBLICATION_SYSTEM) in settings menu

= 5.0.9 - (15.05.2015) =
* New: Partial slovenian translation added
* New: Fade out status messages of the document manager after some seconds
* Changed: Better course select list for the document manager
* Bugfix: Fixed a bug in the handling of volume/number formatting in a publication meta row under certain conditions
* Bugfix: Capability checks added for course actions (editing and deleting)
* Bugfix: Fixed an "order by" bug in get_tp_options()
* Bugfix: Prevent loading of frontent scripts/styles in the document manager tab of the tinyMCE plugin
* Bugfix: [tpcloud]: Author filter doesn't consider the user parameter

= 5.0.8 - (13.04.2015) =
* Bugfix: Fixed a bug in [tpenrollments] which displays the wrong student meta data

= 5.0.7 - (31.03.2015) =
* Bugfix: Tag list was not displayed in the backend

= 5.0.6 - (27.03.2015) =
* New: Send a notification to participants if they were added to a course by admin or after an increase of the number of places
* Bugfix: [tpcloud, tplist]: Fixed a problem with doubled entries, if the headline type 3 or 4 is used.
* Bugfix: [tpcloud]: The parameter "type" was ignored
* Bugfix: [tpcloud]: The filter reset was not displayed if only the author filter was set
* Bugfix: Fix usage of constant WPLANG under WordPress 4.0 or higher

= 5.0.5 - (25.03.2015) =
* Bugfix: Hide the author line of a publication entry, if there is no author or editor given
* Bugfix: Prevent double escapes for sql statements

= 5.0.4 - (22.03.2015) =
* Changed: BibTeX value "tpstatus" renamed to "pubstate" (adapted from biblatex)
* Bugfix: Missing comma in BibTeX entries

= 5.0.3 - (17.03.2015) =
* Bugfix: Fixed an old installer bug, which adds the wrong template for related content. 

= 5.0.2 - (17.03.2015) =
* New: [tpcloud, tplist, tp_search]: New parameter "container_suffix" added

= 5.0.1 - (15.03.2015) =
* New: [tplist]: New parameter "author" added

= 5.0.0 - (12.03.2015) =
* New: A real author filter for publications added
* New: Autocomplete for authors/editors added
* New: Meta data system for courses, students and publications added
* New: Assessment management for courses added
* New: Simple capability management for courses added
* New: Simple document management for courses added
* New: Direct export of .bib files for selected publications added (via "Show as BibTeX entry")
* New: Option for converting utf-8 chars into BibTeX compatible ASCII strings
* New: Direct creation of related content based on drafts added
* New: Direct creation of sub courses added
* New: Page menu for enrollments added
* New: DOI field for publications added
* New: Shortcode [tpcourseinfo]
* New: Shortcode [tpcoursedocs]
* New: [tpenrollments]: New parameter "date_format" added
* New: [tpcloud]: New parameter "show_tags_as" added
* New: tinyMCE 4 plugin for inserting shortcodes and documents
* New: Send notification to a student if his registration is moved from the waitinglist to the course by automatic fill up
* New: Editable time limit for exports and uploads added (constant TEACHPRESS_TIME_LIMIT)
* Changed: Rewritten core API
* Changed: Rewritten Shortcode [tpcloud]
* Changed: Rewritten Shortcode [tpenrollments]
* Changed: [tpcloud, tplist]: Pagination is enabled by default, the parameter entries_per_page is set to 50 (before: 30)
* Changed: UI modifications for better integration in WordPress 3.9+
* Changed: Using HTML5s "required" attribute for required fields in registration forms
* Changed: Using parameters instead of variables for the definition of database table names
* Changed: Visible names for some publication types changed
* Changed: Using field type "search" for search fields
* Changed: Increased varchar limit for some field in table teachpress_pub (author,editor --> 3000 chars; institution, organization --> 500 chars)
* Changed: The parameters TP_COURSE_SYSTEM and TP_PUBLICATION_SYSTEM were renamed to TEACHPRESS_COURSE_MODULE and TEACHPRESS_PUBLICATION_MODULE
* Changed: The plugin requires at least WordPress 3.9 instead of 3.3
* Bugfix: Fixed the handling of curly-brackets for the definition of surnames in author/editor names
* Bugfix: Replace double spaces in author/editor names
* Bugfix: BibTeX import: Better identification for the date of publishing
* Bugfix: teachPress books widget works again
* Killed: The shortcode [tpdate] is deprecated, use [tpcourseinfo] instead
* Killed: The following functions are deprecated: get_tp_course(), get_tp_courses(), get_tp_course_free_places(), get_tp_tags(), get_tp_tag_cloud(), get_tp_publication(), get_tp_publications(), tp_is_user_subscribed(), tp_check_bookmark(), tp_admin_page_menu()

[Older entries](https://github.com/winkm89/teachPress/wiki/Changelog)