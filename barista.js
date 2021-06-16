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
function install(e, path, url, branch)
{
    // just for short
    let doc = document
    // setup link to download
    let linkToDownload = `${url}/archive/refs/heads/${branch}.zip`
    // setresult area
    let result = doc.querySelector('.resultBarista')
    // set children
    let children = e.children
    
    // check internet connection
    isOnline(result);

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
    fetch(`${httpurl}?action=install`, {
        method: 'POST',
        body: JSON.stringify({
            pathDest: path,
            urlDownload: linkToDownload,
            branchName: branch
        })
    })
    .then(response => response.json())
    .then(result => {
        // console.log(result);
        // if success
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
            }, 2000);
        }
        else
        {
            // add error class
            e.classList.add('btn-danger')
            e.classList.remove('btn-info')
            children[0].classList.add('d-none')
            children[1].innerHTML = 'Gagal memasang'

            // set msg
            result.innerHTML = result.msg
            result.classList.add('bg-danger', 'text-white')
            result.classList.remove('d-none')
        }
    })
    // uncomment for debugging
    // .catch(error => {
    //     // set alert if request is failed
    //     // add error class
    //     e.classList.add('btn-danger')
    //     e.classList.remove('btn-info')
    //     children[0].classList.add('d-none')
    //     children[1].innerHTML = 'Gagal memasang'
    //     // set msg
    //     result.innerHTML = error + '. Tekan F12 untuk info lebih lanjut'
    //     result.classList.add('bg-danger', 'text-white');
    //     result.classList.remove('d-none')
    // })
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

// check connection
isOnline(document.querySelector('.resultBarista'))