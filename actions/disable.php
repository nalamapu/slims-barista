<?php
isDirect();

$_POST = getRawPost();

if (isset($_POST['id']))
{
    // set response 
    responseJson(disActivatingPlugin($_POST['id']));
}