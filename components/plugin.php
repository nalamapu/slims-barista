<?php
// check access
isDirect();

// table spec
$table_spec = 'plugins';
// membuat datagrid
$datagrid = new simbio_datagrid();
// set column
$datagrid->setSQLColumn('path as Plugin, id as Aksi, created_at as "Dipasang"');

// Search action
if (isset($_GET['keywords']) AND $_GET['keywords']) 
{
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $criteria = ' path LIKE "%'.$keywords.'%"';
    // jika ada keywords maka akan disiapkan criteria nya
    $datagrid->setSQLCriteria($criteria);
}

/**
 * Ordering
 */
$datagrid->setSQLorder('path ASC');

// Modification
function setUpDescription($db, $data)
{
    // extracting json data
    $decodedData = getActivePluginInfo($data[0]);
    // modify string of plugin name
    $pluginName = ucwords(str_replace('_', ' ', $decodedData['name']));
    // filtering destriction
    $description = strip_tags($decodedData['description']);
    // set version variable
    $version = $decodedData['version'];
    // Author in html
    $author = '<a class="notAJAX" target="_blank" href="'.$decodedData['author_uri'].'">'.$decodedData['author'].'</a>';
    // decision Icon type
    $icon = 'puzzle-op.png';
    // set up template
    $template = <<<HTML
        <section>
            <div class="d-inline-block">
                <img style="width: 50px; margin-left: 10px; margin-right: 10px; margin-top: -50px" src="modules/barista/{$icon}"/>
            </div>
            <div class="d-inline-block">
                <h6><a class="notAJAX w-full block" target="_blank" href="{$decodedData['uri']}"><b>{$pluginName}</b></a></h6>
                <p class="w-full block text-justify mb-0">{$description}</p>
                <span class="d-inline-block">{$author}</span> :: <span class="d-inline-block text-danger">{$version}</span>
            </div>
        </section>
    HTML;

    return $template;
}

function getActivePluginInfo($path)
{
    // took from lib/Plugins.php 
    $file_open = fopen($path, 'r');
    $raw_data = fread($file_open, 8192);
    fclose($file_open);

    // store plugin info as object
    $plugin = [];

    // parsing plugin data
    preg_match('|Plugin Name:(.*)$|mi', $raw_data, $plugin['name']);
    preg_match('|Plugin URI:(.*)$|mi', $raw_data, $plugin['uri']);
    preg_match('|Version:(.*)|i', $raw_data, $plugin['version']);
    preg_match('|Description:(.*)$|mi', $raw_data, $plugin['description']);
    preg_match('|Author:(.*)$|mi', $raw_data, $plugin['author']);
    preg_match('|Author URI:(.*)$|mi', $raw_data, $plugin['author_uri']);

    foreach ($plugin as $key => $val) {
        $plugin[$key] = isset($val[1]) && trim($val[1]) !== '' ? trim($val[1]) : null;
    }

    $plugin['id'] = md5($path);
    $plugin['path'] = $path;

    return $plugin;
}

$datagrid->modifyColumnContent(0, 'callback{setUpDescription}');

function setActionButton($db, $data)
{
    return <<<HTML
        <button class="btn btn-primary" data-hash="{$data[1]}"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/>
            </svg>
            Cek Pembaharuan
        </button>
        <button class="btn btn-danger" data-hash="{$data[1]}"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
            </svg>
            Hapus
        </button>
    HTML;
}

$datagrid->modifyColumnContent(1, 'callback{setActionButton}');
// end modification

// set table and table header attributes
$datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
$datagrid->table_name = 'memberList';
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false); // object database, spesifikasi table, jumlah data yang muncul, boolean penentuan apakah data tersebut dapat di edit atau tidak.
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
    echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
}
// menampilkan datagrid
echo $datagrid_result;

// respect for Freepik :)
echo '<div style="margin: 20px">Icons made by <a class="notAJAX" href="https://www.freepik.com" title="Freepik">Freepik</a> from <a class="notAJAX" href="https://www.flaticon.com/free-icon/puzzle_734034?related_id=734034&origin=search" title="Flaticon">www.flaticon.com</a></div>';
