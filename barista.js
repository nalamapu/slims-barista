// set http url
let httpurl = document.querySelector('script[with-url="yes"]').getAttribute('http-url')

/**
* @param mixed e
* 
* @return [type]
*/
function navClick(e){
        
    let target = e.getAttribute('data-target')
    let navlink = document.querySelectorAll('.nav-link')

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

/**
 * setStatusPlugin
 * 
 * @param object el 
 */
async function setStatusPlugin(el)
{
    let label = el.children[1].innerHTML
    let dataId = el.getAttribute('data-id')

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
 * deletePlugin
 * 
 * @param object el 
 */
async function deletePlugin(el)
{
    let dataId = el.getAttribute('data-id')

    await fetch(`${httpurl}?action=delete`, {
        method: 'POST',
        body: JSON.stringify({
            id: dataId,
            deletePlugin: true
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status)
        {
            // set success
            parent.toastr.success(result.msg, 'Sukses')
            // redirect
            setTimeout(() => {$('#mainContent').simbioAJAX(`${httpurl}?section=plugin`)}, 2000)
        }
        else
        {
            // set success
            parent.toastr.error(result.msg, 'Galat')
        }
    })
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

/**
 * checkUpdate
 * 
 * @param object el 
 * @param integer currentVersion 
 */
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

/**
 * getLastListApp
 * 
 * @param object el 
 */
async function getLastListApp(el)
{
    let children = el.children
    let iconOne = children[0]
    let iconTwo = children[1]
    let label = children[2]

    // modify button
    el.classList.remove('btn-danger')
    el.classList.add('btn-info')
    iconOne.classList.add('d-none')
    iconTwo.classList.remove('d-none')
    label.innerHTML = 'Tunggu Sebentar'

    fetch(`${httpurl}?action=updateList`)
    .then(response => response.json())
    .then(result => {
        if (result.status)
        {
            parent.toastr.success(result.msg, 'Sukses')
        }
        else
        {
            parent.toastr.error(result.msg, 'Galat')
        }
        // set button
        setTimeout(() => {$('#mainContent').simbioAJAX(url)}, 2000);
    })
}

// check connection
isOnline(document.querySelector('.resultBarista'))
