<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Prisma_Recurses
 * @subpackage Kiwop_Prisma_Recurses/admin/partials
 */


 $adminURL = admin_url();
 


?>

<script src=" https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js "></script>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<ul class="tabs" >
    <li class="ktab active-tab" data-tab='tab1'><i class="fa-solid fa-robot"></i> Obtenir recursos</li>    
</ul>



<div id="tab1" class="tab-content active-content" >
    <ul class="subtabs" >
        <li class="subtab active-subtab" data-tab='subtab0'><i class="fa-solid fa-home"></i> Home </li>
        <li class="subtab" data-tab='subtab2'><i class="fa-solid fa-database"></i> Importacions </li>
        <li class="subtab" data-tab='subtab1'><i class="fa-brands fa-searchengin"></i> `Scrapping` dades</li>
        <!-- <li class="subtab" data-tab='subtab3'><i class="fa-solid fa-gear"></i> Altres Configuracions </li> -->
    </ul>

    <div id="subtab0" class="subtab-content active-content">
        <div class="row">
            <div class="col-xs-12 col-lg-6">
                <div id="dataChartBySource" style="height: 500px; width: 100%; position: relative;"></div>
            </div>
            <div class="col-xs-12 col-lg-6">
                <div id="dataChartByPostType" style="height: 500px; width: 100%; position: relative;"></div>
            </div>
        </div>
    </div>

    <div id="subtab1" class="subtab-content ">

        <h4>Formulari de cerca de recursos</h4>
        <hr />
        <form>
            <div class="row">
                <div class="col col-xs-12 col-lg-6">
                    <label for="search_type">Tipo de Búsqueda:</label>
                    <select class="form-select" name="search_type" id="search_type">
                        <option value="toolbox.mobileworldcapital.com">toolbox.mobileworldcapital.com</option>
                        <option value="alexandria.xtec.cat">alexandria.xtec.cat</option>
                        <option value="repositori.educacio.gencat.cat">repositori.educacio.gencat.cat</option>
                        <option disabled="disabled" value="merli.xtec.cat">merli.xtec.cat</option>
                        <option value="apliense.xtec.cat">apliense.xtec.cat</option>
                        <option value="clic.xtec.cat">jClic</option>
                        <option value="other">Altres fonts</option>
                    </select>
                    
                    <div id="jclic_options" style="display: none; margin-top:15px;">
                        
                            <input type="radio" name="jClicUpdate" id="jClicUpdate1" value="update" checked >
                            <label for="jClicUpdate1">
                                Actualitzar
                            </label>
                            <br />
                            <input type="radio" name="jClicUpdate" id="jClicUpdate2" value="insert" >
                            <label for="jClicUpdate2">
                                Trobar només nous jclic
                            </label>
                            <br />
                            <input type="radio" name="jClicUpdate" id="jClicUpdate3" value="upsert">
                            <label for="jClicUpdate3">
                                Actualitza y troba nous jclic
                            </label>
                    </div>
                    <div id="other_source_input" style="display: none; margin-top:15px;">
                        <label>Altres fonts URL:</label>
                        <input class="form-control" type="text" name="other_source" id="other_source">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col col-xs-12 col-lg-6">
                    <br />
                    <button class="btn btn-primary" id="scrappButton" name="submit" class="margin-top:10px;">Cercar</button>
                        <div id="cercar_ajax_response">
                    </div>                        
                </div>
            </div>
        </form>
    </div>    
    <div id="subtab2" class="subtab-content">
        <div class="row">
            <div class="col col-xs-12 col-lg-2">            
                <h4>Filtres</h4>
                <form name="filtresImportacions" id="filtresImportacions">
                    <label for="status">Post Type</label>
                    <select class="form-select bg-light" name="post_type" id="fi_post_type">
                        <option value="">Tots</option>
                        <option value="null">Sense assignar</option>
                        <option value=""> --------- </option>
                            <?php 
                                foreach ($post_types as $key => $value) {
                                    echo '<option value="'.$value.'" >' . $value . '</option>';
                                }
                            ?>                                               
                    </select>
                    <label for="status">Estat</label>
                    <select class="form-select bg-light" name="status" id="fi_status">
                        <option value="">Tots</option>
                        <option value="ignore">Sense tractar</option>
                        <option value="draft_publish">Post creat (draft y public)</option>
                        <option value="draft">Post draft</option>
                        <option value="publish">Post publicat</option>
                    </select>
                    <label for="source">Origen</label>
                    <select class="form-select bg-light" name="source" id="fi_source" >
                        <option value="">Tots</option>
                        <option value="alexandria">Alexandria</option>
                        <option value="rde">Repositori Educació</option>
                        <option value="toolbox">Toolbox</option>
                        <option value="apliense">Apliense (ARC)</option>
                        <option value="merli">Merli</option>
                        <option value="jclic">jClic</option>
                    </select> 
                    <label for="title">Títol</label>
                    <input type="text" class="form-control bg-light" name="title" id="fi_title" placeholder="Ex: kahoo" >
                    <label for="title">Descripció</label>
                    <input type="text" class="form-control bg-light" name="descripcion" id="fi_descripcion" placeholder="Ex: composicions audiovisuals">
                    <label for="title">Data d'importació (des)</label>
                    <input type="date" class="form-control bg-light" name="created_at_desde" id="fi_created_at_desde" >
                    <label for="title">Data d'importació (fins)</label>
                    <input type="date" class="form-control bg-light" name="created_at_hasta" id="fi_created_at_hasta">
                    <label for="title">JSON Key</label> <i class="fa-solid fa-circle-info"></i>
                    <input type="text" autocomplete="on" class="form-control bg-light" name="json_key" id="fi_json_key" placeholder="tipo_recurso" />
                    <label for="title">JSON Value</label> <i class="fa-solid fa-circle-info"></i>
                    <input type="text" autocomplete="on" class="form-control bg-light" name="json_value" id="fi_json_value" placeholder="Video" /> 

                    <label style="color:#00f" for="order_by">Ordenació</label>
                    <select class="form-select bg-light" name="order_by" id="fi_order_by" >
                        <option selected value="updated_at">Date Actualització</option>
                        <option value="created_at">Date Creació</option>
                        <option value="title">Títol</option>
                    </select> 
                    <button type="button" id="filtrarDades" class="mt-1 btn btn-success" style="width:70%">Filtrar dades</button> 
                    <button title="Netejar filtres" type="button" id="borrarFiltro" class="mt-1 btn btn-danger" style="width:25%">
                        <i class="fa-solid fa-trash-can"></i>                                
                    </button> 
                    <br />                       
                    <br />                       
                    <div id="actions" id="actions" >  
                        <h4>Accions sobre selecció</h4>                          
                        <select class="form-select bg-light" id="scrappingActionsSelect" >                        
                            <option data-post_status="draft" value="upsertPost">
                                Crear/Actualitzar post (draft)
                            </option>
                            <option data-post_status="publish" value="upsertPost">
                                Crear/Actualitzar post (publish)
                            </option>
                            <?php 
                                foreach ($post_types as $key => $value) {
                                    echo '<option data-post_type="' . $key . '" data-post_status="draft" value="upsertPost" >
                                        Crear/Actualitzar post ( post_type: `' . $value . '` | post_status -> `draft` )
                                    </option>';
                                    echo '<option data-post_type="' . $key . '" data-post_status="publish" value="upsertPost" >
                                        Crear/Actualitzar post ( post_type: `' . $value . '` | post_status -> `publish` )
                                    </option>';
                                }

                            ?>
                        </select>                         
                        <button type="button" id="clickOnSelectedAction" class="mt-1 btn btn-primary" style="width:100%">Llançar procés seleccionat</button> 
                        <div class="mt-1">                                
                            <label class="" for="applyToAllFilter">
                                <input class="" type="checkbox" value="1" name="applyToAllFilter" id="applyToAllFilter">
                                Ignorar seleccio, aplicar a totes les pagines de les dades filtrades 
                                <span id="totalPagesFiltres"><?php 
                                    echo "(<strong>" .$resultScrappings['total'] . "</strong> registres)";
                                ?></span>
                            </label>
                        </div>                            
                    </div>                           
                </form>
            </div>            
            <div class="col col-xs-12 col-lg-10">   
                <form name="formSelectionRows" id="formSelectionRows" >
                    <div class="row p-2"  >
                        <div class="col col-xs-12 col-md-6" id="selectionMultipleImportacion"  >
                            <button title="Selecció només a la pàgina actual" data-destino="importacionsSelecterCheckbox" class="seleccionaTodo btn btn-dark btn-sm">
                                <i class="fa-regular fa-square"></i> Tots 
                            </button>
                            <button title="Afecta només a la pàgina actual" data-destino="importacionsSelecterCheckbox" class="deSeleccionaTodo btn btn-dark btn-sm">
                                <i class="fa-regular fa-square-check"></i> Ningú
                            </button>  
                        </div>            
        
                        <div id="paginatorImportacions" class="col col-xs-12 col-md-6" ><?php 
                            echo $resultScrappings['paginator'];
                        ?></div>            
                    </div>            
                    <table class="table" id="tablaImportacions"><?php 
                        echo $resultScrappings['html'];
                    ?></table>            
                </form>            
            </div>            
        </div>   
    </div>
    <div id="hide___subtab3" class="subtab-content">
        <h4>Configuració de scrapping</h4>
        <hr />
        <button type="button" class="btn btn-primary" id="createCategoriesToolbox" >
            Crear categories de Toolbox
        </button>
    </div>
</div>    

<div id="tab2" class="tab-content">
    <h2>Aportacion de usuaris</h2>
    <p>Aquí puedes colocar cualquier contenido que desees en la primera pestaña.</p>
</div>

<!--
<div id="popUpModalInfoBox" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content popupStyle" ></div>        
    </div>    
</div>
-->

<div class="modal modal-lg fade " id="popUpModalInfoBox" tabindex="-1" role="dialog" aria-labelledby="popUpModalInfoBoxLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="popUpModalInfoBoxLabel">Creación de posts</h5>
                <button type="button" class="closeThePopup" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
            </div>
        </div>
    </div>
</div>
        
   
