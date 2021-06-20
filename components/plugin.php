<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-19 13:16:31
 * @modify date 2021-06-19 13:16:31
 * @desc [description]
 */

 // check access
isDirect();

// table spec
$table_spec = 'barista_files';
// membuat datagrid
$datagrid = new simbio_datagrid();
// set column
$datagrid->setSQLColumn('raw as Plugin, options as Status, options as Aksi, register_date as "Dipasang"');

// Search action
$criteria = 'options != \'\' and '.jsonCriteria('raw', '$.Type', 'Plugin');

if (isset($_GET['keywords']) AND $_GET['keywords']) 
{
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $criteria .= ' and raw LIKE "%'.$keywords.'%"';
}

// jika ada keywords maka akan disiapkan criteria nya
$datagrid->setSQLCriteria($criteria);

/**
 * Ordering
 */
$datagrid->setSQLorder('register_date ASC');

// Modification
/**
 * setUpDescription
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
    $icon = 'puzzle-op.png';
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

/**
 * isPluginActive
 *
 * @param object $db
 * @param string $id
 * @return boolean
 */
function isPluginActive(object $db, string $id)
{
    // check plugin active;
    $pluginId = $db->escape_string($id);
    $active = $db->query('select id from plugins where id = \''.$id.'\'')->num_rows;

    return $active;
}

/**
 * setStatus
 *
 * @param object $db
 * @param array $data
 * @return void
 */
function setStatus(object $db, array $data)
{
    // decoding
    $options = json_decode($data[1], TRUE);
    if (isset($options['path']) && file_exists($options['path']))
    {
        // check plugin active;
        if (isPluginActive($db, $options['id']))
        {
            return '<span class="btn bg-success p-1 text-white">Aktif</span>';
        }
        return '<span class="btn bg-danger p-1 text-white">Tidak Aktif</span>';
    }
    // other
    return '<button title="Kemungkinan folder plugin hilang, atau terhapus" class="btn btn-danger">Plugin Korup</button>';
}
$datagrid->modifyColumnContent(1, 'callback{setStatus}');

/**
 * btnAction
 *
 * @param object $db
 * @param array $data
 * @return void
 */
function btnAction(object $db, array $data)
{
    // decoding
    $options = json_decode($data[2], TRUE);
    if (isset($options['path']) && file_exists($options['path']))
    {
        // button active
        $buttonStatus = (isPluginActive($db, $options['id'])) ? ['btn-warning', 'Non-aktifkan'] : ['btn-success', 'Aktfikan'];
        // set button
        return <<<HTML
            <button class="btn btn-primary my-2 d-block" onclick="toastr.info('Belum tersedia pada fitur ini', 'Info')" data-id="{$options['id']}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                </svg>
                <span>Perbaharui</span>
            </button>
            <button class="btn {$buttonStatus[0]} my-2 d-block" onclick="setStatusPlugin(this)" data-id="{$options['id']}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-power" viewBox="0 0 16 16">
                    <path d="M7.5 1v7h1V1h-1z"/>
                    <path d="M3 8.812a4.999 4.999 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812z"/>
                </svg>
                <span>{$buttonStatus[1]}</span>
            </button>
            <button class="btn btn-danger my-2 d-block" onclick="deletePlugin(this)" data-id="{$options['id']}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
                <span>Hapus</span>
            </button>
        HTML;
    }
}
$datagrid->modifyColumnContent(2, 'callback{btnAction}');
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
