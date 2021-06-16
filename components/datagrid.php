<?php
// check access
isDirect();

// set constant
define('PluginActive', ['btn-success', 'Terpasang', 'Plugin sudah aktif']);
define('PluginInstalledNotActive', ['btn-secondary', 'Terunduh', 'Plugin sudah ada, namun tidak aktif']);
define('PluginNotInstalled', ['btn-primary', 'Pasang', 'Klik untuk memasang']);

// table spec
$table_spec = 'barista_files';
// membuat datagrid
$datagrid = new simbio_datagrid();
// set column
$datagrid->setSQLColumn('raw as Deskripsi, id as Aksi, last_update as "Terakhir diperbaharui", register_date as "Taggal Register"');


function isPluginActive($db, $id)
{
    // get options
    $id = (int)$id;
    $data = $db->query('select options from barista_files where id = '.$id.' and options is not null');

    if ($data->num_rows > 0)
    {
        $result = $data->fetch_row();
        $meta = json_decode($result[0], TRUE);

        if (file_exists($meta['path']))
        {
            $plugin = $db->query('select id from plugins where id = \''.$db->escape_string($meta['id']).'\'');
            return ($plugin->num_rows) ? PluginActive : PluginInstalledNotActive;
        }
        return PluginInstalledNotActive;
    }

    return PluginNotInstalled;

}

/**
 * Modify Column Content
 */
function setupActionButton($db, $column)
{
    // decoding
    $data = json_decode($column[0], true);
    // path name
    $getPathName = explode('/', str_replace(['http://','https://'], '', $data['PluginURI']));
    // fix path
    $path = $getPathName[(count($getPathName) - 1)];
    // Branch
    $branch = $data['Branch'];
    // set button prop
    $button = isPluginActive($db, $column[1]);
    // Plugin URL
    $pluginURL = rtrim($data['PluginURI']);
    // set out
    $buffer = <<<HTML
            <button class="btn {$button[0]} actionBtn" title="{$button[2]}"><span class="d-inline-block">{$button[1]}</span></button></button>'
    HTML;
    if ($button[1] !== 'Terpasang')
    {
        $buffer = <<<HTML
            <button class="btn {$button[0]} actionBtn" title="{$button[2]}" onclick="install(this, '{$path}', '{$pluginURL}', '{$branch}')">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="spinLoader d-none" for="{$column[1]}" style="margin: auto;" width="25px" height="25px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                    <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e0e0e0" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                </circle>
            </svg>    
            <span class="d-inline-block">{$button[1]}</span></button>
        HTML;
    }

    return $buffer;
}
$datagrid->modifyColumnContent(1, 'callback{setupActionButton}');

function setUpDescription($db, $data)
{
    // extracting json data
    $decodedData = json_decode($data[0], TRUE);
    // modify string of plugin name
    $pluginName = ucwords(str_replace('_', ' ', $decodedData['PluginName']));
    // filtering destriction
    $description = strip_tags($decodedData['Description']);
    // set version variable
    $version = $decodedData['Version'];
    // Author in html
    $author = '<a class="notAJAX" target="_blank" href="'.$decodedData['AuthorURI'].'">'.$decodedData['Author'].'</a>';
    // decision Icon type
    $icon = ($decodedData['Type'] === 'Plugin') ? 'puzzle-op.png' : 'puzzle-op.png';
    // set up template
    $template = <<<HTML
        <section>
            <div class="d-inline-block">
                <img style="width: 50px; margin-left: 10px; margin-right: 10px; margin-top: -50px" src="modules/barista/{$icon}"/>
            </div>
            <div class="d-inline-block">
                <h6><a class="notAJAX w-full block" target="_blank" href="{$decodedData['PluginURI']}"><b>{$pluginName}</b></a></h6>
                <p class="w-full block text-justify mb-0">{$description}</p>
                <span class="d-inline-block">{$author}</span> :: <span class="d-inline-block text-danger">{$version}</span>
            </div>
        </section>
    HTML;

    return $template;
}
$datagrid->modifyColumnContent(0, 'callback{setUpDescription}');

// Search action
if (isset($_GET['keywords']) AND $_GET['keywords']) 
{
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $criteria = ' raw LIKE "%'.$keywords.'%"';
    // jika ada keywords maka akan disiapkan criteria nya
    $datagrid->setSQLCriteria($criteria);
}

/**
 * Ordering
 */
$datagrid->setSQLorder('raw ASC');

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
/* End datagrid */

// respect for Freepik :)
echo '<div style="margin: 20px">Icons made by <a class="notAJAX" href="https://www.freepik.com" title="Freepik">Freepik</a> from <a class="notAJAX" href="https://www.flaticon.com/free-icon/puzzle_734034?related_id=734034&origin=search" title="Flaticon">www.flaticon.com</a></div>';
