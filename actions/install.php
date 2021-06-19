<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-19 14:19:47
 * @modify date 2021-06-19 14:19:47
 * @desc [description]
 */

isDirect();

$_POST = getRawPost();

if (isset($_POST['urlDownload']) && isset($_POST['pathDest']))
{
    if (!file_exists(SB. 'plugins/baristaCache/'))
    {
        createCacheDir();
    }

    $filepath = SB. 'plugins/baristaCache/' . basename($_POST['pathDest']) . '.zip';
    $download = downloadPlugin(urldecode($_POST['urlDownload']), $filepath);

    if ($download['status'])
    {
        // make dest
        $dest = SB. 'plugins/';
        // unpacking zip file from cache folder to dest folder
        renamingZipDir(
            extractZip($filepath, $dest), 
            basename($_POST['pathDest']), 
            basename($_POST['branchName']), 
            urldecode($_POST['urlDownload']),
            $_POST['id']
        );
    }
    else
    {
        responseJson($download);
    }
    exit;
}
else
{
    echo json_encode(['status' => false, 'data' => NULL]);
    exit;
}