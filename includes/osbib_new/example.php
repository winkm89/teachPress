<?php
include('core.php');

$resourceArray = array(
    'author' => 'Mark Grimshaw and Christian Boulanger',
    'title' => 'How Bibliographies Ruined our Lives',
    'year' => '2005',
    'volume' => '20',
    'number' => '4',
    'journal' => 'Journal of Mundane Trivia',
    'pages' => '42--111',
    'howpublished' => "\url{http://bibliophile.sourceforge.net}",
);

/** The new one */
$new_bibformat = new osbib_bibformat(true);
list($info, $citation, $footnote, $common, $types) = $new_bibformat->loadStyle("APA");
$new_bibformat->getStyle($common, $types, $footnote);
$new_bibformat->preProcess('article', $resourceArray);
echo $new_bibformat->map();