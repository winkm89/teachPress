<?php

require_once ('../../../../../wp-config.php');
require_once('../../admin/publication-sources.php');

header('Content-Type: application/json; charset=utf-8');
echo json_encode(TP_Publication_Sources_Page::update_sources());
