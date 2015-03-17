=== teachPress ===
Contributors: Michael Winkler
Tags: publications, enrollments, education, courses, BibTeX, bibliography
License: GPLv2 or later
Requires at least: 3.9
Tested up to: 4.1.1
Stable tag: 5.0.2

Manage your courses and publications with teachPress 

== Description ==
This plugin unites a course management system (with modules for enrollments, documents and assessments) and a BibTeX compatible publication management. teachPress is optimized for the needs of professorships and research groups. You can use it with WordPress 3.9.0 or higher.

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
* Spanish (o)

(o) Outdated language files

= Further information = 
* [Developer blog](https://mtrv.wordpress.com/teachpress/) 
* [Upgrade information](https://mtrv.wordpress.com/2015/03/12/teachpress-5-0-upgrade-information/)
* [teachPress Shortcode Reference](https://mtrv.wordpress.com/teachpress/shortcode-reference/) 
* [teachPress on github](https://github.com/winkm89/teachPress)  

= Disclaimer =  
Use at your own risk. No warranty expressed or implied is provided.  

== Credits ==

Copyright 2008-2015 by Michael Winkler

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
* Graphics for mimetypes, user-new-3.png, folder-new-7.png and document-new-6.png by Oxygen Icons 4.3.1 http://www.oxygen-icons.org/ (Licence: LPGL)
* jquery-ui-icons.png by The jQuery Foundation (License: MIT)
* view-refresh-3.png by GNOME Icon Theme 2.26.0 http://art.gnome.org (License: GPLv2)
* bookmark-new-4.png by Tango Icon Library (License: Public Domain)
* bibtexParse by Mark Grimshaw & Guillaume Gardey (Licence: GPL)

= Thanks =
I would like to thank the team of [CBIS, Chemnitz University of Technology](http://www.tu-chemnitz.de/wirtschaft/wi2/wp/en/) for the support and the collaboration during the last years.

= Translators who did a great job in translating the plugin into other languages. Thank you! =
* [Jozef Dobos] (http://xn--dobo-j6a.eu/) (Slovak)
* Elisabetta Mancini (Italian)
* Aurelio Pons (Spanish)
* Marcus Tavares (Portuguese-Brazil)

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
1. Add course menu
2. Add publication menu
3. Example for a publication list created with [tpcloud]
 

== Frequently Asked Questions ==

= How I can use the shortcodes? =
[See teachPress shortcode reference](http://mtrv.wordpress.com/teachpress/shortcode-reference/)

= How can I add a course list in a page or post? = 
If you writing a post or page use the following tag: [tpcourselist]

= How can I add the enrollment system in my blog? =
Create a new page or post and use the following tag: [tpenrollments]

= How can I add longer course desciptions? =
Write a long course desciption as normal WordPress pages and add this page as related content to the course.

= How can I display images in publication lists? =
An example: [tplist image="left" image_size="70"]. Important: You must specify both image parameters.

= How can I split publication lists into multiple pages? =
An example: [tpcloud pagination="1" entries_per_page="50"]. This works also for [tplist] and [tpsearch]

= How can I protect course documents? =
The plugin saves course documents in your WordPress upload directory under /teachpress/*course_id*. You can add a protection for this directory with a .htaccess file without influence to your normal media files.

= How can I deactivate parts of the plugin? =
If you want to use only one part of the plugin, so write the following in the wp-config.php of your WordPress installation
`// For deactivating the course module:  
define ('TEACHPRESS_COURSE_MODULE', false);  
// For deactivating the publication module:  
define ('TEACHPRESS_PUBLICATION_MODULE', false);  `

= I see only error messages if I use the RSS-Feed for publications or the xls/csv export for enrollments. What's wrong? =
If you save plugins outside the normal path (/wp-content/plugins/), the plugin can't load required WordPress files in some cases. Solution: Change the path in the following plugin files: export.php (line 9) / feed.php (line 13).

== Upgrade Notice ==

= 5.0.0 =
It's **strongly** recommended to save your teachPress database tables before upgrading!

== Changelog ==

= 5.02 - (17.03.2015) =
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