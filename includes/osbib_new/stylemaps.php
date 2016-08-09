<?php
/**
* This mapping class is specific to BibTeX for those databases that store or access their data in associative arrays 
* modelled on BibTeX.  e.g.
* array(
* 'author'	=>	'Grimshaw, Mark and Boulanger, Christian',
* 'title'	=>	'How Bibliographies Ruined our Lives',
* 'year'	=>	'2005',
* 'volume'	=>	'20',
* 'number'	=>	'4',
* 'journal'	=>	'Journal of Mundane Trivia',
* 'pages'	=>	'42--111',
* 'howpublished' =>	"\url{http://bibliophile.sourceforge.net}",
* );
*
* See the docs/osbib.html for details on how to use this
*/
class osbib_stylemapbibtex {
    function __construct() {
        $this->loadMap();
    }
    /**
    * loadMap: Load the map into arrays based on resource type.
    *
    * The basic() array contains database fields that are common to all types of resources.
    * The key is the database field and the value is displayed to the user to be part of the style definition.
    * e.g. if the user enters:
    * author. title. publisherName|: publisherLocation|.
    * for a style definition for a book, we know that 'author' is the database field 'creator1', 'title' is 
    * the database field 'title' etc.
    * There are some exceptions as defined by WIKINDX (other systems may have different methods).  Because these may be 
    * represented in different ways in different systems, you will need to explicitly define these.  See BIBSTYLE.php 
    * for examples of how WIKINDX does this.  The comments below relate to how WIKINDX stores such values in its database:
    * 1/ 'originalPublicationYear doesn't exist in the database but is used to re-order publicationYear and reprintYear 
    * for book and book_article resource types.
    * 2/ 'pages' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields pageStart and pageEnd.
    * 3/ 'date' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields miscField2 (day) and miscField3 (month).
    * 4/ 'runningTime' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields miscField1 (minute) and miscField4 (hour) for film/broadcast.
    *
    * @author Mark Grimshaw
    */
    function loadMap() {
        /**
        * What fields are available to the in-text citation template? This array should NOT be changed.
        */
        $this->citation = array(
            "creator" => "creator", 
            "title"	=>	"title",
            "year" => "year", 
            "pages" => "pages", 
        );
        /**
        * What fields are available to the in-text citation template for endnote-style citations? This array should NOT be changed.
        */
        $this->citationEndnoteInText = array(
            "id" => "id",
            "pages" => "pages", 
        );
        /**
        * What fields are available to the endnote citation template for endnote-style citations? This array should NOT be changed.
        */
        $this->citationEndnote = array(
            "citation" => "citation",
            "creator" => "creator", 
            "title"	=>	"title",
            "year" => "year", 
            "pages" => "pages", 
        );
        /**
        * Map between OSBib's resource types (keys) and the bibliographic system's resource types (values). You must 
        * NOT remove any elements or change the generic types. You may edit the value of each element. If your system 
        * does not have a particular resource type, then you should set the value to FALSE (e.g. 'film' => FALSE,)
        */
        $this->types = array(
            // The generic types must be present and unchanged.  DO NOT CHANGE THE VALUE OF THESE THREE!
                'genericBook'		=>	'genericBook',
                'genericArticle'	=>	'genericArticle',
                'genericMisc'		=>	'genericMisc',
            // Edit values if necessary
                'book'			=>	'book',
                'book_article'		=>	'inbook',
                'journal_article'	=>	'article',
                'newspaper_article'	=>	'article',
                'magazine_article'	=>	'article',
                'proceedings'		=>	'proceedings',
                'conference_paper'	=>	'incollection',
                'proceedings_article'	=>	'inproceedings',
                'thesis'		=>	'phdthesis',
                'web_article'		=>	FALSE,
                'film'			=>	FALSE,
                'broadcast'		=>	FALSE,
                'music_album'		=>	FALSE,
                'music_track'		=>	FALSE,
                'music_score'		=>	FALSE,
                'artwork'		=>	FALSE,
                'software'		=>	FALSE,
                'audiovisual'		=>	FALSE,
                'database'		=>	FALSE,
                'government_report'	=>	FALSE,
                'report'		=>	'techreport',
                'hearing'		=>	FALSE,
                'statute'		=>	FALSE,
                'legal_ruling'		=>	FALSE,
                'case'			=>	FALSE,
                'bill'			=>	FALSE,
                'patent'		=>	FALSE,
                'personal'		=>	FALSE,
                'unpublished'		=>	'unpublished',
                'classical'		=>	FALSE,
                'manuscript'		=>	FALSE,
                'map'			=>	FALSE,
                'chart'			=>	FALSE,
                'miscellaneous'		=>	'misc',
        );
        /**
        * Basic array of elements common to all types - change the key to map the database field that stores that value.
        */
        $this->basic = array(
            'title'		=>	'title',
            'year'		=>	'publicationYear',
        );
        /**
        * Creator mapping.  OSBib uses 'creator1' .. 'creator5' for internally managing creator names such as 
        * author, editor, series editor, translator, reviser, artist, inventor, composer etc.  The associative 
        * array (SQL row) you submit to $this->bibformat->preProcess() MUST use these fields for the creators.
        * Furthermore, you may NOT change any keys (or values) in the arrays below that are 'creator1' ... 'creator5'.
        */

        /**
        * For the following arrays, the only things you should change are the keys of each array (except 'creator1' 
        * .. 'creator5' - see above).  These keys are your database fieldnames for resources.
        * The values are displayed to the user when creating/editing a style and 
        * must NOT change or be removed.  If your database does not store a particular value, then it should still 
        * exist in the array and must have a null key (e.g. $this->book[] = 'publisherName'; in the case of a database 
        * that does not store publisher names for books ;-)).
        * 
        * The keys 'creator1', 'creator2', 'date' and 'URL' are special keys.  All other keys should be lowercase 
        * field names from the BibTeX specification.
        **************
        **************
        * Do NOT remove arrays.
        * Do not remove array elements.
        * Do not add array elements.
        **************
        **************
        *
        * You do not need to edit arrays where the value in $this->types above is FALSE as the array will then simply be 
        * ignored.  So, although 34 resource types are defined here, if you system only has 6 resource types, you only need 
        * to edit those 6 types.
        *
        * If you do not conform to this, OSBib XML style definition sheets you produce will not be compatible with other systems.
        */
        // Three Generic fallback types used when there's no style definition for one of the resources below.
        // Generic Book type - no collection data, like a book
        $this->genericBook = $this->basic;
        $this->genericBook['creator1'] = 'creator';
        $this->genericBook['creator2'] = 'editor';
        $this->genericBook['publisher'] = 'publisherName';
        $this->genericBook['address'] = 'publisherLocation';
        $this->genericBook['ISBN'] = 'ID';
        // Generic Article type - in a collection like an article
        $this->genericArticle = $this->basic;
        $this->genericArticle['creator1'] = 'creator';
        $this->genericArticle['creator2'] = 'editor';
        $this->genericArticle['journal'] = 'collection';
        $this->genericArticle['publisher'] = 'publisherName';
        $this->genericArticle['address'] = 'publisherLocation';
        $this->genericArticle['date'] = 'date';
        $this->genericArticle['pages'] = 'pages';
        $this->genericArticle['ISBN'] = 'ID';
        // Generic Miscellaneous type - whatever is best not put in the above two fall back types....?
        $this->genericMisc = $this->basic;
        $this->genericMisc['creator1'] = 'creator';
        $this->genericMisc['publisher'] = 'publisherName';
        $this->genericMisc['address'] = 'publisherLocation';
        $this->genericMisc['type'] = 'type';
        $this->genericMisc['date'] = 'date';
        $this->genericMisc['ISBN'] = 'ID';
		
        // Resource specific mappings. The order here is the display order when editing/creating styles.
        // BOOK
        $this->book = $this->basic;
        $this->book['creator1'] = 'author';
        $this->book['creator2'] = 'editor';
        $this->book[] = 'translator';
        $this->book[] = 'reviser';
        $this->book[] = 'seriesEditor';
        $this->book['series'] = 'seriesTitle';
        $this->book['edition'] = 'edition';
        $this->book['number'] = 'seriesNumber';
        $this->book[] = 'numberOfVolumes';
        $this->book['volume'] = 'volumeNumber';
        $this->book[] = 'originalPublicationYear';
        $this->book[] = 'volumePublicationYear';
        $this->book['publisher'] = 'publisherName';
        $this->book['address'] = 'publisherLocation';
        $this->book['ISBN'] = 'ISBN';
        // BOOK ARTICLE/CHAPTER
        $this->book_article = $this->book;
        $this->book_article['bookitle'] = 'book';
        $this->book_article[] = 'shortBook';
        $this->book_article['pages'] = 'pages';
        // JOURNAL ARTICLE
        $this->journal_article = $this->basic;
        $this->journal_article['creator1'] = 'author';
        $this->journal_article['volume'] = 'volume';
        $this->journal_article['number'] = 'issue';
        $this->journal_article['journal'] = 'journal';
        $this->journal_article[] = 'shortJournal';
        $this->journal_article['pages'] = 'pages';
        $this->journal_article['ISSN'] = 'ISSN';
        // NEWSPAPER ARTICLE
        $this->newspaper_article = $this->basic;
        $this->newspaper_article['year'] = 'issueYear'; // override publicationYear
        $this->newspaper_article['date'] = 'issueDate';
        $this->newspaper_article['creator1'] = 'author';
        $this->newspaper_article['journal'] = 'newspaper';
        $this->newspaper_article[] = 'shortNewspaper';
        $this->newspaper_article['chapter'] = 'section';
        $this->newspaper_article['address'] = 'city';
        $this->newspaper_article['pages'] = 'pages';
        $this->newspaper_article['ISSN'] = 'ISSN';
        // MAGAZINE ARTICLE
        $this->magazine_article = $this->basic;
        $this->magazine_article['year'] = 'issueYear'; // override publicationYear
        $this->magazine_article['date'] = 'issueDate';
        $this->magazine_article['creator1'] = 'author';
        $this->magazine_article['journal'] = 'magazine';
        $this->magazine_article[] = 'shortMagazine';
        $this->magazine_article['edition'] = 'edition';
        $this->magazine_article['type'] = 'type';
        $this->magazine_article['volume'] = 'volume';
        $this->magazine_article['number'] = 'number';
        $this->magazine_article['pages'] = 'pages';
        $this->magazine_article['ISSN'] = 'ISSN';
        // PROCEEDINGS ARTICLE
        $this->proceedings_article = $this->basic;
        $this->proceedings_article['creator1'] = 'author';
        $this->proceedings_article['booktitle'] = 'conference';
        $this->proceedings_article[] = 'shortConference';
        $this->proceedings_article['organization'] = 'conferenceOrganiser';
        $this->proceedings_article['address'] = 'conferenceLocation';
        $this->proceedings_article['date'] = 'conferenceDate';
        // overwrite publicationYear
        $this->proceedings_article['year'] = 'conferenceYear';
        $this->proceedings_article['pages'] = 'pages';
        $this->proceedings_article['ISBN'] = 'ISSN';
        // THESIS
        $this->thesis = $this->basic;
        // overwrite publicationYear
        $this->thesis['year'] = 'awardYear';
        $this->thesis['creator1'] = 'author';
        $this->thesis[] = 'label'; // 'thesis', 'dissertation'
        // 'type' is special and used in BIBFORMAT.php
        $this->thesis['type'] = 'type'; // 'Master's', 'PhD', 'Doctoral', 'Diploma' etc.
        $this->thesis['institution'] = 'institution';
        $this->thesis['address'] = 'institutionLocation';
        $this->thesis[] = 'department';
        $this->thesis['journal'] = 'journal';
        $this->thesis[] = 'shortJournal';
        $this->thesis['volume'] = 'volumeNumber';
        $this->thesis['number'] = 'issueNumber';
        $this->thesis[] = 'abstractYear';
        $this->thesis['pages'] = 'pages';
        $this->thesis['ISBN'] = 'ID';
        // WEB ARTICLE
        $this->web_article = $this->basic;
        $this->web_article['creator1'] = 'author';
        $this->web_article['journal'] = 'journal';
        $this->web_article[] = 'shortJournal';
        $this->web_article['volume'] = 'volume';
        $this->web_article['number'] = 'issue';
        $this->web_article['pages'] = 'pages';
        $this->web_article['URL'] = 'URL';
        $this->web_article['date'] = 'accessDate';
        $this->web_article[] = 'accessYear';
        $this->web_article['ISBN'] = 'ID';
        // MISCELLANEOUS
        $this->miscellaneous = $this->basic;
        $this->miscellaneous['creator1'] = 'creator';
        $this->miscellaneous['type'] = 'medium';
        $this->miscellaneous['publisher'] = 'publisherName';
        $this->miscellaneous['address'] = 'publisherLocation';
        $this->miscellaneous['ISBN'] = 'ID';
        // REPORT/DOCUMENTATION
        $this->report = $this->basic;
        $this->report['creator1'] = 'author';
        $this->report['type'] = 'type';
        $this->report['series'] = 'seriesTitle';
        $this->report['number'] = 'number';
        $this->report['institution'] = 'institution';
        $this->report['address'] = 'institutionLocation';
        $this->report['date'] = 'reportDate';
        $this->report['year'] = 'reportYear'; // override
        $this->report['pages'] = 'pages';
        $this->report['ISSN'] = 'ISSN';
        // PROCEEDINGS (complete set of)
        $this->proceedings = $this->basic;
        $this->proceedings['creator2'] = 'editor';
        $this->proceedings['organization'] = 'conferenceOrganiser';
        $this->proceedings['address'] = 'conferenceLocation';
        $this->proceedings['date'] = 'conferenceDate';
        $this->proceedings['year'] = 'conferenceYear'; // override
        $this->proceedings['ISBN'] = 'ISSN';
        // UNPUBLISHED WORK
        $this->unpublished = $this->basic;
        $this->unpublished['year'] = 'year'; // Override
        $this->unpublished['creator1'] = 'author';
        $this->unpublished['type'] = 'type';
        $this->unpublished['institution'] = 'institution';
        $this->unpublished['address'] = 'institutionLocation';
        $this->unpublished['ISBN'] = 'ID';
        // CONFERENCE PAPER
        $this->conference_paper = $this->basic;
        $this->conference_paper['creator1'] = 'author';
        $this->conference_paper['publisherName'] = 'publisherName';
        $this->conference_paper['publisherLocation'] = 'publisherLocation';
        $this->conference_paper['isbn'] = 'ISSN';

        /***********************
        The following not used by BibTeX.  They are ignored as per $this->types above.
        ***********************/

        // FILM
        $this->film = $this->basic;
        $this->film['creator1'] = 'director';
        $this->film['creator2'] = 'producer';
        $this->film['field1'] = 'country';
        $this->film['runningTime'] = 'runningTime';
        $this->film['publisherName'] = 'distributor';
        $this->film['isbn'] = 'ID';
        // BROADCAST
        $this->broadcast = $this->basic;
        $this->broadcast['creator1'] = 'director';
        $this->broadcast['creator2'] = 'producer';
        $this->broadcast['runningTime'] = 'runningTime';
        $this->broadcast['date'] = 'broadcastDate';
        $this->broadcast['year1'] = 'broadcastYear'; // override
        $this->broadcast['publisherName'] = 'channel';
        $this->broadcast['publisherLocation'] = 'channelLocation';
        $this->broadcast['isbn'] = 'ID';
        // SOFTWARE
        $this->software = $this->basic;
        $this->software['creator1'] = 'author';
        $this->software['field2'] = 'type';
        $this->software['field4'] = 'version';
        $this->software['publisherName'] = 'publisherName';
        $this->software['publisherLocation'] = 'publisherLocation';
        $this->software['isbn'] = 'ID';
        // ARTWORK
        $this->artwork = $this->basic;
        $this->artwork['creator1'] = 'artist';
        $this->artwork['field2'] = 'medium';
        $this->artwork['publisherName'] = 'publisherName';
        $this->artwork['publisherLocation'] = 'publisherLocation';
        $this->artwork['isbn'] = 'ID';
        // AUDIOVISUAL
        $this->audiovisual = $this->basic;
        $this->audiovisual['creator1'] = 'author';
        $this->audiovisual['creator2'] = 'performer';
        $this->audiovisual['creator5'] = 'seriesEditor';
        $this->audiovisual['field1'] = 'seriesTitle';
        $this->audiovisual['field4'] = 'seriesNumber';
        $this->audiovisual['field3'] = 'edition';
        $this->audiovisual['miscField4'] = 'numberOfVolumes';
        $this->audiovisual['field5'] = 'volumeNumber';
        $this->audiovisual['year3'] = 'volumePublicationYear';
        $this->audiovisual['publisherName'] = 'publisherName';
        $this->audiovisual['publisherLocation'] = 'publisherLocation';
        $this->audiovisual['field2'] = 'medium';
        $this->audiovisual['isbn'] = 'ID';
        // (LEGAL) CASE
        $this->case = $this->basic;
        $this->case['field1'] = 'reporter';
        $this->case['creator3'] = 'counsel';
        $this->case['field4'] = 'reporterVolume';
        $this->case['date'] = 'caseDecidedDate';
        $this->case['year1'] = 'caseDecidedYear'; // override
        $this->case['publisherName'] = 'court';
        $this->case['isbn'] = 'ISBN';
        // LEGAL RULING/REGULATION
        $this->legal_ruling = $this->basic;
        $this->legal_ruling['creator1'] = 'author';
        $this->legal_ruling['field1'] = 'section';
        $this->legal_ruling['field2'] = 'type';
        $this->legal_ruling['field4'] = 'number';
        $this->legal_ruling['field3'] = 'edition';
        $this->legal_ruling['date'] = 'codeEditionDate';
        $this->legal_ruling['year1'] = 'codeEditionYear'; // override
        $this->legal_ruling['publisherName'] = 'publisherName';
        $this->legal_ruling['publisherLocation'] = 'publisherLocation';
        $this->legal_ruling['pages'] = 'pages';
        $this->legal_ruling['isbn'] = 'ISBN';
        // (PARLIAMENTARY) BILL
        $this->bill = $this->basic;
        $this->bill['field2'] = 'code';
        $this->bill['field3'] = 'codeVolume';
        $this->bill['field1'] = 'codeSection';
        $this->bill['field5'] = 'number';
        $this->bill['field4'] = 'session';
        $this->bill['year1'] = 'sessionYear'; // override publicationYear
        $this->bill['publisherName'] = 'legislativeBody';
        $this->bill['publisherLocation'] = 'publisherLocation';
        $this->bill['pages'] = 'pages';
        $this->bill['isbn'] = 'ID';
        // CLASSICAL WORK
        $this->classical = $this->basic;
        $this->classical['creator1'] = 'attributedTo';
        $this->classical['field4'] = 'volume';
        $this->classical['isbn'] = 'ISBN';
        // GOVERNMENT REPORT/DOCUMENTATION
        $this->government_report = $this->basic;
        $this->government_report['creator1'] = 'author';
        $this->government_report['field2'] = 'department';
        $this->government_report['field1'] = 'section';
        $this->government_report['field4'] = 'volume';
        $this->government_report['field5'] = 'issueNumber';
        $this->government_report['field3'] = 'edition';
        $this->government_report['publisherName'] = 'publisherName';
        $this->government_report['pages'] = 'pages';
        $this->government_report['isbn'] = 'ISSN';
        // GOVERNMENT/LEGAL HEARING
        $this->hearing = $this->basic;
        $this->hearing['field1'] = 'committee';
        $this->hearing['field2'] = 'legislativeBody';
        $this->hearing['field3'] = 'session';
        $this->hearing['miscField4'] = 'numberOfVolumes';
        $this->hearing['field4'] = 'documentNumber';
        $this->hearing['date'] = 'hearingDate';
        $this->hearing['year1'] = 'hearingYear'; // override
        $this->hearing['publisherName'] = 'publisherName';
        $this->hearing['publisherLocation'] = 'publisherLocation';
        $this->hearing['pages'] = 'pages';
        $this->hearing['isbn'] = 'ISSN';
        // ONLINE DATABASE
        $this->database = $this->basic;
        $this->database['creator1'] = 'author';
        $this->database['URL'] = 'URL';
        $this->database['date'] = 'accessDate';
        $this->database['year2'] = 'accessYear';
        $this->database['publisherName'] = 'publisherName';
        $this->database['publisherLocation'] = 'publisherLocation';
        $this->database['isbn'] = 'ID';
        // MANUSCRIPT
        $this->manuscript = $this->basic;
        $this->manuscript['creator1'] = 'author';
        $this->manuscript['collectionTitle'] = 'collection';
        $this->manuscript['collectionTitleShort'] = 'shortCollection';
        $this->manuscript['field3'] = 'number';
        $this->manuscript['field2'] = 'type';
        $this->manuscript['date'] = 'issueDate';
        $this->manuscript['year1'] = 'issueYear'; // override
        $this->manuscript['pages'] = 'pages';
        $this->manuscript['isbn'] = 'ISBN';
        // MAP
        $this->map = $this->basic;
        $this->map['creator1'] = 'cartographer';
        $this->map['creator5'] = 'seriesEditor';
        $this->map['field1'] = 'seriesTitle';
        $this->map['field2'] = 'type';
        $this->map['field3'] = 'edition';
        $this->map['publisherName'] = 'publisherName';
        $this->map['publisherLocation'] = 'publisherLocation';
        $this->map['isbn'] = 'ISBN';
        // CHART
        $this->chart = $this->basic;
        $this->chart['creator1'] = 'creator';
        $this->chart['field1'] = 'fileName';
        $this->chart['field2'] = 'program';
        $this->chart['field3'] = 'size';
        $this->chart['field4'] = 'type';
        $this->chart['field5'] = 'version';
        $this->chart['field6'] = 'number';
        $this->chart['publisherName'] = 'publisherName';
        $this->chart['publisherLocation'] = 'publisherLocation';
        $this->chart['isbn'] = 'ID';
        // STATUTE
        $this->statute = $this->basic;
        $this->statute['field2'] = 'code';
        $this->statute['field5'] = 'codeNumber';
        $this->statute['field1'] = 'publicLawNumber';
        $this->statute['field3'] = 'session';
        $this->statute['field4'] = 'section';
        $this->statute['date'] = 'statuteDate';
        $this->statute['year1'] = 'statuteYear'; // override
        $this->statute['pages'] = 'pages';
        $this->statute['isbn'] = 'ID';
        // PATENT
        $this->patent = $this->basic;
        $this->patent['creator1'] = 'inventor';
        $this->patent['creator2'] = 'issuingOrganisation';
        $this->patent['creator3'] = 'agent';
        $this->patent['creator4'] = 'intAuthor';
        $this->patent['field8'] = 'patentNumber';
        $this->patent['field2'] = 'versionNumber';
        $this->patent['field3'] = 'applicationNumber';
        $this->patent['field6'] = 'intTitle';
        $this->patent['field5'] = 'intPatentNumber';
        $this->patent['field7'] = 'intClassification';
        $this->patent['field1'] = 'publishedSource';
        $this->patent['field9'] = 'legalStatus';
        $this->patent['field4'] = 'type';
        $this->patent['publisherName'] = 'assignee';
        $this->patent['publisherLocation'] = 'assigneeLocation';
        $this->patent['date'] = 'issueDate';
        $this->patent['year1'] = 'issueYear'; // override
        $this->patent['isbn'] = 'ID';
        // PERSONAL COMMUNICATION
        $this->personal = $this->basic;
        $this->personal['creator1'] = 'author';
        $this->personal['creator2'] = 'recipient';
        $this->personal['field2'] = 'type';
        $this->personal['date'] = 'date';
        $this->personal['year1'] = 'year'; // override
        $this->personal['isbn'] = 'ID';
        // MUSIC ALBUM
        $this->music_album = $this->basic;
        $this->music_album['creator1'] = 'performer';
        $this->music_album['creator2'] = 'composer';
        $this->music_album['creator3'] = 'conductor';
        $this->music_album['field2'] = 'medium';
        $this->music_album['publisherName'] = 'publisherName';
        $this->music_album['isbn'] = 'ID';
        // MUSIC TRACK
        $this->music_track = $this->basic;
        $this->music_track['creator1'] = 'performer';
        $this->music_track['creator2'] = 'composer';
        $this->music_track['creator3'] = 'conductor';
        $this->music_track['collectionTitle'] = 'album';
        $this->music_track['collectionTitleShort'] = 'shortAlbum';
        $this->music_track['field2'] = 'medium';
        $this->music_track['publisherName'] = 'publisherName';
        $this->music_track['isbn'] = 'ID';
        // MUSIC SCORE
        $this->music_score = $this->basic;
        $this->music_score['creator1'] = 'composer';
        $this->music_score['creator2'] = 'editor';
        $this->music_score['field3'] = 'edition';
        $this->music_score['publisherName'] = 'publisherName';
        $this->music_score['publisherLocation'] = 'publisherLocation';
        $this->music_score['isbn'] = 'ISBN';
    }
}


