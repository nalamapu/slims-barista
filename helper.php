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
 * isBulian
 * 
 * Check SLiMS 9 Bulian version with comparasion
 *
 * @param integer $minimumVersion
 * @return boolean
 */
function isBulian(int $minimumVersion)
{
    if (preg_replace('/[^0-9]/', '', SENAYAN_VERSION_TAG) >= $minimumVersion)
    {
        return true;
    }
    return false;
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
    if ($dbs->query('show tables like \'barista_files\'')->num_rows === 0) 
    {
        if (!$dbs->query(file_get_contents(__DIR__  . '/migration.sql')))
        {
            utility::jsAlert($dbs->error);
            exit;
        }
    }

    if ($generateLocalAvailablePlugin && file_exists(__DIR__ . '/barista-plugin-local.json'))
    {
        $data = json_decode(file_get_contents(__DIR__ . '/barista-plugin-local.json'), TRUE);
        $query = [];
        foreach ($data as $index => $plugin) {
            $id = ($index + 1);
            if (!localIdExists([$dbs, $sqlOp], $id, $plugin))
            {
                @$sqlOp->insert('barista_files', ['id' => $id, 'raw' => $dbs->escape_string(json_encode($plugin)), 'register_date' => date('Y-m-d H:i:s'), 'last_update' => date('Y-m-d H:i:s')]);
                $query[] = $sqlOp->getSQL();
            }
        }
    }
}

/**
 * localIdExists
 *
 * @param array $objectInArray
 * @param integer $id
 * @param array $data
 * @return bool
 */
function localIdExists(array $objectInArray, int $id, array $data)
{
    global $dbs;

    // filtering
    $baristaId = replaceString($id, 'num');
    // check query
    $checkId = $objectInArray[0]->query('select id from barista_files where id = '.$baristaId);

    if ($checkId->num_rows === 1)
    {
        $objectInArray[1]->update('barista_files', ['raw' => $dbs->escape_string(json_encode($data)), 'last_update' => date('Y-m-d H:i:s')], 'id='.$baristaId);
        return true;
    }
    // set false
    return false;
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
 * Modules directory Check
 *
 * @return void
 */
function modulesDirCheck()
{
    if (!is_writable(SB.'admin'.DS.'modules'.DS))
    {
        components('banner.php');
        exit('<div class="bg-danger text-white p-2 h6 font-weight-bold">Direktori '.SB.'admin'.DS.'modules'.DS.' tidak dapat ditulis.</div>');
    }
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
 * @return string
 */
function renamingZipDir(bool $zip, string $namepath, string $branch, string $urlDownload, int $baristaId)
{
    global $sysconf;

    if ($zip)
    {
        // delete and overwrite
        if ($sysconf['barista']['overwrite'] === 'y' && file_exists(SB.'plugins/'.$namepath))
        {
            @rrmdir(SB.'plugins/'.$namepath);
        }

        // renaming folder
        if (rename(SB.'plugins/'.$namepath.'-'.$branch, SB.'plugins/'.$namepath))
        {
            $url = substr($urlDownload, 0, strpos($urlDownload, '/archive'));

            try {
                activatingPlugin($url, $baristaId);
            } catch (Exception $exception) {
                responseJson(['status' => false, 'message' => $exception->getMessage()]);
                exit;
            }
        }
    }
    responseJson(['status' => $zip, 'msg' => 'Gagal mengekstrak zip, folder sudah ada atau folder korup!']);
    exit;
}

/**
 * Activating Plugin
 *
 * @param string $url
 * @param integer $baristaId
 * @return string
 */
function activatingPlugin(string $url, int $baristaId)
{
    global $sysconf;

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
    $id = array_keys($metaObjectPlugin)[0] ?? die(responseJson(['status' => false, 'msg' => 'Plugin not found']));
    // set plugin
    $plugin = $metaObjectPlugin[$id];

        // check auto active
    if ($sysconf['barista']['auto_active'] === 't')
    {
        // setup options
        $baristaOptions = DB::getInstance()
                        ->prepare('update barista_files set options = ? where id = ?');

        $baristaOptions->execute([json_encode(['id' => $id, 'path' => $plugin->path, 'version' => $plugin->version]), $baristaId]);
        // set response
        responseJson(['status' => true, 'message' => 'Plugin berhasil diinstall']);
        exit;
    }

    // active query
    $activeQuery = DB::getInstance()->prepare('INSERT INTO plugins (id, path, options, created_at, deleted_at, uid) VALUES (:id, :path, :options, :created_at, :deleted_at, :uid)');

    if (isBulian(940))
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
        // setup options
        $baristaOptions = DB::getInstance()
                        ->prepare('update barista_files set options = ? where id = ?');

        $baristaOptions->execute([json_encode(['id' => $id, 'path' => $plugin->path, 'version' => $plugin->version]), $baristaId]);
        // set response
        responseJson(['status' => true, 'message' => $message]);
    } else {
        responseJson(['status' => false, 'message' => DB::getInstance()->errorInfo()]);
    }
    exit;
}

/**
 * Disactivating Plugin
 *
 * @param string $id
 * @return void
 */
function disActivatingPlugin(string $id)
{
    // Some modification from admin/modules/system/plugins.php
    // get instances
    $plugins = Plugins::getInstance();

    $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
                                return $plugin->id === $id;
                           })[$id] ?? die(json_encode(['status' => false, 'message' => __('Plugin not found')]));

    
    if (property_exists($plugin, 'migration') && isBulian(940))
    {
        // set run down
        if ($plugin->migration->is_exist) SLiMS\Migration\Runner::path($plugin->path)->setVersion($plugin->migration->{Plugins::DATABASE_VERSION})->runDown();
        
        $process = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = ?");
    } else {
        $process = DB::getInstance()->prepare("DELETE FROM plugins WHERE id = ?");
    }

    $status = $process->execute([$id]);

    if ($status)
    {
        return ['status' => true, 'msg' => 'Plugin dinonaktifkan'];
    }
    // set error
    return ['status' => false, 'msg' => 'Plugin tidak berhasil dinonaktifkan'];
}

