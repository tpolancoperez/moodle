<?php

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(__FILE__) . '/lib.php';

print_r(elluminatelive_list_recordings('meetingId = 1282935356278'));

?>