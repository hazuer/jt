<div class="modal fade" id="modal-folio" tabindex="-1" role="dialog" aria-labelledby="modal-folio-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><span id="modal-folio-title"> </span></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mfIdLocation">Ubicacion:</label>
                            <select name="mfIdLocation" id="mfIdLocation" class="form-control" disabled>
                                <option value="1">Tlaquiltenango</option>
                                <option value="2">Zacatepec</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mfModo">Modo:</label>
                            <select name="mfModo" id="mfModo" class="form-control">
                                <option value="1">Automatico</option>
                                <option value="2">Personalizado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                <div class="col-md-6">
                        <div class="form-group">
                            <label for="mfFolioActual">Folio Actual:</label>
                            <input type="text" class="form-control" name="mfFolioActual" id="mfFolioActual" value="" autocomplete="off" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mfNumFolio">* Folio:</label>
                            <input type="text" class="form-control" name="mfNumFolio" id="mfNumFolio" value="" autocomplete="off" >
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-save-folio" type="button" class="btn btn-success" title="Guardar">Guardar</button>
                <button type="button" class="btn btn-danger" title="Cerrar" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>