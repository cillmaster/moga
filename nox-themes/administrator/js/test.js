function upData(method, url, data, callback, onerror, prm) {
    var r = new XMLHttpRequest();
    r.open(method, url);
    method === 'POST' && r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    r.onreadystatechange = function() {
        if(r.readyState === 4)
            if(r.status === 200){
                callback && callback(r.responseText, prm);
            }else{
                onerror && onerror(prm);
            }
    };
    r.send(data);
}

document.addEventListener('DOMContentLoaded', function () {
    var cons = document.getElementById('console');
    if(cons) {
        var re = /FILE\:/g,
            errors = document.body.textContent.match(re),
            d = document.createElement('div');
        d.innerHTML = 'Errors: ' + (errors ? errors.length : 0);
        cons.insertBefore(d, cons.firstElementChild);
        cons.addEventListener('click', function () {
            cons.className = cons.className ? '' : 'full';
        });
    }
});