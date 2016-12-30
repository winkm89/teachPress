=== teachPress ===
Contributors: Michael Winkler
Tags: publications, enrollments, education, courses, BibTeX, bibliography
License: GPLv2 or later
Requires at least: 3.9
Tested up to: 4.7
Stable tag: 6.0

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
* [Developer blog](https://mtrv.wordpress.com/teachpress/) 
* [Documentation](https://github.com/winkm89/teachPress/wiki) 
* [teachPress on GitHub](https://github.com/winkm89/teachPress)  

= Disclaimer =  
Use at your own risk. No warranty expressed or implied is provided.  

== Credits ==

Copyright 2008-2016 by Michael Winkler

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
* Graphics for mimetypes, user-new-3.png, folder-new-7.png and document-new-6.png by Oxygen Icons 4.3.1 http://www.oxygen-icons.org/ (Licence: LPGL)
* jquery-ui-icons.png by The jQuery Foundation (License: MIT)
* view-refresh-3.png by GNOME Icon Theme 2.26.0 http://art.gnome.org (License: GPLv2)
* bookmark-new-4.png by Tango Icon Library (License: Public Domain)

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

= 6.0.0 =
Please note the [teachPress 6.0 Upgrade Information](https://mtrv.wordpress.com/2016/12/30/teachpress-6-0-upgrade-information/)

== Changelog ==

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

= 4.3.12 - (02.12.2014) =
* New: [tpsearch]: Reset button for search input field added
* New: [tpsearch]: CSS classes for search button and "Results for..." headline added
* New: [tpsearch]: Returns a message if no publication was found

= 4.3.11 - (01.12.2014) =
* New: [tpsearch]: New parameter "order" added

= 4.3.10 - (19.11.2014) =
* Bugfix: Fixed missing ids and classes for show/hide buttons in [tpcloud, tplist] if the list style "images" is used.

= 4.3.9 - (02.11.2014) =
* Bugfix: [tpdate]: Fixed the sort order of sub courses
* Bugfix: Fixed wrong assignments between TP_COURSE_SYSTEM and TP_PUBLICATION_SYSTEM

= 4.3.8 - (29.06.2014) =
* Bugfix: Prevent adding of `<span>`-tags within publication meta rows in XML and RTF files
* Bugfix: Fix usage of editor names instead of author names in feeds and RTF files
* Bugfix: Reenable xls export for enrollments

= 4.3.7 - (19.06.2014) =
* Bugfix: The copy function for courses creates only empty courses

= 4.3.6 - (28.05.2014) =
* New: Meta data of publications (page, adress, chapter, isbn, ...) have their own HTML selectors

= 4.3.5 - (21.05.2014) = 
* New: [tpcloud, tplist]: New link style option (link_style = "direct") added (Thanks to Ellie) 
* Bugfix: Adding of new courses of studies was impossible 
* Bugfix: Fixed a small spelling mistake

= 4.3.4 - (14.04.2014) =
* New: Support for WordPress 3.9
* Bugfix: Fixed a problem in settings menu with a wrong return to the correct tab
* Bugfix: Fixed the closing of the mass edit menu for publications
* Bugfix: Fixed some style issues in context with the new WordPress UI design
* Bugfix: Fixed a possible issue with non declared array keys in edit students menu

= 4.3.3 - (10.03.2014) =
* New: [tpcloud, tplist]: New headline option (headline = 4: sort by type and year) added (Thanks to Ellie)

= 4.3.2 - (03.03.2014) =
* New: [tpcloud]: New parameter "exclude_tags" added

= 4.3.1 - (22.01.2014) =
* New: Access control is now editable for courses and for publications separately

= 4.3.0 - (12.01.2014) =
* New: Bulk edit for publications in admin menu
* New: [tplist], [tpcloud]: Support for custom sort orders for publication lists added (if headline = 2 is used)
* New: [tpcloud]: Pagination added
* New: [tpcloud]: New parameters "pagination", "entries_per_page" and "sort_order" added
* Changed: [tpcloud]: Parameter "limit" is now "tag_limit"
* Changed: BibTeX type "masterthesis" is now "mastersthesis"
* Bugfix: The original content of a mail was replaced with the header

= 4.2.2 - (11.09.2013) =
* Bugfix: Fixed a bug which prevent adding of terms, course types and courses of studies

= 4.2.1 - (11.09.2013) =
* New: [tpcloud], [tplist], [tpsearch]: Style option "std_num" added
* New: [tpcloud], [tplist]: Style option "std_num_desc" added
* Changed: Auto wordwrap for abstracts disabled
* Bugfix: Fixed a problem with the import of BibTeX data which are enclosed with double quotes

= 4.2.0 - (31.08.2013) =
* New: Shortcodes [tplinks], [tpbibtex], [tpabstract] added
* New: More filters for publications on admin screens
* New: Export for .bib files added
* New: Import for .bib and .txt files added
* New: Import option for forcing updates of existing publications added
* New: Simple generator for bibtex keys added
* New: Auto correction for spaces in bibtex keys added
* New: Support for some html expressions (b,i,u,sup,sub,u,ul,li) and the conversion to their latex equivalents in abtracts added.
* New: Screen options for some admin screens added
* New: [tplist], [tpcloud]: Style option "numbered_desc" added
* New: [tpcloud]: New parameter "hide_tags" added
* New: [tpsearch]: New parameters "user" and "tag" added
* New: [tpsingle]: New parameters "image", "image_size" and "link" added
* New: Parameters "user" and "exclude" for get_tp_tags() added
* New: Parameter "exclude" for get_tp_tag_cloud() added
* Bugfix: Fixed a problem with the return of get_tp_publications() if the function was used in count mode and publications were filtered by year
* Bugfix: Editorials were not identified correctly
* Bugfix: Tags were not editable (tag management page)
* Bugfix: [tpcloud]: A list in "numbered" style started with 0
* Bugfix: [tpcloud]: "Exclude" parameter was ignored
* Bugfix: [tpsearch]: Impossible to use the search if WordPress uses no permalink structure
* Bugfix: [tplist]: Useless default values for "user" and "tag" removed

= 4.1.1 - (06.07.2013) =
* Bugfix: Fixed an division through zero problem in teachpress_addpublications_page()
* Bugfix: Fixed an improper presentation of meta information in some cases if the publication type is presentation
* Bugfix: [tpenrollments]: Prevent execution of tp_add_signup() and tp_delete_signup_student() if there was no course selected
* Bugfix: Fixed embedding of scripts and images for SSL-Sessions 

= 4.1.0 - (13.06.2013) =
* New: [tplist]: Optional pagination added
* New: [tplist]: New parameters "pagination" and "entries_per_page" added
* New: [tplist]: New headline option added (sort by year and type)
* New: Publication type "periodical" added
* New: Field "issuetitle" added for publications with the type "periodical"
* Bugfix: [tpcloud]: Changing of the "order" parameter was not working
* Bugfix: Fixed a bug which prevents adding of publications
* Bugfix: Unable to delete all databases with tp_uninstall()

= 4.0.5 - (05.05.2013) =
* Bugfix: [tpenrollments]: Fixed possible destroying of templates, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-registration-form-in-tpenrollments-destroyes-design)
* Bugfix: [tpenrollments]: Not fillable input fields in the registration form, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-registration-form-in-tpenrollments-destroyes-design)
* Bugfix: [tpenrollments]: User registration doesn't work, [reported in WP Support Forum](http://wordpress.org/support/topic/bug-formula-tp_registration_form-do-not-work-for-registration)
* Bugfix: Fixed a wrong SQL-Request if tags are not exists in get_tp_tag_cloud(), [reported in WP Support Forum](http://wordpress.org/support/topic/bug-see-some-php-code-under-the-add-publication-page)
* Bugfix: Fixed a wrong call of objects under some conditions in tp_registration_form()
* Bugfix: Fixed an untimely loading of data under some conditions in teachpress_students_page()
* Bugfix: Publication import: Try to fix a problem with line breaks within keywords, [reported here](http://mtrv.wordpress.com/teachpress/comment-page-4/#comment-2123)

= 4.0.4 - (15.03.2013) =
* Bugfix: Fixed a bug which prevent deleting terms, courses of studies and course types

= 4.0.3 - (13.03.2013) =
* New: [tpsingle]: New parameter "key" added
* Bugfix: [tpcourselist]: Sub courses were displayed in a wrong way
* Bugfix: [tpcloud, tplist, tpsearch, tpsingle]: Fixed the handling of "In:" strings for publication meta rows
* Bugfix: Publications could not be deleted with the first try
* Bugfix: Fixed a bug with missing keywords/tags in BibTeX based publication feeds

= 4.0.2 - (07.03.2013) =
* Bugfix: Try to fix a problem with unvisible publications
* Bugfix: Fixed a problem with a possible division through in add_publication.php

= 4.0.1 - (28.02.2013) =
* Bugfix: [tpcloud]: Tag cloud generation fixed if parameter user is enabled
* Bugfix: [tpcloud, tplist, tpsearch]: Publication list generation fixed and improved

= 4.0.0 - (27.02.2013) =
* New: Publication types "online" and "collection" added
* New: Field "urldate" added for publications with the type "online"
* New: Shortcode [tpsearch](http://mtrv.wordpress.com/teachpress/shortcode-reference/tpsearch/) added
* New: Numbered publication lists are available
* New: Single course overview redesigned
* New: Enrollments can be moved to releated courses
* New: Sort options for enrollments added
* New: Include parameter for tplist added
* New: Support for network installations added
* New: Automatic permalink detection added
* New: teachPress core API added
* Changed: Parsing of publication meta information for all shortcodes
* Changed: BibTeX import improved
* Changed: RSS feed generation improved
* Changed: Publication search improved
* Bugfix: [tpcloud, tplist, tpsingle]: Some bugs with the publication meta row output fixed
* Bugfix: Wrong flag (selected) was sometimes returned by get_tp_wp_pages() 
* Bugfix: Deprecated function eregi was replaced
* Bugfix: Optional "type" field was declared as "techtype"
* Bugfix: Fixed a bug which arised if the function "Show as BibTeX entry" was used without selecting publications before
* Killed: Menu "Add manually" replaced
* Killed: [tpcloud]: Support for "id" parameter replaced. Please use "user" instead

[Older entries](http://mtrv.wordpress.com/teachpress/changelog/)