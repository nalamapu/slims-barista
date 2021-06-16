<?php

use SLiMS\DB;
use SLiMS\Plugins;

/**
 * isDirect
 * check direct access
 * @return void
 */
function isDirect()
{
    if (!defined('INDEX_AUTH')) die('No direct access');
}

/**
 * Components
 * load php components with multiple load type
 * 
 * @param string $filename
 * @param string $componentDir
 * @param string $loadType
 * 
 * @return mix
 */
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


/**
 * File loader
 * 
 * @param string $filename
 * @param string $type
 * 
 * @return void
 */
function fileLoader(string $filename, string $type = 'include_once')
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


/**
 * unsetPost
 * 
 * unset multiple key in a post input
 * 
 * @param array $arrayToUnset
 * 
 * @return array
 */
function unsetPost(array $arrayToUnset)
{
    foreach ($arrayToUnset as $key) {
        unset($_POST[$key]);
    }

    return $_POST;
}


/**
 * baristaMigration
 * 
 * simple migration to create barista_files table
 * 
 * @param object $sqlOp
 * @param bool $generateLocalAvailablePlugin
 * 
 * @return void
 */
function baristaMigration(object $sqlOp, bool $generateLocalAvailablePlugin = true)
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


/**
 * simbioaRedirect
 * 
 * redirect page with simbioAJAX library
 * 
 * @param string $destionationUrl
 * @param string $selector
 * 
 * @return string
 */
function simbioRedirect(string $destionationUrl, string $selector = '#mainContent')
{
    echo <<<HTML
        <script>
            parent.$('{$selector}').simbioAJAX('{$destionationUrl}');
        </script>
    HTML;
}


/**
 * Check php version 
 * 
 * @return void
 */
function phpVersionCheck()
{
    if (version_compare(PHP_VERSION, '7.4', '<'))
    {
        components('banner.php');
        exit('<div class="bg-danger text-white p-2 h6 font-weight-bold">Versi PHP anda kurang dari 7.4</div>');
    }
}


/**
 * Curl Check
 * @return void
 */
function curlCheck()
{
    if (!function_exists('curl_init'))
    {
        components('banner.php');
        exit('<div class="bg-danger text-white p-2 h6 font-weight-bold">Extension cURL tidak terinstall, install terlebih dahulu ekstensi tersebut.</div>');
    }
}


/**
 * Download file via CURL
 * 
 * @param string $url
 * @param string $filepath
 * @Source from https://subinsb.com/php-download-extract-zip-archives/
 * 
 * @return array
 */
function downloadPlugin(string $url, string $filepath) {
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


/**
 * @param string $file
 * @param string $extract_to
 * @source Modified from https://www.php.net/manual/en/ziparchive.extractto.php
 * 
 * @return boolean
 */
function extractZip(string $file, string $extract_to)
{
    global $sysconf;

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res === TRUE) {
        $zip->extractTo($extract_to);
        $zip->close();
        if ($sysconf['barista']['make_cache'] === 't')
        {
            // remove file zip
            unlink($file);
        }
        return true;
    } else {
        return false;
    }
}


/**
 * Renaming folder dest
 * 
 * @param boolean $zip
 * @param string $namepath
 * @param string $branch
 * 
 * @return boolean
 */
function renamingZipDir(bool $zip, string $namepath, string $branch, string $urlDownload)
{
    global $sysconf;

    if ($zip)
    {
        if (rename(SB.'plugins/'.$namepath.'-'.$branch, SB.'plugins/'.$namepath))
        {
            $url = substr($urlDownload, 0, strpos($urlDownload, '/archive'));

            if ($sysconf['barista']['auto_active'] === 'y')
            {
                try {
                   activatingPlugin($url);
               } catch (Exception $exception) {
                   echo json_encode(['status' => false, 'message' => $exception->getMessage()]);
                   exit;
               }
            }
            else
            {
                echo json_encode(['status' => true, 'message' => 'Plugin berhasil diinstall']);
                exit;
            }
        }
    }
    return ['status' => $zip, 'msg' => 'Gagal mengekstrak zip, folder sudah ada atau folder korup!'];
}

function activatingPlugin(string $url)
{
    /**
     * Some modification from admin/modules/system/plugins.php
     */
    // set up plugin instances
    $pluginInstance = Plugins::getInstance();
    // grab meta plugin -> took form plugins.php
    $metaObjectPlugin = array_filter($pluginInstance->getPlugins(), function ($plugin) use ($url) {
        return $plugin->uri === $url;
    });
    // get plugin id
    $id = array_keys($metaObjectPlugin)[0] ?? die(json_encode(['status' => false, 'msg' => 'Plugin not found']));
    // set plugin
    $plugin = $metaObjectPlugin[$id];

    // active query
    $activeQuery = DB::getInstance()->prepare('INSERT INTO plugins (id, path, options, created_at, deleted_at, uid) VALUES (:id, :path, :options, :created_at, :deleted_at, :uid)');

    if (preg_replace('/[^0-9]/', '', SENAYAN_VERSION_TAG) >= '940')
    {
        if ($pluginInstance->isActive($id))
            $activeQuery = DB::getInstance()->prepare('UPDATE `plugins` SET `path` = :path, `options` = :options, `updated_at` = :created_at, `deleted_at` = :deleted_at, `uid` = :uid WHERE `id` = :id');

        $options = ['version' => $plugin->version];
        // run migration if available
        if ($plugin->migration->is_exist) {
            $options[Plugins::DATABASE_VERSION] = SLiMS\Migration\Runner::path($plugin->path)->setVersion($plugin->migration->{Plugins::DATABASE_VERSION})->runUp();
            $activeQuery->bindValue(':options', json_encode($options));
        } else {
            $activeQuery->bindValue(':options', null);
        }

        $activeQuery->bindValue(':created_at', date('Y-m-d H:i:s'));
        $activeQuery->bindValue(':deleted_at', null);
    }

    $activeQuery->bindValue(':id', $id);
    $activeQuery->bindValue(':path', $plugin->path);
    $activeQuery->bindValue(':uid', $_SESSION['uid']);
    $message = sprintf(__('Plugin %s enabled'), $plugin->name);

    $run = $activeQuery->execute();

    if ($run) {
        echo json_encode(['status' => true, 'message' => $message]);
    } else {
        echo json_encode(['status' => false, 'message' => DB::getInstance()->errorInfo()]);
    }
    exit;
}


/**
 * Get POST data from RAW input
 * 
 * @return string
 */
function getRawPost()
{
    return json_decode(file_get_contents('php://input'), TRUE);
}


/**
 * Create barista cache dir for downloaded .zip
 * 
 * @return void
 */
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


/**
 * @param string $pathname
 * 
 * @return [type]
 */
function createPluginDir(string $pathname)
{
    if (!file_exists(SB.'plugins/' . basename($pathname)))
    {
        if (!mkdir(SB.'plugins/' . basename($pathname), 0755, TRUE))
        {
            utility::jsToastr('Galat', 'Folder plugins tidak dapat ditulis. Pastikan anda sudah mengatur folder tersebut dapat di tulis oleh PHP.', 'error');
            exit;
        }
    }
}

function searchPlugin(string $dest)
{
    $dir = scandir($dest);
}


/**
 * Just for debugging
 * @return mix
 */
function test()
{
    activatingPlugin('https://github.com/idoalit/label_barcode');
}


function dd($mix, bool $exit = true)
{
    echo '<pre>';
    var_dump($mix);
    echo '</pre>';

    if ($exit) exit;
}

// run php check
phpVersionCheck();

// curl check
curlCheck();