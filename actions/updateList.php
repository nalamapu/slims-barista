<?php
isDirect();

// get raw file from github
if (ini_get('allow_url_fopen'))
{
    $getList = file_get_contents($sysconf['barista']['repo_server']);
    // decoding data
    json_decode($getList);

    if (!json_last_error())
    {
        // storing
        @file_put_contents(__DIR__ . '/../barista-plugin-local.json', $getList);
        // make migration
        baristaMigration((new simbio_dbop($dbs)));
    }

    responseJson(['status' => true, 'msg' => 'Daftar berhasil diperbaharui']);
}
else
{
    responseJson(['status' => false, 'msg' => 'Galat, opsi allow_url_fopen tidak aktif.']);
}