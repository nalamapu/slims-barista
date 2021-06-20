<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-15 10:39:44
 * @modify date 2021-06-15 10:39:44
 * @licesense GPLv3
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// Barista Version
define('BARISTA_VERSION', '1.0.0-beta-2');

// load settings
utility::loadSettings($dbs);

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
// set dependency
require SB.'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require __DIR__ . '/helper.php';
// end dependency

if (isset($_GET['test']))
{
    test();
}

// privileges checking
$can_read = utility::havePrivilege('barista', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// set page title
$page_title = 'Barista';

// set welcome
components('welcome.php');

/* Action Area */
if (isset($_GET['action']))
{
    $actionType = replaceString($_GET['action'], 'alpha');
    fileLoader(__DIR__ . '/actions/'.$actionType.'.php');
    exit;
}
/* End Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <!-- Banner -->
        <?php components('banner.php') ?>
        <!-- Form -->
        <?php components('form.php') ?>
        <!-- Navbvar -->
        <?php components('navbar.php') ?>
    </div>
</div>
<div class="result">
<?php
switch (true)
{
    case (isset($_GET['section'])):
        components(preg_replace('/[^A-Za-z\/]/i', '', $_GET['section']) . '.php');
        break;
    default:
        components('datagrid.php');
        break;
}
?>
</div>
<script with-url="yes" http-url="<?= $_SERVER['PHP_SELF'] ?>" src="<?= AWB ?>modules/barista/barista.js"></script>
