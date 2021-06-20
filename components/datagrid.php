<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-19 13:13:28
 * @modify date 2021-06-19 13:13:28
 * @desc [description]
 */

// check access
isDirect();

echo <<<HTML
    <div class="w-100 block p-3 text-white">
        <button onclick="getLastListApp(this)" class="btn btn-danger float-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1h-2z"/>
                <path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="spinLoader d-none" style="margin: auto;" width="20" height="20" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e0e0e0" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
                <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                </circle>
            </svg> 
            <span>Perbaharui daftar</span>
        </button>
    </div>
HTML;

// set constant
define('PluginActive', ['btn-success', 'Terpasang', 'Plugin sudah aktif']);
define('PluginInstalledNotActive', ['btn-secondary', 'Terunduh', 'Plugin sudah ada, namun tidak aktif']);
define('PluginNotInstalled', ['btn-primary', 'Pasang', 'Klik untuk memasang']);
define('PluginCorrupted', ['btn-danger', 'Plugin Korup', 'Kemungkinan plugin terhapus atau lain sebab (Klik untuk memasang kembali).']);

// table spec
$table_spec = 'barista_files';
// membuat datagrid
$datagrid = new simbio_datagrid();
// set column
$datagrid->setSQLColumn('raw as Deskripsi, id as Aksi, last_update as "Terakhir diperbaharui", register_date as "Taggal Register"');

/**
 * Modify Column Content
 */
/**
 * isPluginActive
 *
 * @param object $db
 * @param integer $id
 * @return Array
 */
function isPluginActive(object $db, int $id, string $path, string $url)
{
    // get options
    $id = (int)$id;
    $data = $db->query('select options from barista_files where id = '.$id.' and options != \'\'');

    if ($data->num_rows > 0)
    {
        $result = $data->fetch_row();
        $meta = json_decode($result[0], TRUE);

        if (isset($meta['path']) && file_exists($meta['path']))
        {
            $plugin = $db->query('select id from plugins where id = \''.$db->escape_string($meta['id']).'\'');
            return ($plugin->num_rows) ? PluginActive : PluginInstalledNotActive;
        }
        return PluginCorrupted;
    }
    else
    {
        return isPluginExistsBeforeBarista($db, $id, $path, $url);
    }

    return PluginNotInstalled;
}

function isPluginExistsBeforeBarista(object $db, int $baristaId, string $path, string $url)
{
    // check in plugin table
    $pluginQuery = $db->query('select id, path, options from plugins where path like "%'.$db->escape_string($path).'%"');

    if ($pluginQuery->num_rows === 1)
    {
        $data = $pluginQuery->fetch_assoc();
        $options = json_decode($data['options'], TRUE);
        $baristaId = (int)$baristaId;
        $baristaOptions = $db->escape_string(json_encode(['id' => $data['id'], 'path' => $data['path'], 'version' => $options['version']]));
        return ($db->query('update barista_files set options = \''.$baristaOptions.'\' where id ='.$baristaId)) ? PluginActive : PluginInstalledNotActive;
    }
    else
    {
        // set up plugin instances
        $pluginInstance = SLiMS\Plugins::getInstance();
        // grab meta plugin -> took form plugins.php
        $metaObjectPlugin = array_filter($pluginInstance->getPlugins(), function ($plugin) use ($url) {
            return $plugin->uri === $url;
        });
        // get plugin id
        $getId = array_keys($metaObjectPlugin);

        if (count($getId))
        {
            $id = $getId[0];
            $meta = $metaObjectPlugin[$id];
            $baristaOptions = $db->escape_string(json_encode(['id' => $id, 'path' => $meta->path, 'version' => $meta->version]));
            return ($db->query('update barista_files set options = \''.$baristaOptions.'\' where id ='.$baristaId)) ? PluginInstalledNotActive : PluginNotInstalled;
        }
    }

    return PluginNotInstalled;
}

/**
 * setupActionButton
 *
 * @param object $db
 * @param array $column
 * @return void
 */
function setupActionButton(object $db, array $column)
{
    // decoding
    $data = json_decode($column[0], true);
    // path name
    $getPathName = explode('/', str_replace(['http://','https://'], '', $data['PluginURI']));
    // fix path
    $path = $getPathName[(count($getPathName) - 1)];
    // Branch
    $branch = $data['Branch'];
    // Plugin URL
    $pluginURL = rtrim($data['PluginURI']);
    // set button prop
    $button = isPluginActive($db, $column[1], $path, $pluginURL);
    // set out
    $buffer = <<<HTML
            <button class="btn {$button[0]} actionBtn" title="{$button[2]}"><span class="d-inline-block">{$button[1]}</span></button></button>'
    HTML;
    if (!in_array($button[1], ['Terpasang','Terunduh']))
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

/**
 * seUpDescription
 *
 * @param object $db
 * @param array $data
 * @return void
 */
function setUpDescription(object $db, array $data)
{
    // extracting json data
    $decodedData = json_decode($data[0], TRUE);
    // modify string of plugin name
    $pluginName = ucwords(str_replace('_', ' ', $decodedData['PluginName']));
    // filtering destriction
    $description = substr(strip_tags($decodedData['Description']), 0,80);
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
                <p style="max-width: 550px" class="block text-justify mb-0">{$description}</p>
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
