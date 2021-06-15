<?php
$_POST = getRawPost();

if (isset($_POST['urlDownload']) && isset($_POST['pathDest']))
{
    $filepath = SB. 'plugins/baristaCache/' . basename($_POST['pathDest']) . '.zip';
    
    $download = downloadPlugin(urldecode($_POST['urlDownload']), $filepath);

    echo json_encode($download);
    exit;
}
else
{
    echo json_encode(['status' => false, 'data' => NULL]);
    exit;
}