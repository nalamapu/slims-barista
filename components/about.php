<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-06-19 13:19:10
 * @modify date 2021-06-19 13:19:10
 * @desc [description]
 */

isDirect();
?>
<fieldset class="mx-3 my-4">
    <div class="row">
        <div class="col-2">
            <label class="h6">Nomor versi</label>
        </div>
        <div class="col-10 font-weight-bold">
            <?= $sysconf['barista']['version'] ?>
        </div>
    </div>
    <div class="row">
        <div class="col-2">
            <label class="h6">Diracik Oleh</label>
        </div>
        <div class="col-10 font-weight-bold">
            <a target="_blank" href="https://fb.me/MafriaTechEdu/">Drajat Hasan</a>
        </div>
    </div>
    <div class="row">
        <div class="col-2">
            <label class="h6"></label>
        </div>
        <div class="col-10 font-weight-bold">
            <button onclick="checkUpdate(this, '<?= $sysconf['barista']['version'] ?>')" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="spinLoader d-none" for="{$column[1]}" style="margin: auto;" width="20" height="20" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                    <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#e0e0e0" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
                    </circle>
                </svg>    
                <span>Cek Pembaharuan</span>
            </button>
        </div>
    </div>
</fieldset>