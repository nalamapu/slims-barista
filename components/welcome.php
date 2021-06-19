<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-19 13:10:54
 * @modify date 2021-06-19 13:10:54
 * @desc [description]
 */
// prevent direct access
isDirect();

if ($dbs->query('show tables like \'barista_files\'')->num_rows === 0)
{
    components('banner.php');
    if (!isset($_GET['section'])):
        echo <<<HTML
            <h1 class="w-full block mx-5 mt-5 mb-2">Selamat datang</h1> 
            <p class="mx-5 h5">Sebelum menggunakan <b>barista</b>, anda diwajibkan untuk mengatur beberapa pengaturan.</p>
            <button class="start ml-5 btn btn-primary">Mulai</button>
            <script>
                $('.start').click(() => {
                    $('#mainContent').simbioAJAX('{$_SERVER['PHP_SELF']}?section=setting');
                });
            </script>
        HTML;
    else:
        components('setting.php');
    endif;
    exit;
}