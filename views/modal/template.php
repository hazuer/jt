<div class="modal fade" id="modal-template" tabindex="-1" role="dialog" aria-labelledby="modal-template-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><span id="modal-template-title"> </span></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mTIdLocation"><b>Ubicación:</b></label>
                            <select name="mTIdLocation" id="mTIdLocation" class="form-control" disabled>
                                <option value="1">Tlaquiltenango</option>
                                <option value="2">Zacatepec</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mTDia"><b>Día:</b></label>
                            <select name="mTDia" id="mTDia" class="form-control">
                                <option value="0">Otro</option>
                                <option value="1">Domingo</option>
                                <option value="2">Lunes</option>
                                <option value="3">Martes</option>
                                <option value="4">Míercoles</option>
                                <option value="5">Jueves</option>
                                <option value="6">Viernes</option>
                                <option value="7">Sábado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mTName"><b>* Nombre:</b></label>
                            <input type="text" class="form-control" name="mTName" id="mTName" value="" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2"  style="margin-bottom: 20px;">
                        <label for="mTTemplate"><b>Variables:</b></label>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-dark">_Ubicación_</span>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-dark">_Día_entrega_1_</span>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-dark">_Día_entrega_2_</span>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-dark">_Día_devolución_</span>
                    </div>
                    <div class="col-md-2">
                        <span class="badge badge-dark">_Año_</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="mTTemplate"><b>Plantilla:</b></label>
                            <textarea class="form-control" id="mTTemplate" name="mTTemplate" rows="5">Te notificamos que tu paquete con J&T - _Ubicación_ está listo para ser recogido. Podrás hacerlo en los siguientes días y horarios: _Día_entrega_1_ y _Día_entrega_2_, de 10:00 a.m. a 3:00 p.m. Si no puedes hacerlo dentro de este plazo, tu paquete será devuelto el _Día_devolución_ de _Año_ a las 11:00 a.m.
Por favor, asegúrate de ajustarte a los días y horarios mencionados. Recuerda que no hay servicio de entrega los sábados y domingos.
Ten en cuenta que J&T ya no realiza entregas a domicilio, por lo que deberás recoger tu paquete en el lugar indicado:https://maps.app.goo.gl/pj2QbZCFF3xcKzD7A
Recuerda presentar una identificación al momento de recoger el paquete. Puede ser cualquier persona que designes.
¡Gracias y esperamos que disfrutes de tu paquete!</textarea>
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