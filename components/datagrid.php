<?php
// check access
isDirect();

/* Datagrid area */
/**
 * table spec
 * ---------------------------------------
 * Tuliskan nama tabel pada variabel $table_spec. Apabila anda 
 * ingin melakukan pegabungan banyak tabel, maka anda cukup menulis kan
 * nya saja layak nya membuat query seperti biasa
 *
 * Contoh :
 * - dummy_plugin as dp left join non_dummy_plugin as ndp on dp.id = ndp.id ... dst
 *
 */
$table_spec = 'barista_files';

// membuat datagrid
$datagrid = new simbio_datagrid();

/** 
 * Menyiapkan kolom
 * -----------------------------------------
 * Format penulisan sama seperti anda menuliskan di query pada phpmyadmin/adminer/yang lain,
 * hanya di SLiMS anda diberikan beberapa opsi seperti, penulisan dengan gaya multi parameter,
 * dan gaya single parameter.
 *
 * Contoh :
 * - Single Parameter : $datagrid->setSQLColumn('id', 'kolom1, kolom2, kolom3'); // penulisan langsung
 * - Single Parameter : $datagrid->setSQLColumn('id', 'kolom1', 'kolom2', 'kolom3'); // penulisan secara terpisah
 *
 * Catatan :
 * - Jangan lupa menyertakan kolom yang bersifat PK (Primary Key) / FK (Foreign Key) pada urutan pertama,
 *   karena kolom tersebut digunakan untuk pengait pada proses lain.
 */
$datagrid->setSQLColumn('raw as Deskripsi, id as Aksi, last_update as "Terakhir diperbaharui", register_date as "Taggal Register"');

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
    // set button prop
    $button = (is_dir($path) && file_exists($path)) ?  ['btn-success', 'Terpasang'] : ['btn-primary', 'Pasang'];
    // set out
    return '<button class="btn '.$button[0].' actionBtn" onclick="install(this, \''.$path.'\', \''.rtrim($data['PluginURI'], '/').'\')">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="spinLoader d-none" for="'.$column[1].'" style="margin: auto;" width="25px" height="25px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
        <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e0e0e0" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
        </circle>
    </svg>    
    <span class="d-inline-block">'.$button[1].'</span></button>';
}
$datagrid->modifyColumnContent(1, 'callback{setupActionButton}');

function setUpDescription($db, $data)
{
    $decodedData = json_decode($data[0], TRUE);
    $pluginName = ucwords(str_replace('_', ' ', $decodedData['PluginName']));
    $description = strip_tags($decodedData['Description']);
    $version = $decodedData['Version'];
    $author = '<a class="notAJAX" target="_blank" href="'.$decodedData['AuthorURI'].'">'.$decodedData['Author'].'</a>';
    $icon = ($decodedData['Type'] === 'Plugin') ? 'puzzle-op.png' : 'puzzle-op.png';
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

/* End modify column content */
/** 
 * Pencarian data
 * ------------------------------------------
 * Bagian ini tidak lepas dari nama kolom dari tabel yang digunakan.
 * Jadi, untuk pencarian yang lebih banyak anda dapat menambahkan kolom pada variabel
 * $criteria
 *
 * Contoh :
 * - $criteria = ' kolom1 = "'.$keywords.'" OR kolom2 = "'.$keywords.'" OR kolom3 = "'.$keywords.'"';
 * - atau anda bisa menggunakan query anda.
 */
if (isset($_GET['keywords']) AND $_GET['keywords']) 
{
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $criteria = ' raw LIKE "%'.$keywords.'%"';
    // jika ada keywords maka akan disiapkan criteria nya
    $datagrid->setSQLCriteria($criteria);
}

/** 
 * Atribut tambahan
 * --------------------------------------------
 * Pada bagian ini anda dapat menentukan atribut yang akan muncul pada datagrid
 * seperti judul tombol, dll
 */
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
echo '<div style="margin: 20px">Icons made by <a class="notAJAX" href="https://www.freepik.com" title="Freepik">Freepik</a> from <a class="notAJAX" href="https://www.flaticon.com/free-icon/puzzle_734034?related_id=734034&origin=search" title="Flaticon">www.flaticon.com</a></div>';
