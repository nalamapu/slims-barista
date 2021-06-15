<?php

function isDirect()
{
    if (!defined('INDEX_AUTH')) die('No direct access');
}

function components(string $filename, string $componentDir = 'components', string $loadType = 'include_once')
{
    if ($componentDir === 'components' && (file_exists(__DIR__ . DS . $componentDir . DS . $filename)))
    {
        fileLoader(__DIR__ . DS . $componentDir . DS . $filename);
    }
    else if ($componentDir !== 'components')
    {
        fileLoader($componentDir . DS . $filename, $loadType);
    }
    else
    {
        echo '<div class="w-100 bg-danger text-white p-3">Komponen tidak ditemukan!</div>';
    }
}

function fileLoader($filename, $type = 'include_once')
{
    global $sysconf,$dbs,$page_title;

    switch ($type) {
        case 'include':
            include $filename;
            break;
        
        case 'require':
            require $filename;
            break;

        case 'require_once':
            require_once $filename;
            break;

        default:
            include_once $filename;
            break;
    }
}

function unsetPost($arrayToUnset)
{
    foreach ($arrayToUnset as $key) {
        unset($_POST[$key]);
    }

    return $_POST;
}

function baristaMigration($sqlOp, $generateLocalAvailablePlugin = true)
{
    global $dbs;

    // make table
    if (!$dbs->query(file_get_contents(__DIR__  . '/migration.sql'))) 
    {
        utility::jsAlert($dbs->error);
        exit;
    }

    if ($generateLocalAvailablePlugin && file_exists(__DIR__ . '/barista-plugin-local.json'))
    {
        $data = json_decode(file_get_contents(__DIR__ . '/barista-plugin-local.json'), TRUE);

        foreach ($data as $index => $plugin) {
            @$sqlOp->insert('barista_files', ['raw' => json_encode($plugin), 'register_date' => date('Y-m-d H:i:s'), 'last_update' => date('Y-m-d H:i:s')]);
        }
    }
}

function simbioRedirect($destionationUrl, $selector = '#mainContent')
{
    echo <<<HTML
        <script>
            parent.$('{$selector}').simbioAJAX('{$destionationUrl}');
        </script>
    HTML;
}

function phpVersionCheck()
{
    if (version_compare(PHP_VERSION, '7.4', '<'))
    {
        exit('Versi PHP anda kurang dari 7.3');
    }
}

// Source https://subinsb.com/php-download-extract-zip-archives/
function downloadPlugin($url, $filepath) {
    $zipResource = fopen($filepath, "w");
    // Get The Zip File From Server
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($ch, CURLOPT_FILE, $zipResource);
    $page = curl_exec($ch);
    if(!$page) {
        return ['status' => false, 'msg' => "Error :- ".curl_error($ch)];
    }
    curl_close($ch);

    return (filesize($filepath) > 0)? ['status' => true, 'msg' => 'OK'] : ['status' => false, 'msg' => "Error : gagal mendownload, mungkin file korup"];
}

function getRawPost()
{
    return json_decode(file_get_contents('php://input'), TRUE);
}

function createCacheDir()
{
    if (!file_exists(SB.'plugins/baristaCache/'))
    {
        if (!mkdir(SB.'plugins/baristaCache/', 0755, TRUE))
        {
            utility::jsToastr('Galat', 'Folder plugins tidak dapat ditulis. Pastikan anda sudah mengatur folder tersebut dapat di tulis oleh PHP.', 'error');
            exit;
        }
    }
}

function test()
{
    $data = json_decode(file_get_contents(__DIR__ . '/barista-plugin-local.json'), TRUE);

    foreach ($data as $index => $plugin) {
        $data[$index]['Type'] = 'Plugin';
    }

    echo json_encode($data);
    exit;
}

phpVersionCheck();