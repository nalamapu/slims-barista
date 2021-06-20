<?php
isDirect();

$_POST = getRawPost();

if (isset($_POST['id']) && isset($_POST['deletePlugin']))
{
    $process = deletingPlugin($_POST['id']);

    if (is_null($process))
    {
        // set out
        responseJson(['status' => true, 'msg' => 'Plugin berhasil dihapus.']);
    }
    // set out
    responseJson(['status' => false, 'msg' => 'Plugin tidak berhasil dihapus.']);
}