/**
 * Reactivate Plugin
 *
 * @param string $id
 * @return void
 */
function reActivatingPlugin(string $id)
{
    // Some modification from admin/modules/system/plugins.php
    // get instances
    $plugins = Plugins::getInstance();

    $plugin = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
                                return $plugin->id === $id;
                           })[$id] ?? die(json_encode(['status' => false, 'message' => __('Plugin not found')]));

     // active query
     $activeQuery = DB::getInstance()->prepare('INSERT INTO plugins (id, path, options, created_at, deleted_at, uid) VALUES (:id, :path, :options, :created_at, :deleted_at, :uid)');

     if (isBulian(940))
     {
         if ($plugins->isActive($id))
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
        // set response
        return ['status' => true, 'msg' => $message];
    } else {
        return ['status' => false, 'msg' => DB::getInstance()->errorInfo()];
    }
}

/**
 * deletingPlugin
 *
 * @param string $id
 * @return void
 */
function deletingPlugin(string $id)
{
    // get instances
    $plugins = Plugins::getInstance();

    // grab meta
    $meta = array_filter($plugins->getPlugins(), function ($plugin) use ($id) {
                            return $plugin->id === $id;
                        })[$id] ?? die(json_encode(['status' => false, 'message' => __('Plugin not found')]));
    // Fix path
    $fixPath = getPluginTruePath($meta->path);

    try {
        // check is active?
        if ($plugins->isActive($id))
        {
            // set DB instance
            $instance = DB::getInstance();
            // remove plugin from database
            $process  = $instance->prepare('delete from plugins where id = ?')->execute([$id]);
            $deleting = ($process) ? rrmdir($fixPath) : $instance->errorInfo();
            if ($meta->migration->is_exist && $process && isBulian(940)) 
            {
                SLiMS\Migration\Runner::path($meta->path)->setVersion($meta->migration->{Plugins::DATABASE_VERSION})->runDown();
            }
            // update barista files
            updateBaristaFiles($id);
            // set out
            return $deleting;
        }
        else
        {
            // deleting
            return rrmdir($fixPath);
        }
    } catch (Exception $exception) {
        return $exception->getMessage();
    }
}