/**
 * Stylemapping class
 * @author Mark Grimshaw
 */
class osbib_stylemap {
    function __construct() {
        $this->loadMap();
    }
    /**
    * loadMap: Load the map into arrays based on resource type.
    *
    * The basic() array contains database fields that are common to all types of resources.
    * The key is the database field and the value is displayed to the user to be part of the style definition.
    * e.g. if the user enters:
    * author. title. publisherName|: publisherLocation|.
    * for a style definition for a book, we know that 'author' is the database field 'creator1', 'title' is 
    * the database field 'title' etc.
    * There are some exceptions as defined by WIKINDX (other systems may have different methods).  Because these may be 
    * represented in different ways in different systems, you will need to explicitly define these.  See BIBSTYLE.php 
    * for examples of how WIKINDX does this.  The comments below relate to how WIKINDX stores such values in its database:
    * 1/ 'originalPublicationYear doesn't exist in the database but is used to re-order publicationYear and reprintYear 
    * for book and book_article resource types.
    * 2/ 'pages' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields pageStart and pageEnd.
    * 3/ 'date' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields miscField2 (day) and miscField3 (month).
    * 4/ 'runningTime' doesn't exist in the database but is created on the fly in BIBSTYLE.php as an amalgamation of 
    * the database fields miscField1 (minute) and miscField4 (hour) for film/broadcast.
    *
    * @author Mark Grimshaw
    */
    function loadMap() {
        /**
        * What fields are available to the in-text citation template? This array should NOT be changed.
        */
        $this->citation = array(
            "creator"       => "creator", 
            "title"         => "title",
            "year"          => "year", 
            "pages"         => "pages", 
            "shortTitle"    => "shortTitle",
            "URL"           => "URL");
        /**
        * What fields are available to the in-text citation template for endnote-style citations? This array should NOT be changed.
        */
        $this->citationEndnoteInText = array(
            "id" => "id",
            "pages" => "pages");
        /**
        * What fields are available to the endnote citation template for endnote-style citations? This array should NOT be changed.
        */
        $this->citationEndnote = array(
            "citation" => "citation",
            "creator" => "creator", 
            "title"	=>	"title",
            "year" => "year", 
            "pages" => "pages");
        /**
        * 
        * Map between OSBib's resource types (keys) and the bibliographic system's resource types (values). You must 
        * NOT remove any elements NOR change the generic types. You may edit the value of each element. If your system 
        * does not have a particular resource type, then you should set the value to FALSE (e.g. 'film' => FALSE,)
        */
        $this->types = array(
            // The generic types must be present and unchanged.  DO NOT CHANGE THE VALUE OF THESE THREE!
            'genericBook'           =>	'genericBook',
            'genericArticle'        =>	'genericArticle',
            'genericMisc'           =>	'genericMisc',
            // Edit values if necessary
            'book'                  =>	'book',
            'book_article'          =>	'book_article',
            'book_chapter'          =>	'book_chapter',
            'journal_article'       =>	'journal_article',
            'newspaper_article'     =>	'newspaper_article',
            'magazine_article'      =>	'magazine_article',
            'proceedings'           =>	'proceedings',
            'conference_paper'      =>	'conference_paper',
            'conference_poster'     =>	'conference_poster',
            'proceedings_article'   =>	'proceedings_article',
            'thesis'                =>	'thesis',
            'web_site'              =>	'web_site',
            'web_article'           =>	'web_article',
            'web_encyclopedia'      =>	'web_encyclopedia',
            'web_encyclopedia_article'	=>	'web_encyclopedia_article',
            'film'                  =>	'film',
            'broadcast'             =>	'broadcast',
            'music_album'           =>	'music_album',
            'music_track'           =>	'music_track',
            'music_score'           =>	'music_score',
            'artwork'               =>	'artwork',
            'software'              =>	'software',
            'audiovisual'           =>	'audiovisual',
            'database'              =>	'database',
            'government_report'     =>	'government_report',
            'report'                =>	'report',
            'hearing'               =>	'hearing',
            'statute'               =>	'statute',
            'brochure'              =>	'brochure',
            'legal_ruling'          =>	'legal_ruling',
            'case'                  =>	'case',
            'bill'                  =>	'bill',
            'patent'                =>	'patent',
            'personal'              =>	'personal',
            'unpublished'           =>	'unpublished',
            'classical'             =>	'classical',
            'manuscript'            =>	'manuscript',
            'map'                   =>	'map',
            'chart'                 =>	'chart',
            'miscellaneous'         =>	'miscellaneous',
            'miscellaneous_section' =>	'miscellaneous_section');
        /**
        * Basic array of elements common to all types - change the key to map the database field that stores that value.
        */
        $this->basic = array(
            'title'     =>	'title',
            'year1'	=>	'publicationYear');
        /**
        * Creator mapping.  OSBib uses 'creator1' .. 'creator5' for internally managing creator names such as 
        * author, editor, series editor, translator, reviser, artist, inventor, composer etc.  The associative 
        * array (SQL row) you submit to $this->bibformat->preProcess() MUST use these fields for the creators.
        * Furthermore, you may NOT change any keys (or values) in the arrays below that are 'creator1' ... 'creator5'.
        */

        /**
        *
        * For the following arrays, the only things you should change are the keys of each array (except 'creator1' 
        * .. 'creator5' - see above).  These keys are your database fieldnames for resources.
        * The values are displayed to the user when creating/editing a style and 
        * must NOT change or be removed.  If your database does not store a particular value, then it should still 
        * exist in the array and must have a null key (e.g. $this->book[] = 'publisherName'; in the case of a database 
        * that does not store publisher names for books ;-)).
        * 
        **************
        **************
        * Do NOT remove arrays.
        * Do not remove array elements.
        * Do not add array elements.
        **************
        **************
        *
        * You do not need to edit arrays where the value in $this->types above is FALSE as the array will then simply be 
        * ignored.  So, although 34 resource types are defined here, if you system only has 6 resource types, you only need 
        * to edit those 6 types.
        *
        * If you do not conform to this, XML style definition sheets you produce will not be compatible with other systems.
        */
        // Three Generic fallback types used when there's no style definition for one of the resources below.
        // Generic Book type - no collection data, like a book
        $this->genericBook = $this->basic;
        $this->genericBook['creator1'] = 'creator';
        $this->genericBook['creator2'] = 'editor';
        $this->genericBook['publisherName'] = 'publisherName';
        $this->genericBook['publisherLocation'] = 'publisherLocation';
        $this->genericBook['isbn'] = 'ID';
        // Generic Article type - in a collection like an article
        $this->genericArticle = $this->basic;
        $this->genericArticle['creator1'] = 'creator';
        $this->genericArticle['creator2'] = 'editor';
        $this->genericArticle['collectionTitle'] = 'collection';
        $this->genericArticle['publisherName'] = 'publisherName';
        $this->genericArticle['publisherLocation'] = 'publisherLocation';
        $this->genericArticle['date'] = 'date';
        $this->genericArticle['pages'] = 'pages';
        $this->genericArticle['isbn'] = 'ID';
        // Generic Miscellaneous type - whatever is best not put in the above two fall back types....?
        $this->genericMisc = $this->basic;
        $this->genericMisc['creator1'] = 'creator';
        $this->genericMisc['publisherName'] = 'publisherName';
        $this->genericMisc['publisherLocation'] = 'publisherLocation';
        $this->genericMisc['field2'] = 'type';
        $this->genericMisc['date'] = 'date';
        $this->genericMisc['isbn'] = 'ID';
		
        // Resource specific mappings. The order here is the display order when editing/creating styles.
        // BOOK
        $this->book = $this->basic;
        $this->book['creator1'] = 'author';
        $this->book['creator2'] = 'editor';
        $this->book['creator3'] = 'translator';
        $this->book['creator4'] = 'reviser';
        $this->book['creator5'] = 'seriesEditor';
        $this->book['field1'] = 'seriesTitle';
        $this->book['field2'] = 'edition';
        $this->book['field3'] = 'seriesNumber';
        $this->book['miscField4'] = 'numberOfVolumes';
        $this->book['field4'] = 'volumeNumber';
        $this->book['year2'] = 'originalPublicationYear';
        $this->book['year3'] = 'volumePublicationYear';
        $this->book['publisherName'] = 'publisherName';
        $this->book['publisherLocation'] = 'publisherLocation';
        $this->book['isbn'] = 'ISBN';
        // BOOK ARTICLE/CHAPTER
        $this->book_article = $this->basic;
        $this->book_article['creator1'] = 'author';
        $this->book_article['creator2'] = 'editor';
        $this->book_article['creator3'] = 'translator';
        $this->book_article['creator4'] = 'reviser';
        $this->book_article['creator5'] = 'seriesEditor';
        $this->book_article['field1'] = 'seriesTitle';
        $this->book_article['field2'] = 'edition';
        $this->book_article['field3'] = 'seriesNumber';
        $this->book_article['miscField4'] = 'numberOfVolumes';
        $this->book_article['field4'] = 'volumeNumber';
        $this->book_article['year2'] = 'originalPublicationYear';
        $this->book_article['year3'] = 'volumePublicationYear';
        $this->book_article['publisherName'] = 'publisherName';
        $this->book_article['publisherLocation'] = 'publisherLocation';
        $this->book_article['collectionTitle'] = 'book';
        $this->book_article['collectionTitleShort'] = 'shortBook';
        $this->book_article['pages'] = 'pages';
        $this->book_article['isbn'] = 'ISBN';
        // JOURNAL ARTICLE
        $this->journal_article = $this->basic;
        $this->journal_article['creator1'] = 'author';
        $this->journal_article['field1'] = 'volume';
        $this->journal_article['field2'] = 'issue';
        $this->journal_article['date'] = 'issueDate';
        $this->journal_article['collectionTitle'] = 'journal';
        $this->journal_article['collectionTitleShort'] = 'shortJournal';
        $this->journal_article['pages'] = 'pages';
        $this->journal_article['isbn'] = 'ISSN';
        // NEWSPAPER ARTICLE
        $this->newspaper_article = $this->basic;
        $this->newspaper_article['year1'] = 'issueYear'; // override publicationYear
        $this->newspaper_article['date'] = 'issueDate';
        $this->newspaper_article['creator1'] = 'author';
        $this->newspaper_article['collectionTitle'] = 'newspaper';
        $this->newspaper_article['collectionTitleShort'] = 'shortNewspaper';
        $this->newspaper_article['field1'] = 'section';
        $this->newspaper_article['field2'] = 'city';
        $this->newspaper_article['pages'] = 'pages';
        $this->newspaper_article['isbn'] = 'ISSN';
        // MAGAZINE ARTICLE
        $this->magazine_article = $this->basic;
        $this->magazine_article['year1'] = 'issueYear'; // override publicationYear
        $this->magazine_article['date'] = 'issueDate';
        $this->magazine_article['creator1'] = 'author';
        $this->magazine_article['collectionTitle'] = 'magazine';
        $this->magazine_article['collectionTitleShort'] = 'shortMagazine';
        $this->magazine_article['field1'] = 'edition';
        $this->magazine_article['field2'] = 'type';
        $this->magazine_article['field4'] = 'volume';
        $this->magazine_article['field3'] = 'number';
        $this->magazine_article['pages'] = 'pages';
        $this->magazine_article['isbn'] = 'ISSN';
        // PROCEEDINGS ARTICLE
        $this->proceedings_article = $this->basic;
        $this->proceedings_article['field1'] = 'seriesTitle';
        $this->proceedings_article['field3'] = 'seriesNumber';
        $this->proceedings_article['field4'] = 'volumeNumber';
        $this->proceedings_article['creator1'] = 'author';
        $this->proceedings_article['creator2'] = 'editor';
        $this->proceedings_article['collectionTitle'] = 'conference';
        $this->proceedings_article['collectionTitleShort'] = 'shortConference';
        $this->proceedings_article['publisherName'] = 'conferenceOrganiser';
        $this->proceedings_article['publisherLocation'] = 'conferenceLocation';
        $this->proceedings_article['date'] = 'conferenceDate';
        $this->proceedings_article['year2'] = 'conferenceYear';
        $this->proceedings_article['publisher'] = 'publisherName'; // on the fly in BIBSTYLE
        $this->proceedings_article['location'] = 'publisherLocation'; // on the fly in BIBSTYLE
        $this->proceedings_article['pages'] = 'pages';
        $this->proceedings_article['isbn'] = 'ISSN';
        // THESIS
        $this->thesis = $this->basic;
        // overwrite publicationYear
        $this->thesis['year1'] = 'awardYear';
        $this->thesis['creator1'] = 'author';
        $this->thesis['field1'] = 'type'; // 'Master's', 'PhD', 'Doctoral', 'Diploma' etc.
        $this->thesis['field2'] = 'label'; // 'thesis', 'dissertation'
        $this->thesis['publisherName'] = 'institution';
        $this->thesis['publisherLocation'] = 'institutionLocation';
        $this->thesis['field5'] = 'department';
        $this->thesis['collectionTitle'] = 'journal';
        $this->thesis['collectionTitleShort'] = 'shortJournal';
        $this->thesis['field3'] = 'volumeNumber';
        $this->thesis['field4'] = 'issueNumber';
        $this->thesis['year2'] = 'abstractYear';
        $this->thesis['pages'] = 'pages';
        $this->thesis['isbn'] = 'ID';
        // WEB ARTICLE
        $this->web_article = $this->basic;
        $this->web_article['creator1'] = 'author';
        $this->web_article['collectionTitle'] = 'journal';
        $this->web_article['collectionTitleShort'] = 'shortJournal';
        $this->web_article['field1'] = 'volume';
        $this->web_article['field2'] = 'issue';
        $this->web_article['pages'] = 'pages';
        $this->web_article['URL'] = 'URL';
        $this->web_article['date'] = 'accessDate';
        $this->web_article['year2'] = 'accessYear';
        $this->web_article['isbn'] = 'ID';
        // FILM
        $this->film = $this->basic;
        $this->film['creator1'] = 'director';
        $this->film['creator2'] = 'producer';
        $this->film['field1'] = 'country';
        $this->film['runningTime'] = 'runningTime';
        $this->film['publisherName'] = 'distributor';
        $this->film['isbn'] = 'ID';
        // BROADCAST
        $this->broadcast = $this->basic;
        $this->broadcast['creator1'] = 'director';
        $this->broadcast['creator2'] = 'producer';
        $this->broadcast['runningTime'] = 'runningTime';
        $this->broadcast['date'] = 'broadcastDate';
        $this->broadcast['year1'] = 'broadcastYear'; // override
        $this->broadcast['publisherName'] = 'channel';
        $this->broadcast['publisherLocation'] = 'channelLocation';
        $this->broadcast['isbn'] = 'ID';
        // SOFTWARE
        $this->software = $this->basic;
        $this->software['creator1'] = 'author';
        $this->software['field2'] = 'type';
        $this->software['field4'] = 'version';
        $this->software['publisherName'] = 'publisherName';
        $this->software['publisherLocation'] = 'publisherLocation';
        $this->software['isbn'] = 'ID';
        // ARTWORK
        $this->artwork = $this->basic;
        $this->artwork['creator1'] = 'artist';
        $this->artwork['field2'] = 'medium';
        $this->artwork['publisherName'] = 'publisherName';
        $this->artwork['publisherLocation'] = 'publisherLocation';
        $this->artwork['isbn'] = 'ID';
        // AUDIOVISUAL
        $this->audiovisual = $this->basic;
        $this->audiovisual['creator1'] = 'author';
        $this->audiovisual['creator2'] = 'performer';
        $this->audiovisual['creator5'] = 'seriesEditor';
        $this->audiovisual['field1'] = 'seriesTitle';
        $this->audiovisual['field4'] = 'seriesNumber';
        $this->audiovisual['field3'] = 'edition';
        $this->audiovisual['miscField4'] = 'numberOfVolumes';
        $this->audiovisual['field5'] = 'volumeNumber';
        $this->audiovisual['year3'] = 'volumePublicationYear';
        $this->audiovisual['publisherName'] = 'publisherName';
        $this->audiovisual['publisherLocation'] = 'publisherLocation';
        $this->audiovisual['field2'] = 'medium';
        $this->audiovisual['isbn'] = 'ID';
        // (LEGAL) CASE
        $this->case = $this->basic;
        $this->case['field1'] = 'reporter';
        $this->case['creator3'] = 'counsel';
        $this->case['field4'] = 'reporterVolume';
        $this->case['date'] = 'caseDecidedDate';
        $this->case['year1'] = 'caseDecidedYear'; // override
        $this->case['publisherName'] = 'court';
        $this->case['isbn'] = 'ISBN';
        // LEGAL RULING/REGULATION
        $this->legal_ruling = $this->basic;
        $this->legal_ruling['creator1'] = 'author';
        $this->legal_ruling['field1'] = 'section';
        $this->legal_ruling['field2'] = 'type';
        $this->legal_ruling['field4'] = 'number';
        $this->legal_ruling['field3'] = 'edition';
        $this->legal_ruling['date'] = 'codeEditionDate';
        $this->legal_ruling['year1'] = 'codeEditionYear'; // override
        $this->legal_ruling['publisherName'] = 'publisherName';
        $this->legal_ruling['publisherLocation'] = 'publisherLocation';
        $this->legal_ruling['pages'] = 'pages';
        $this->legal_ruling['isbn'] = 'ISBN';
        // (PARLIAMENTARY) BILL
        $this->bill = $this->basic;
        $this->bill['field2'] = 'code';
        $this->bill['field3'] = 'codeVolume';
        $this->bill['field1'] = 'codeSection';
        $this->bill['field5'] = 'number';
        $this->bill['field4'] = 'session';
        $this->bill['year1'] = 'sessionYear'; // override publicationYear
        $this->bill['publisherName'] = 'legislativeBody';
        $this->bill['publisherLocation'] = 'publisherLocation';
        $this->bill['pages'] = 'pages';
        $this->bill['isbn'] = 'ID';
        // CLASSICAL WORK
        $this->classical = $this->basic;
        $this->classical['creator1'] = 'attributedTo';
        $this->classical['field4'] = 'volume';
        $this->classical['isbn'] = 'ISBN';
        // CONFERENCE PAPER
        $this->conference_paper = $this->basic;
        $this->conference_paper['creator1'] = 'author';
        $this->conference_paper['publisherName'] = 'publisherName';
        $this->conference_paper['publisherLocation'] = 'publisherLocation';
        $this->conference_paper['isbn'] = 'ISSN';
        // MISCELLANEOUS
        $this->miscellaneous = $this->basic;
        $this->miscellaneous['creator1'] = 'creator';
        $this->miscellaneous['field2'] = 'medium';
        $this->miscellaneous['publisherName'] = 'publisherName';
        $this->miscellaneous['publisherLocation'] = 'publisherLocation';
        $this->miscellaneous['isbn'] = 'ID';
        // GOVERNMENT REPORT/DOCUMENTATION
        $this->government_report = $this->basic;
        $this->government_report['creator1'] = 'author';
        $this->government_report['field2'] = 'department';
        $this->government_report['field1'] = 'section';
        $this->government_report['field4'] = 'volume';
        $this->government_report['field5'] = 'issueNumber';
        $this->government_report['field3'] = 'edition';
        $this->government_report['publisherName'] = 'publisherName';
        $this->government_report['pages'] = 'pages';
        $this->government_report['isbn'] = 'ISSN';
        // REPORT/DOCUMENTATION
        $this->report = $this->basic;
        $this->report['creator1'] = 'author';
        $this->report['field2'] = 'type';
        $this->report['field1'] = 'seriesTitle';
        $this->report['field5'] = 'number';
        $this->report['publisherName'] = 'institution';
        $this->report['publisherLocation'] = 'institutionLocation';
        $this->report['date'] = 'reportDate';
        $this->report['year1'] = 'reportYear'; // override
        $this->report['pages'] = 'pages';
        $this->report['isbn'] = 'ISSN';
        // GOVERNMENT/LEGAL HEARING
        $this->hearing = $this->basic;
        $this->hearing['field1'] = 'committee';
        $this->hearing['field2'] = 'legislativeBody';
        $this->hearing['field3'] = 'session';
        $this->hearing['miscField4'] = 'numberOfVolumes';
        $this->hearing['field4'] = 'documentNumber';
        $this->hearing['date'] = 'hearingDate';
        $this->hearing['year1'] = 'hearingYear'; // override
        $this->hearing['publisherName'] = 'publisherName';
        $this->hearing['publisherLocation'] = 'publisherLocation';
        $this->hearing['pages'] = 'pages';
        $this->hearing['isbn'] = 'ISSN';
        // ONLINE DATABASE
        $this->database = $this->basic;
        $this->database['creator1'] = 'author';
        $this->database['URL'] = 'URL';
        $this->database['date'] = 'accessDate';
        $this->database['year2'] = 'accessYear';
        $this->database['publisherName'] = 'publisherName';
        $this->database['publisherLocation'] = 'publisherLocation';
        $this->database['isbn'] = 'ID';
        // MANUSCRIPT
        $this->manuscript = $this->basic;
        $this->manuscript['creator1'] = 'author';
        $this->manuscript['collectionTitle'] = 'collection';
        $this->manuscript['collectionTitleShort'] = 'shortCollection';
        $this->manuscript['field3'] = 'number';
        $this->manuscript['field2'] = 'type';
        $this->manuscript['date'] = 'issueDate';
        $this->manuscript['year1'] = 'issueYear'; // override
        $this->manuscript['pages'] = 'pages';
        $this->manuscript['isbn'] = 'ISBN';
        // MAP
        $this->map = $this->basic;
        $this->map['creator1'] = 'cartographer';
        $this->map['creator5'] = 'seriesEditor';
        $this->map['field1'] = 'seriesTitle';
        $this->map['field2'] = 'type';
        $this->map['field3'] = 'edition';
        $this->map['publisherName'] = 'publisherName';
        $this->map['publisherLocation'] = 'publisherLocation';
        $this->map['isbn'] = 'ISBN';
        // CHART
        $this->chart = $this->basic;
        $this->chart['creator1'] = 'creator';
        $this->chart['field1'] = 'fileName';
        $this->chart['field2'] = 'program';
        $this->chart['field3'] = 'size';
        $this->chart['field4'] = 'type';
        $this->chart['field5'] = 'version';
        $this->chart['field6'] = 'number';
        $this->chart['publisherName'] = 'publisherName';
        $this->chart['publisherLocation'] = 'publisherLocation';
        $this->chart['isbn'] = 'ID';
        // STATUTE
        $this->statute = $this->basic;
        $this->statute['field2'] = 'code';
        $this->statute['field5'] = 'codeNumber';
        $this->statute['field1'] = 'publicLawNumber';
        $this->statute['field3'] = 'session';
        $this->statute['field4'] = 'section';
        $this->statute['date'] = 'statuteDate';
        $this->statute['year1'] = 'statuteYear'; // override
        $this->statute['pages'] = 'pages';
        $this->statute['isbn'] = 'ID';
        // PATENT
        $this->patent = $this->basic;
        $this->patent['creator1'] = 'inventor';
        $this->patent['creator2'] = 'issuingOrganisation';
        $this->patent['creator3'] = 'agent';
        $this->patent['creator4'] = 'intAuthor';
        $this->patent['field8'] = 'patentNumber';
        $this->patent['field2'] = 'versionNumber';
        $this->patent['field3'] = 'applicationNumber';
        $this->patent['field6'] = 'intTitle';
        $this->patent['field5'] = 'intPatentNumber';
        $this->patent['field7'] = 'intClassification';
        $this->patent['field1'] = 'publishedSource';
        $this->patent['field9'] = 'legalStatus';
        $this->patent['field4'] = 'type';
        $this->patent['publisherName'] = 'assignee';
        $this->patent['publisherLocation'] = 'assigneeLocation';
        $this->patent['date'] = 'issueDate';
        $this->patent['year1'] = 'issueYear'; // override
        $this->patent['isbn'] = 'ID';
        // PERSONAL COMMUNICATION
        $this->personal = $this->basic;
        $this->personal['creator1'] = 'author';
        $this->personal['creator2'] = 'recipient';
        $this->personal['field2'] = 'type';
        $this->personal['date'] = 'date';
        $this->personal['year1'] = 'year'; // override
        $this->personal['isbn'] = 'ID';
        // PROCEEDINGS (complete set of)
        $this->proceedings = $this->basic;
        $this->proceedings['creator2'] = 'editor';
        $this->proceedings['publisherName'] = 'conferenceOrganiser';
        $this->proceedings['publisherLocation'] = 'conferenceLocation';
        $this->proceedings['date'] = 'conferenceDate';
        $this->proceedings['year2'] = 'conferenceYear';
        $this->proceedings['isbn'] = 'ISSN';
        // MUSIC ALBUM
        $this->music_album = $this->basic;
        $this->music_album['creator1'] = 'performer';
        $this->music_album['creator2'] = 'composer';
        $this->music_album['creator3'] = 'conductor';
        $this->music_album['field2'] = 'medium';
        $this->music_album['publisherName'] = 'publisherName';
        $this->music_album['isbn'] = 'ID';
        // MUSIC TRACK
        $this->music_track = $this->basic;
        $this->music_track['creator1'] = 'performer';
        $this->music_track['creator2'] = 'composer';
        $this->music_track['creator3'] = 'conductor';
        $this->music_track['collectionTitle'] = 'album';
        $this->music_track['collectionTitleShort'] = 'shortAlbum';
        $this->music_track['field2'] = 'medium';
        $this->music_track['publisherName'] = 'publisherName';
        $this->music_track['isbn'] = 'ID';
        // MUSIC SCORE
        $this->music_score = $this->basic;
        $this->music_score['creator1'] = 'composer';
        $this->music_score['creator2'] = 'editor';
        $this->music_score['field3'] = 'edition';
        $this->music_score['publisherName'] = 'publisherName';
        $this->music_score['publisherLocation'] = 'publisherLocation';
        $this->music_score['isbn'] = 'ISBN';
        // UNPUBLISHED WORK
        $this->unpublished = $this->basic;
        $this->unpublished['year1'] = 'year';
        $this->unpublished['creator1'] = 'author';
        $this->unpublished['field2'] = 'type';
        $this->unpublished['publisherName'] = 'institution';
        $this->unpublished['publisherLocation'] = 'institutionLocation';
        $this->unpublished['isbn'] = 'ID';
    }
}

