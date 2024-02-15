(function( $ ) {
    'use strict';
    

    var datosGuardados = obtenerDatosDeCookie();
    if (datosGuardados) {
        
        console.log("cargando pagina",datosGuardados);

        // Asigna los valores a los campos del formulario

        //document.getElementById("fi_source").value = datosGuardados.source;
        
                
        setTimeout(function(){ 
            $("#fi_post_type").val(datosGuardados.post_type);
            $("#fi_status").val(datosGuardados.status);
            $("#fi_source").val(datosGuardados.source);
            $("#fi_title").val(datosGuardados.title);
            $("#fi_descripcion").val(datosGuardados.descripcion);
            $("#fi_created_at_desde").val(datosGuardados.created_at_desde);
            $("#fi_created_at_hasta").val(datosGuardados.created_at_hasta);
            $("#fi_json_key").val(datosGuardados.json_key);
            $("#fi_json_value").val(datosGuardados.json_value);
            $("#fi_order_by").val(datosGuardados.order_by);
        }, 1000);
        
    }

    let urlActual = window.location.href;
    urlActual = urlActual.split('=');    

    if (urlActual[1] == 'kiwop-prisma-recursos') {

        document.addEventListener("DOMContentLoaded", function() {
    
    
            let data = {
                action: 'getStadisticsHome',
                _ajax_nonce: kiwop_prisma_globals.getStadisticsHome
            };
            $.ajax({
                url: kiwop_prisma_globals.ajax_url,
                data: data,
                type: 'POST',
                success: function(response) {
                    let dataImported = JSON.parse(response);
                    // Tu código que utiliza CanvasJS aquí
                    var chart = new CanvasJS.Chart("dataChartBySource", {
    
                        //Chart Options - Check https://canvasjs.com/docs/charts/chart-options/
                        title:{
                        text: dataImported.dataChartBySource.title             
                        },
                        axisY: {
                            minimum: 0,                        
                        },                    
                        data: [{
                            type: "doughnut",
                            startAngle: 60,
                            //innerRadius: 60,
                            indexLabelFontSize: 17,
                            indexLabel: "{label} - #percent%",
                            toolTipContent: "<b>{label}:</b> {y} (#percent%)",                        
                            dataPoints: dataImported.dataChartBySource.data
                        }]
                    });
                    //Render Chart
                    chart.render();                
    
                    var chart2 = new CanvasJS.Chart("dataChartByPostType", {
    
                        //Chart Options - Check https://canvasjs.com/docs/charts/chart-options/
                        title:{
                        text: dataImported.dataChartByPostType.title             
                        },
                        axisY: {
                            minimum: 0,                        
                        },                    
                        data: [{
                            type: "doughnut",
                            startAngle: 60,
                            //innerRadius: 60,
                            indexLabelFontSize: 17,
                            indexLabel: "{label} - #percent%",
                            toolTipContent: "<b>{label}:</b> {y} (#percent%)",                        
                            dataPoints: dataImported.dataChartByPostType.data
                        }]
                    });
                    //Render Chart
                    chart2.render();                
    
                    //let data = JSON.parse(response);
                    
                    
                },
                error: function(errorThrown){
                    button.find('.loader').remove();
                    ajaxMessage('cercar_ajax_response',errorThrown.statusText);
                }
            });
    
    
        });
        
    }



    var action_vars = [];
    //$('.tab[data-tab="tab1"]').trigger('click');

    $(document).on("click",".ktab", function(event){      
        let tabId = $(this).attr('data-tab'); 
        
        // Ocultar todas las pestañas y mostrar la pestaña seleccionada
        var tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(function(tab) {
            tab.classList.remove('active-content');
        });

        //alert("kaka");

        document.getElementById(tabId).classList.add('active-content');

        // Cambiar la clase activa en las pestañas
        var tabButtons = document.querySelectorAll('.ktab');
        tabButtons.forEach(function(button) {
            button.classList.remove('active-tab');
        });

        $(this).addClass('active-tab');
        
    });
    
    $(document).on("click",".subtab", function(event){      
        let tabId = $(this).attr('data-tab'); 
        
        // Ocultar todas las pestañas y mostrar la pestaña seleccionada
        var tabs = document.querySelectorAll('.subtab-content');
        tabs.forEach(function(tab) {
            tab.classList.remove('active-content');
        });
        
        //alert("kaka");
        
        document.getElementById(tabId).classList.add('active-content');
        
        // Cambiar la clase activa en las pestañas
        var tabButtons = document.querySelectorAll('.subtab');
        tabButtons.forEach(function(button) {
            button.classList.remove('active-subtab');
        });
        
        $(this).addClass('active-subtab');
        
    });
    

    $(document).on("change","#search_type", function(event){      
        let source = $(this).val();
        if (source == 'other') {
            $('#other_source_input').css('display','block');
        } else {
            $('#other_source_input').css('display','none');
        }

        if (source == 'clic.xtec.cat') {
            $('#jclic_options').css('display','block');
        } else {
            $('#jclic_options').css('display','none');
        }
    });

    $(document).on("click",".seleccionaTodo", function(event){      
        event.preventDefault();
        let target_class = $(this).attr('data-destino');
        var checkboxes = document.querySelectorAll('.' + target_class);
    
        // Iterar sobre los checkboxes y establecer su propiedad "checked" a true
        checkboxes.forEach(function(checkbox) {
          checkbox.checked = true;
        });
    });

    $(document).on("click",".deSeleccionaTodo", function(event){      
        event.preventDefault();
        let target_class = $(this).attr('data-destino');
        var checkboxes = document.querySelectorAll('.' + target_class);
    
        // Iterar sobre los checkboxes y establecer su propiedad "checked" a true
        checkboxes.forEach(function(checkbox) {
          checkbox.checked = false;
        });
    });

    function ajaxMessage(container,msg) {
        
        Array.isArray(msg) ? msg = msg.join('<br />') : msg = msg;

        $('#'+container).html(msg);
        /*
        setTimeout(function(){ 
            $('#'+container).html('');
        }, 5000);
        */

    }

    /* --------------------  scrapping  ----------------------- */

    $(document).on("click","#scrappButton", function(event){    
        event.preventDefault();
        
        let button = $(this);
        button.find('.loader').remove();

        let img = ' <img class="loader" src="'+kiwop_prisma_globals.loader+'" />';
        button.append(img);


        let source = $('#search_type').val();
        let other_source = $('#other_source').val();
        let jclicUpdateMode = $("input[name='jClicUpdate']:checked").val();

        if (source.length == 0) {
            ajaxMessage('cercar_ajax_response','Debes seleccionar una fuente de datos');
            return false;
        }
        ajaxMessage('cercar_ajax_response','');
        console.log(source,kiwop_prisma_globals.find_resources);

        let data = {
            action: 'find_resources',
            source: source,
            other_source: other_source,
            updateMode: jclicUpdateMode,
            _ajax_nonce: kiwop_prisma_globals.find_resources
        };
        $.ajax({
            url: kiwop_prisma_globals.ajax_url,
            data: data,
            type: 'POST',
            success: function(response) {
                let data = JSON.parse(response);
                button.find('.loader').remove();
                let text = data.data.resumen + '<br /> <br />';
                if (data.data.messages.length > 0) {
                    text += data.data.messages.join('<br />');
                }
                ajaxMessage('cercar_ajax_response',text);
            },
            error: function(errorThrown){
                button.find('.loader').remove();
                ajaxMessage('cercar_ajax_response',errorThrown.statusText);
            }
        });
    });
    
    /* --------------------  filtros tabla scrapping  ----------------------- */
    $(document).on("click","#borrarFiltro", function(event){    
        $('#filtresImportacions')[0].reset();
    });


    $(document).on("click","#filtrarDades", function(event){    
        
        event.preventDefault();      
        
        let name_container = '#tablaImportacions';
        let container = $(name_container);
        let paginador = $("#paginatorImportacions");
        let button = $(this);
        button.find('.loader').remove();
        
        let img = ' <img class="loader" src="'+kiwop_prisma_globals.loader+'" />';
        button.append(img);
        
        let dataPost = $('#filtresImportacions').serialize();
        
        let dataPostJS = {};

        dataPostJS.post_type = $("#fi_post_type").val();
        dataPostJS.status = $("#fi_status").val();
        dataPostJS.source = $("#fi_source").val();
        dataPostJS.title = $("#fi_title").val();
        dataPostJS.descripcion = $("#fi_descripcion").val();
        dataPostJS.created_at_desde = $("#fi_created_at_desde").val();
        dataPostJS.created_at_hasta = $("#fi_created_at_hasta").val();
        dataPostJS.json_key = $("#fi_json_key").val();
        dataPostJS.json_value = $("#fi_json_value").val();
        dataPostJS.order_by = $("#fi_order_by").val();

        console.log(JSON.stringify(dataPostJS));
        localStorage.setItem('filtresImportacions', JSON.stringify(dataPostJS));

        let data = {
            action: 'getHTMLScrappings',
            data:dataPost,
            _ajax_nonce: kiwop_prisma_globals.getHTMLScrappings
        };
        
        filtrarDades(container, name_container, paginador, button, data);
        
        
    });
    
    function filtrarDades(container, name_container, paginador, button, data)
    {
        
        $.ajax({
            url: kiwop_prisma_globals.ajax_url,
            data: data,
            type: 'POST',
            dataType : 'json',
            success: function(response) {
                button.find('.loader').remove();
                if (response.error) {
                    $(container).html(response.message);
                } else {
                    $(container).html(response.html);
                    $(paginador).html(response.paginator);                    
                    $('#totalPagesFiltres').html("(<strong>"+response.total+"</strong> registres)");
                }
                $('#selectionMultipleImportacion').removeAttr('hidden');
            },
            error: function(errorThrown){
                button.find('.loader').remove();
                ajaxMessage(name_container,errorThrown.statusText);
            }
        });
    }
    
    
    // --------------------------------------------------------------------------------------------------------------------------
    // pestaña principal, navegador ajax listado ultimos descuentos aplicados
    // --------------------------------------------------------------------------------------------------------------------------

    $(document).on("change","#pagina_actual ", function(event){    
        var button = $('#loader_select_page');
        let value = $(this).val();
        cambiaPaginaScrappings('prior', button, value);
    });

    $(document).on("click","#paginatorImportacions .prior_row", function(event){    
        var button = $(this);
        cambiaPaginaScrappings('prior', button);
    });

    $(document).on("click","#paginatorImportacions .next_row", function(event){    
        var button = $(this);
        cambiaPaginaScrappings('next', button);
    });

    function cambiaPaginaScrappings(direction, button, selected_page = null)
    {
        //event.preventDefault();


        let page = parseInt( button.attr('data-page') );
        let total = parseInt( button.attr('data-total') );
        let per_page = parseInt( button.attr('data-per_page') );
        
        if (selected_page == null) {

            if (direction == 'prior') {
                let priorIsDisabled = button.hasClass('a_navigator_disabled');
                if (priorIsDisabled) {
                    return;
                }  
                page = page - 1;           
            }
    
            if (direction == 'next') {
                let nextIsDisabled = button.hasClass('a_navigator_disabled');
                if (nextIsDisabled) {
                    return;
                }            
                page = page + 1;           
            }
        } else {
            page = selected_page; 
        }

        //event.preventDefault();      
        
        let name_container = '#tablaImportacions';
        let container = $(name_container);
        let paginador = $("#paginatorImportacions");

        button.find('.loader').remove();
        
        let img = ' <img class="loader" src="'+kiwop_prisma_globals.loader+'" />';
        button.append(img);

        let dataPost = $('#filtresImportacions').serialize();

        //Cookies.set('filtresImportacions', JSON.stringify(dataPostJS), { expires: 365 } );


        let data = {
            per_page:per_page,
            paged:page,
            action: 'getHTMLScrappings',
            data:dataPost,
            _ajax_nonce: kiwop_prisma_globals.getHTMLScrappings
        };

        filtrarDades(container, name_container, paginador, button, data);
        
    }

    function obtenerDatosDeCookie() {
        var datos = localStorage.getItem('filtresImportacions');

        console.log("obtenerDatosDeCookie",datos);

        // Convertir la cadena JSON de vuelta a un array
        
        if (datos) {
            try {
                datos = JSON.parse(datos);
              } catch (error) {
                console.error(error);
              }

            return datos;
        }
  
        return null;
      }

    /* --------------------  mostrar jsons insertados en data attr  ----------------------- */


    $(document).on("click",".clickOnShowDataJSON", function(event){  
        //$('#popUpModalInfoBox').modal('hide');
        event.preventDefault();

        let id = $(this).attr('data-id');
        let row = $('#row'+id);

        let data_xtrajson = null;
        let data_json = null;
        let json = row.attr('data-json');
        let xtrajson = row.attr('data-extra-json');
        let popUpResponse = '#popUpModalInfoBox';
        let label = '#popUpModalInfoBoxLabel';
        $(label).html('Altres dades');

        try {
            data_xtrajson = JSON.parse( atob(xtrajson) );           
        } catch (error) {
            console.error('El string no es un JSON válido:', error.message);
        }


       // console.log(data_xtrajson);
       // console.log(data_json);

        let show = false;
        let html = '';

        if (xtrajson.length > 0) {
            html += "<h4>Títol i descripció completa</h4>";
            html += recorrerObjetoYCrearHTML(data_xtrajson);
            html += "<br /><hr />";
            show = true;
        }
        

        if (show) {
            $(popUpResponse + ' .modal-body').html(html);
            $(popUpResponse).modal('show');
        }


    });

    function recorrerObjetoYCrearHTML(objeto) {
        var html = '<table class="table" border="1">';
        var data = '<tr><th>JSON Key</th><th>JSON Value</th></tr>';
        var html_header1 = '<tr><th>Campo</th><th>Valor</th></tr>';
        var html_header2 = '<tr><td colspan="2"><h4>camps JSON</h4></td></tr>';
        var first_fields = '';
    
        for (var clave in objeto) {
            if (objeto.hasOwnProperty(clave)) {
                // Agrega una fila a la tabla con clave y valor
                if (clave == 'title' || clave == 'description') {
                    first_fields += '<tr><th style="valign:top;" >' + clave + '<br /></th><td>' + objeto[clave] + '</td></tr>';
                } else {
                    data += '<tr><th class="clickOnCellJSONKey" style="valign:top; background-color:#0000CC; color:#fff;" >' + clave + '</th><td class="clickOnCellJSONValue" >' + objeto[clave] + '</td></tr>';
                }
            }
        }

    
        html = html + html_header1 + first_fields + html_header2 + data + '</table>';
        return html;
    }

    
    $(document).on("click",".clickOnCellJSONKey", function(event){  
        event.preventDefault();
        let text = $(this).html();
        $('#fi_json_key').val(text.trim());
    });

    $(document).on("click",".clickOnCellJSONValue", function(event){  
        event.preventDefault();
        let text = $(this).html();
        $('#fi_json_value').val(text.trim());
    });


    /* --------------------  creacion de posts  ----------------------- */
    
    $('#popUpModalInfoBox').dialog({
        autoOpen: false, 
        title: 'Creació/actualització de posts',
        modal: true,
        width:800,
        height:350,
    });  

    $(document).on("click",".closeThePopup", function(event){  
        $('#popUpModalInfoBox').modal('hide');
    });

   
    $(document).on("click","#clickOnSelectedAction", function(event){  
        
        
        event.preventDefault();
        
        
        let action = $("#scrappingActionsSelect").val();
        let post_status = $('#scrappingActionsSelect option:selected').attr('data-post_status');
        let post_type = $('#scrappingActionsSelect option:selected').attr('data-post_type');

        console.log(action,post_status);
        console.log(action,post_type);

        let img = ' <img class="loader" src="'+kiwop_prisma_globals.loader+'" />';
        let popUpResponse = '#popUpModalInfoBox';
        let button = $(this);
        let dataPost = $('#filtresImportacions').serialize();        
        let selection = $('#formSelectionRows').serialize();        

        let data = {
            action: 'doSelectedAction',
            insideAction: action,
            data:dataPost,
            selection:selection,
            post_status:post_status,
            post_type:post_type,
            _ajax_nonce: kiwop_prisma_globals.doSelectedAction
        };

        button.find('.loader').remove();
        
        button.append(img);        

        createPosts(popUpResponse, button, data)
    });
    
    function createPosts(popUpResponse, button, data)
    {
        $('#popUpModalInfoBoxLabel').html('Creació/Actualització de posts');
        
        $.ajax({
            url: kiwop_prisma_globals.ajax_url,
            data: data,
            type: 'POST',
            dataType : 'json',
            success: function(response) {
                button.find('.loader').remove();
                $(popUpResponse + ' .modal-body').html(response.message);
                $(popUpResponse).modal('show');
                
                let selected_page = $('#pagina_actual').val();
                console.log("selected_page:" + selected_page)
                cambiaPaginaScrappings(null, button, selected_page);
            },
            error: function(errorThrown){
                button.find('.loader').remove();
                $(popUpResponse + ' .modal-body').html(errorThrown.statusText);
                $(popUpResponse).modal('show');
            }
        });
    }
    
})( jQuery );
