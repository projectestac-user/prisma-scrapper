function realizarSolicitudAjax(url, metodo, datos, exitoCallback, errorCallback) {
    var xhr = new XMLHttpRequest();
    
    xhr.open(metodo, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
           
            if (exitoCallback) {
                exitoCallback(JSON.parse(xhr.responseText));
            }
        } else {
            
            if (errorCallback) {
                errorCallback(xhr.statusText);
            }
        }
    };

   
    xhr.onerror = function () {
        if (errorCallback) {
            errorCallback('Error de red');
        }
    };

    
    if (datos) {
        xhr.send(JSON.stringify(datos));
    } else {
        xhr.send();
    }
}


var url = 'http://prisma.kiwop.loc/wp-admin/admin-ajax.php?action=getDataSharedFindPrisma';
var metodo = 'POST'; 
var datos = { clave: 'valor' }; 

realizarSolicitudAjax(url, metodo, datos,
    function (respuesta) {
        /*  Manejar la respuesta exitosa */
        let html = '';
        respuesta.forEach(function (item) {
            image = "<img src='"+item.image+"' />";
            title = "<div class='title'>"+item.title+"</div>";
            html += "<div class='post'>"+image+title+"</div>";
        });
        document.getElementById('prisma_share_container').innerHTML = html;
        console.log('Respuesta exitosa:', respuesta);
    },
    function (error) {
        /* Manejar el error */
        document.getElementById('prisma_share_container').innerHTML = error;
    }
);


