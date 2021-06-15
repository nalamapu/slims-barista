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

// load settings
utility::loadSettings($dbs);

// set page title
$page_title = 'Barista';

// set welcome
components('welcome.php');

/* Action Area */
if (isset($_GET['action']))
{
    components('install.php');
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
<script>
    
       function navClick(e){
            
            let target = e.getAttribute('data-target')
            let navlink = document.querySelectorAll('.nav-link');

            navlink.forEach(element => {
                element.classList.remove('active')
            });

            e.classList.add('active')

            if (target !== 'default')
            {
                $('#mainContent').simbioAJAX(`<?= $_SERVER['PHP_SELF'] ?>?section=${target}`)
            }
            else
            {
                $('#mainContent').simbioAJAX(`<?= $_SERVER['PHP_SELF'] ?>`)
            }
        }

        function install(e, path, url, destBtn)
        {
            let doc = document;
            let linkToDownload = url+'/archive/refs/heads/master.zip';
            let children = e.children;
            
            doc.querySelectorAll('.actionBtn').forEach(el => {
                el.classList.add('disabled');
            })
            
            e.classList.remove('btn-primary', 'disabled');
            e.classList.add('btn-info');
            children[0].classList.remove('d-none');
            children[1].innerHTML = 'Memasang';

            fetch('<?= $_SERVER['PHP_SELF'] ?>?action=install', {
            method: 'POST',
            body: JSON.stringify({
                pathDest: path,
                urlDownload: linkToDownload
                })
            })
            .then(response => response.json())
            .then(result => {
                console.log(result);
                if (result.status)
                {
                    doc.querySelectorAll('.actionBtn').forEach(el => {
                        el.classList.remove('disabled');
                    })
                    
                    e.classList.add('btn-success');
                    e.classList.remove('btn-info');
                    children[0].classList.add('d-none');
                    children[1].innerHTML = 'Terpasang';
                }
            })
            .catch(error => {
                alert(error);
            })
        }
</script>
