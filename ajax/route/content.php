<?php
ob_start();
$route = trim(strip_tags(filter_input(INPUT_POST, 'src', FILTER_DEFAULT)));
require_once PATH_HOME . $route;

$data['data'] = ob_get_contents();
ob_end_clean();