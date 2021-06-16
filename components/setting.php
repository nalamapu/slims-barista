<?php
// check direct access
isDirect();

// action area
if (isset($_POST['saveBarista']))
{
    // make a cache
    createCacheDir();
    
    // set up sql operation
    $sqlOp = new simbio_dbop($dbs);
    // unset post
    $_POST = unsetPost(['saveBarista', 'csrf_token', 'form_name']);

    foreach ($_POST as $key => $value) {
        if (empty($_POST[$key]))
        {
            utility::jsToastr('Galat', 'Isian '.ucwords(str_replace('_', ' ', $key)).' tidak boleh kosong!', 'error');
            break;
        }
        else
        {
            $_POST[$key] = $dbs->escape_string($value);
        }
    }

    // to serialize
    $serializeData = serialize($_POST);

    if (!isset($sysconf['barista']))
    {
        if ($sqlOp->insert('setting', ['setting_name' => 'barista', 'setting_value' => $serializeData]))
        {
            // make migration
            baristaMigration($sqlOp);
            // set alert
            utility::jsToastr('Sukses', 'Berhasil menyimpan data.', 'success');
            // redirect
            simbioRedirect($_SERVER['PHP_SELF']);
            exit;
        }
    }
    else
    {
        
        if ($sqlOp->update('setting', ['setting_value' => $serializeData], 'setting_name=\'barista\''))
        {
            // utility::jsAlert($sqlOp->getSQL());
            utility::jsToastr('Sukses', 'Berhasil memperbaharui data.', 'success');
            simbioRedirect($_SERVER['PHP_SELF']);
            exit;
        }
    }

    utility::jsToastr('Galat', 'Terdapat error : '.$sqlOp->error, 'danger');
    exit;
}
// end action area

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'] . '?section=setting', 'post');
$form->submit_button_attr = 'name="saveBarista" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// server repo
$element = [
            'addTextField'
            => 
            [
                [
                'text', 
                'repo_server', 'Alamat Repo Agregator', 
                ($sysconf['barista']['repo_server']) ?? 'https://raw.githubusercontent.com/drajathasan/slims-barista-repo/main/list.json', 
                'style="width: 60%;" class="form-control"'
                ]
            ],
            'addSelectList'
            =>
            [
                ['auto_active', __('Otomatis Aktif'), [['t', __('Disable')],['y', __('Enable')]], $sysconf['barista']['auto_active']??0,'class="form-control col-3"'],
                ['make_cache', __('Hidupkan Cache'), [['t', __('Disable')],['y', __('Enable')]], $sysconf['barista']['make_cache']??0,'class="form-control col-3"'],
                ['refresh_to_update', __('Refresh untuk memperbaharui daftar'), [['t', __('Disable')],['y', __('Enable')]], $sysconf['barista']['refresh_to_update']??0,'class="form-control col-3"']
            ]
        ];

// set up element
foreach ($element as $el => $fields) {
    foreach ($fields as $field) {
        call_user_func_array([$form, $el], $field);
    }
}

// print out the object
echo $form->printOut();