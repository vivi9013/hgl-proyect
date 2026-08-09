$("#formActualizarx").submit(function(e){
    var idServ = $("#idServicio").val();
    var idMob= $("#idMobiliario").val();
    var desc = $("#descx").val();
    var inv = $("#invx").val();
    var accion = $("#accion").val();
    var tipo = $("#servicios").val();

    // alert(idServ+"-"+idMob+"-"+desc+"-"+inv+"-"+accion+"-"+tipo);

   $.ajax({
        url:"modificar.php",
        type:"POST",
        dateType:"html",
        data:{'idServ':idServ,'idMob':idMob,'desc':desc,'inv':inv,'accion':accion,'tipo':tipo},
        success:function(respuesta){
            
            $("#modalEditar").modal("hide");

             swal("¡Servicio Terminado!", "El servicio ha sido termiando por parte del usuario de soporte técnico", "success");

             llenarSolPendientes();
            
        },
        error:function(xhr,status){
            alert(xhr);
        },
    });
    e.preventDefault();
    return false;
 
});

$("#formActualizarxSI").submit(function(e){
    var idServ = $("#idServicioSI").val();
    var idMob= $("#idMobiliarioSI").val();
    var desc = $("#descxSI").val();
    var inv = $("#invxSI").val();
    var accion = $("#accionSI").val();
    var tipo = $("#serviciosSI").val();

    // alert(idServ+"-"+idMob+"-"+desc+"-"+inv+"-"+accion+"-"+tipo);

   $.ajax({
        url:"modificar.php",
        type:"POST",
        dateType:"html",
        data:{'idServ':idServ,'idMob':idMob,'desc':desc,'inv':inv,'accion':accion,'tipo':tipo},
        success:function(respuesta){
            
            $("#modalSinInv").modal("hide");

             swal("¡Servicio Terminado!", "El servicio ha sido termiando por parte del usuario de soporte técnico", "success");

             llenarSolPendientes();
            
        },
        error:function(xhr,status){
            alert(xhr);
        },
    });
    e.preventDefault();
    return false;
 
});


$("#formActualizarxSM").submit(function(e){
    var idServ = $("#idServicioSM").val();
    var idMob= $("#idMobiliarioSM").val();
    var desc = $("#descxSM").val();
    var inv = $("#invxSM").val();
    var accion = $("#accionSM").val();
    var tipo = $("#serviciosSM").val();

    // alert(idServ+"-"+idMob+"-"+desc+"-"+inv+"-"+accion+"-"+tipo);

   $.ajax({
        url:"modificar.php",
        type:"POST",
        dateType:"html",
        data:{'idServ':idServ,'idMob':idMob,'desc':desc,'inv':inv,'accion':accion,'tipo':tipo},
        success:function(respuesta){
            
            $("#modalSinMob").modal("hide");

             swal("¡Servicio Terminado!", "El servicio ha sido termiando por parte del usuario de soporte técnico", "success");

             llenarSolPendientes();
            
        },
        error:function(xhr,status){
            alert(xhr);
        },
    });
    e.preventDefault();
    return false;
 
});