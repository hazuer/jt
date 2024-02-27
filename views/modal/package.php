<div class="modal fade" id="modal-package" tabindex="-1" role="dialog" aria-labelledby="modal-package-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><span id="modal-package-title"> </span></h3>
                <button id="close-qr-x" type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-modal-package" name="form-modal-package" class="form" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="hidden" name="id_package" id="id_package" value="" >
                        <input type="hidden" name="folio" id="folio" value="" >
                        <input type="hidden" name="id_contact" id="id_contact" value="" >
                        <input type="hidden" name="action" id="action" value="" >
                    </div>

                    <div class="row" id="div-keep-modal">
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="keepModal" id="opcMA" value="option1">
                            <label class="form-check-label" for="opcMA">Guardar y volver a registrar</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="keepModal" id="opcGC" value="option2">
                            <label class="form-check-label" for="opcGC">Guardar y cerrar</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_location">Ubicacion:</label>
                                <select name="id_location" id="id_location" class="form-control" disabled>
                                <option value="1">Tlaquiltenango</option>
                                <option value="2">Zacatepec</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="c_date">Fecha:</label>
                                <input type="text" class="form-control" name="c_date" id="c_date" value="" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">* Télefono:</label>
                                <input type="text" class="form-control" name="phone" id="phone" value="" autocomplete="off" >
                            </div>
                            <div id="coincidencias" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="receiver">* Nombre:</label>
                                <input type="receiver" class="form-control" name="receiver" id="receiver" value="" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="tracking">* Guía:</label>
                                <input type="text" class="form-control" name="tracking" id="tracking" value="" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="div-status">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_status">Status:</label>
                                <select name="id_status" id="id_status" class="form-control">
                                    <option value="1">Nuevo</option>
                                    <option value="2">SMS Enviado</option>
                                    <option value="4">Devuelto</option>
                                    <option value="5">Eliminado</option>
                                    <option value="7">Contactado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="note">Nota:</label>
                                <input type="note" class="form-control" name="note" id="note" value="" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button id="btn-erase" type="button" class="btn btn-default" title="Borrar">Borrar</button>
                <button id="btn-save" type="button" class="btn btn-success" title="Guardar">Guardar</button>
                <button id="close-qr-b" type="button" class="btn btn-danger" title="Cerrar" data-dismiss="modal">Cerrar</button>
                <audio id="beep-sound" style="display: none;">
                        <source src="<?php echo BASE_URL;?>/assets/beep-sound.mp3" type="audio/mpeg">
                </audio>
            </div>
        </div>
    </div>
</div>