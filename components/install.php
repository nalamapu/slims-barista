<?php
$_POST = getRawPost();

if (isset($_POST['urlDownload']) && isset($_POST['pathDest']))
{
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
        echo json_encode($download);
    }
    exit;
}
else
{
    echo json_encode(['status' => false, 'data' => NULL]);
    exit;
}