/**
 * updateBaristaFiles
 *
 * @param string $id
 * @return void
 */
function updateBaristaFiles(string $id)
{
    global $dbs;
    // set id
    $id = $dbs->escape_string($id);
    // set options
    $dbs->query('update barista_files set options = NULL where '.jsonCriteria('options', '$.id', $id));
}

/**
 * getPluginTruePath
 *
 * @param string $dir
 * @return void
 */
function getPluginTruePath(string $dir)
{
    $dirSlice = explode(DS, $dir);
    unset($dirSlice[array_key_last($dirSlice)]);
    return implode(DS, $dirSlice);
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

/**
 * Just for debugging
 * @return mix
 */
function test()
{
}

/**
 * dd a.k.a dump data (like Laravel but just dump :D)
 *
 * @param mix $mix
 * @param boolean $exit
 * @return void
 */
function dd($mix, bool $exit = true)
{
    echo '<pre>';
    var_dump($mix);
    echo '</pre>';

    if ($exit) exit;
}

/**
 * jsonExtractExists
 *
 * check JSON_EXTRACT exists in RDMS
 *  
 * @return boolean
 */
function jsonExtractExists()
{
    global $dbs;

    $dbs->query('select json_extract(\'{"name": "Barista"}\', \'$.name\')');

    if ($dbs->errno === 1305)
    {
        return false;
    }

    return true;
}

/**
 * jsonCriteria
 * 
 * Generate criteria for JSON data,
 * if not exists then throw to LIKE statement
 *
 * @param string $column
 * @param string $prop
 * @param string $value
 * @return void
 */
function jsonCriteria(string $column, string $prop, string $value)
{
    $criteria = $column . ' like "%' . $value . '%"';
    if (jsonExtractExists())
    {
        $criteria = 'json_extract('.$column.', \''.$prop.'\') = \''.$value.'\'';
    }
    return $criteria;
}

/**
 * responseJSON
 *
 * set out data with json encoding
 * 
 * @param mix $mixData
 * @return void
 */
function responseJson($mixData)
{
    header('Content-Type: application/json');
    echo json_encode($mixData);
    exit;
}

/**
 * Replace String
 * 
 * Helper to replace character with preg_replace with template
 * or custom regex
 *
 * @param string $input
 * @param string $type
 * @param string $regex
 * @return void
 */
function replaceString(string $input, string $type = 'alphanum', string $regex = '')
{
    switch ($type) {
        case 'num':
            $result = preg_replace('/[^0-9]/', '', $input);
            break;

        case 'alpha':
            $result = preg_replace('/[^A-Za-z]/', '', $input);
            break;
        case 'regex':
            $result = preg_replace($regex, '', $input);
            break;
        default:
            $result = preg_replace('/[^A-Za-z0-9\.\-\_]/', '', $input);
            break;
    }

    return $result;
}

/**
 * Recursively empty and delete a directory
 * 
 * @param string $path
 * @ref https://gist.github.com/jmwebservices/986d9b975eb4deafcb5e2415665f8877
 */
function rrmdir( string $path ) : void
{

    if( trim( pathinfo( $path, PATHINFO_BASENAME ), '.' ) === '' )
        return;

    if( is_dir( $path ) )
    {
        array_map( 'rrmdir', glob( $path . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE | GLOB_NOSORT ) );
        @rmdir( $path );
    }

    else
        @unlink( $path );

}

// run php check
phpVersionCheck();

// curl check
curlCheck();

// check if admin/modules/ is writeable or not
modulesDirCheck();