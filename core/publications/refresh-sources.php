<?php

require_once ('../../../../../wp-config.php');
require_once('../../admin/publication-sources.php');

echo var_dump(TP_Publication_Sources_Page::update_sources());
