'use strict'

/**
* @param mixed e
* 
* @return [type]
*/
function navClick(e){
        
    let target = e.getAttribute('data-target')
    let navlink = document.querySelectorAll('.nav-link')
    let httpurl = document.querySelector('script[with-url="yes"]').getAttribute('http-url')

    navlink.forEach(element => {
        element.classList.remove('active')
    });

    e.classList.add('active')

    if (target !== 'default')
    {
        $('#mainContent').simbioAJAX(`${httpurl}?section=${target}`)
    }
    else
    {
        $('#mainContent').simbioAJAX(`${httpurl}`)
    }
}

/**
 * @param object e
 * @param string path
 * @param string url
 * @param string branch
 * 
 * @return void
 */
async function install(e, path, url, branch)
{
    // just for short
    let doc = document
    // setup link to download
    let linkToDownload = `${url}/archive/refs/heads/${branch}.zip`
    // setresult area
    let resultArea = doc.querySelector('.resultBarista')
    // set children
    let children = e.children
    
    // check internet connection
    isOnline(resultArea);

    // manipulate
    doc.querySelectorAll('.actionBtn').forEach(el => {
        el.classList.add('disabled')
        el.removeAttribute('onclick')
    })
    
    e.classList.remove('btn-primary', 'disabled')
    e.classList.add('btn-info')
    children[0].classList.remove('d-none')
    children[1].innerHTML = 'Memasang'

    // make post request
    let httpurl = doc.querySelector('script[with-url="yes"]').getAttribute('http-url')
    await fetch(`${httpurl}?action=install`, {
        method: 'POST',
        body: JSON.stringify({
            pathDest: path,
            urlDownload: linkToDownload,
            branchName: branch,
            id: parseInt(children[0].getAttribute('for'))
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status)
        {
            // remove disable class
            doc.querySelectorAll('.actionBtn').forEach(el => {
                el.classList.remove('disabled')
            })
            
            // class modification
            e.classList.add('btn-success')
            e.classList.remove('btn-info')
            children[0].classList.add('d-none')
            children[1].innerHTML = 'Terpasang'

            // set timeout
            setTimeout(() => {
                // reload it
                location.reload()
            }, 500);
        }
        else
        {
            // add error class
            e.classList.add('btn-danger')
            e.classList.remove('btn-info')
            children[0].classList.add('d-none')
            children[1].innerHTML = 'Gagal memasang'

            // set msg
            resultArea.innerHTML = result.msg
            resultArea.classList.add('bg-danger', 'text-white')
            resultArea.classList.remove('d-none')
        }
    })
    .catch(error => {
        // set alert if request is failed
        // add error class
        e.classList.add('btn-danger')
        e.classList.remove('btn-info')
        children[0].classList.add('d-none')
        children[1].innerHTML = 'Gagal memasang'
        // set msg
        resultArea.innerHTML = error + '. Tekan F12 untuk info lebih lanjut'
        resultArea.classList.add('bg-danger', 'text-white');
        resultArea.classList.remove('d-none')
    })
}

async function setStatusPlugin(el)
{
    let label = el.children[1].innerHTML
    let dataId = el.getAttribute('data-id')
    let httpurl = document.querySelector('script[with-url="yes"]').getAttribute('http-url')

    let action = 'renable';
    if (label === 'Non-aktifkan')
    {
        action = 'disable'
    }

    await fetch(`${httpurl}?action=${action}`, {
        method: 'POST',
        body: JSON.stringify({
            id: dataId
        })
    })
    // .then(response => response.text())
    .then(response => response.json())
    .then(result => {
        // console.log(result)
        if (result.status)
        {
            parent.toastr.success(result.msg, 'Plugin')
            setTimeout(() => {$('#mainContent').simbioAJAX(`${httpurl}?section=plugin`)}, 2000)
        }
        else
        {
            parent.toastr.error(res.msg, 'Galat')
        }
    })
    .catch(error => {alert(error)})
}

/**
 * @param mixed resultSelector
 * 
 * @return [type]
 */
function isOnline(resultSelector)
{
    if (!window.navigator.onLine)
    {
        resultSelector.innerHTML = 'Anda tidak terkoneksi internet, modul ini membutuhkan koneksi internet untuk dapat bekerja. Pastikan koneksi internet anda stabil.'
        resultSelector.classList.add('bg-danger', 'text-white', 'font-weight-bold', 'h6')
        resultSelector.classList.remove('d-none')
        return;
    }
}

async function checkUpdate(el, currentVersion)
{
    el.children[0].classList.add('d-none')
    el.children[1].classList.remove('d-none')
    el.children[2].innerHTML = 'Memproses'
    el.classList.remove('btn-primary')
    el.classList.add('btn-info')

    await fetch('https://api.github.com/repos/drajathasan/slims-barista/releases/latest')
    .then(response => response.json())
    .then(result => {
        if (result.name > currentVersion)
        {
            el.children[0].classList.remove('d-none')
            el.children[1].classList.add('d-none')
            el.children[2].innerHTML = 'Terdapat versi terbaru (Klik untuk upgrade)'
            el.classList.remove('btn-info')
            el.classList.add('btn-success')
        }
        else
        {
            el.children[0].classList.remove('d-none')
            el.children[1].classList.add('d-none')
            el.children[2].innerHTML = 'Tidak ada pembaharuan'
            el.classList.remove('btn-info')
            el.classList.add('btn-success')
        }
    })
    .catch(error => {alert(error)})    
}

async function getLastListApp()
{
    let url = document.querySelector('script[with-url="yes"]').getAttribute('http-url');

    fetch(`${url}?action=updateList`)
    .then(response => response.json())
    .then(result => {
        if (result.status)
        {
            location.reload();
        }
        else
        {
            parent.toastr.error(result.msg, 'Galat');
        }
    })
}

// check connection
isOnline(document.querySelector('.resultBarista'))