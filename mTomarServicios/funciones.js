function llenarSegServicios(){
    $('#titulo').show();
    $('#botonTitle').hide();
    $.ajax({
        url:"llenarListaS.php",
        type:"POST",
        dateType:"html",
        data:{},
        success:function(respuesta){

            $("#llenar_listaS").html(respuesta);
        },
        error:function(xhr,status){
            alert("no se muestra");
        }
    });
}

function llenarSolPendientes(){
    $('#titulo').show();
    $('#botonTitle').hide();
    $.ajax({
        url:"llenarLista.php",
        type:"POST",
        dateType:"html",
        data:{},
        success:function(respuesta){
            $('#inventarios').hide();
            $('#resultadoSolPendientes').fadeIn(1000);

            $("#resultadoSolPendientes").html(respuesta);
        },
        error:function(xhr,status){
            alert("no se muestra");
        }
    });
}

function llenarListax(){
    $('#titulo').show();
    $('#botonTitle').hide();
    $.ajax({
        url:"llenarListaX.php",
        type:"POST",
        dateType:"html",
        data:{},
        success:function(respuesta){
            $('#inventarios').hide();
            $('#resultadoSolPendientes').fadeIn(1000);

            $("#llenar_listaXX").html(respuesta);
        },
        error:function(xhr,status){
            alert("no se muestra");
        }
    });
}

function inventarios(id_servicio,id_area,ServSol){
    $('#titulo').hide();
    $('#botonTitle').show();
    $.ajax({
        url:"inv.php",
        type:"POST",
        dateType:"html",
        data:{'id_servicio':id_servicio,'id_area':id_area,'ServSol':ServSol},
        success:function(respuesta){
            $('#resultadoSolPendientes').hide();            
            $('#inventarios').fadeIn(1000);

            $("#inventarios").html(respuesta);
            
            idServicio=id_servicio;

            llenar_servicios(id_area);
        },
        error:function(xhr,status){
            alert("no se muestra");
        }
    });
}

//CONFIRMACION
function confirmar(id){
    $("#modalConfirmacion").modal('show');
    $("#botonTomarServicio").attr("onclick","tomarServicio("+id+");");
 }

//FOTOGRAFIA
function foto(foto,area,persona){
    swal({
  title: area,
  text: persona,
  imageUrl: foto,
  imageWidth: 300,
  imageHeight: 300,
  animation: true
})
}

function imprimirx(id){
    
swal({
  title: 'Documento PDF',
  text: "¿Deseas visualizar la hoja de servicio con folio "+id+" ?",
  type: 'question',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Vista Previa',
  cancelButtonText: 'No, cancelar!'
}).then(function () {
    // window.location="pdfServicios.php?id="+id; 
    window.open("pdfServicios.php?id="+id, '_blank');
})
}

function imprimir(id){
    
swal({
  title: 'Descarga en PDF',
  text: "¿Deseas descargar la hoja de servicio con el folio "+id+" ?",
  type: 'question',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Descargar',
  cancelButtonText: 'No, cancelar!'
}).then(function () {
    window.location="../plugins/html2toPDF/hServicePDF.php?id="+id; 
})
}
//ENVIA VALOR A UN ARCHIVO PHP
function tomarServicio(id){
swal({
  title: 'Eligiendo Servicio',
  text: "Deseas elegir este servicio?",
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Si, eligo este servicio!',
  cancelButtonText: 'Salir!'
}).then(function () {

// --------------------------------
    $.ajax({
        url:"servicioTomado.php",
        type:"POST",
        dateType:"html",
        data:{'id':id},
        success:function(respuesta){
            
            //aqui es donde se especifican las funciones en caso de que la peticion sea correcta
            // $("#modalConfirmacion").modal('hide');
            swal("¡Servicio Elegido!", "El servicio ha sido separado a tu Lista", "success");
            llenarSolPendientes();
        },
        error:function(xhr,status){
            //no se encontro el archivo donde se procesa la peticion Ajax
            alert("no se muestra");
        }
    });
// --------------------------------
})
}

//ENVIAR VARIOS VALORES DE UN FORMULARIO POR MEDIO DE BOTON
 function enviarValorBotones(valor,idRegistro){

switch(valor){
    case "Con inventario":
    texto="mobiliario con inventario"
    break;
    case "Sin inventario":
    texto="mobiliario sin inventario"
    break;
    case "Especial":
    texto="no interviene mobiliario"
    break;   
}

swal({
  title: '¿Estas seguro?',
  text: "Acabas de seleccionar - "+texto,
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Aceptar',
  cancelButtonText: 'Cancelar'
}).then(function () {
    $.ajax({
        url:"cambiarValor.php",
        type:"POST",
        dateType:"html",
        data:{'idRegistro':idRegistro,'valor':valor},
        success:function(respuesta){

            llenarSolPendientes();
            
        },
        error:function(xhr,status){
            //no se encontro el archivo donde se procesa la peticion Ajax
            alert("no se muestra");
        }
    });
})


}

function enviarDatos(id_registro){
    $("#idRegistro").val(id_registro);
    $("#modalBotones").modal("show");
}

function enviarDatos2(id_registro){
    $("#idRegistro").val(id_registro);
    $("#modalBotones2").modal("show");
}

//actualizar servicio con mobiliairo
function modificar(idMobiliario,idServicio,inv,desc,accion,tipo,id_area){
    $("#idMobiliario").val(idMobiliario);
    $("#idServicio").val(idServicio);
     $("#folio").text(idServicio);
    $("#invx").val(inv);
    $('textarea#descx').val(desc);
    $("textarea#accion").val(accion);

    $("#tipo").val(tipo);
    $("#modalEditar").modal("show");

    $('.modal').on('shown.bs.modal', function (e) {
    $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    });

    $('#botonI').attr("disabled", true);


}

function modalSI(idServicio,servicio,id_area,tipo){

    $("#idMobiliarioSI").val(0);
    $("#idServicioSI").val(idServicio);
    $("#folioSI").text(idServicio);
    $("#invxSI").val("Mobiliario sin inventario");
    $('textarea#descxSI').val("No hay descripción");
    $("textarea#solSI").val(servicio);
    llenar_serviciosSI(id_area);
    $("#modalSinInv").modal("show");

    $('.modal').on('shown.bs.modal', function (e) {
    $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    });

    $('#botonSI').attr("disabled", true);

}

function modalSM(idServicio,servicio,id_area,tipo){

    $("#idMobiliarioSM").val(0);
    $("#idServicioSM").val(idServicio);
    $("#folioSM").text(idServicio);
    $("#invxSM").val("No interviene mobiliario");
    $('#descxSM').val("Al no ser mobiliario no existe descripción del mismo");
    $("textarea#solSM").val(servicio);
    llenar_serviciosSM(id_area);
    $("#modalSinMob").modal("show");

    $('.modal').on('shown.bs.modal', function (e) {
    $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    });

    $('#botonSM').attr("disabled", true);

}

//MODAL MUESTRA FOLIO
function ventanaFolio(desc,folio,area,fecha,hora,sede){
    $("#descVF").text(desc);
    $("#folioVF").text(folio);
    $("#areaVF").text(area);
    $("#fechaVF").text(fecha);
    $("#horaVF").text(hora);
    $("#sedeVF").text(sede);

    $("#ventanaFolio").modal('show');
 }

 //MODAL MUESTRA PROCESO
function ventanaProceso(solicitante,folio,tel,fecha,hora,sede,fechat,horat,servicio){
    $("#solicitanteVP").text(solicitante);
    $("#folioVP").text(folio);
    $("#telVP").text(tel);
    $("#fechaVP").text(fecha);
    $("#horaVP").text(hora);
    $("#sedeVP").text(sede);
    $("#fechaVPt").text(fechat);
    $("#horaVPt").text(horat);

  switch (servicio) {
    case 'Con inventario':
        cServicio="El servicio incluye mobiliario con un inventario registrado";
    break;
    case 'Sin inventario':
        cServicio="El servicio incluye mobiliario sin un inventario registrado";
    break;
    case 'Especial':
        cServicio="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
    break;
    default:
        cServicio="Aún no se ha seleccionado ningun tipo de servicio";
      break;
  }

    $("#servicioVP").text(cServicio);

    $("#ventanaProceso").modal('show');
 }

 //MODAL MUESTRA TERMINADO
function ventanaTerminado(solicitante,folio,tel,fecha,hora,sede,fechat,horat,servicio,fechatt,horatt,sol,tipo){
    $("#solicitanteVT").text(solicitante);
    $("#folioVT").text(folio);
    $("#telVT").text(tel);
    $("#fechaVT").text(fecha);
    $("#horaVT").text(hora);
    $("#sedeVT").text(sede);
    $("#fechaVPtT").text(fechat);
    $("#horaVPtT").text(horat);
    $("#fechaVTER").text(fechatt);
    $("#horaVTER").text(horatt);
    $("#solVTER").text(sol);
    $("#tipoVTER").text(tipo);
  switch (servicio) {
    case 'Con inventario':
        cServicio="El servicio incluye mobiliario con un inventario registrado";
    break;
    case 'Sin inventario':
        cServicio="El servicio incluye mobiliario sin un inventario registrado";
    break;
    case 'Especial':
        cServicio="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
    break;
    default:
        cServicio="Aún no se ha seleccionado ningun tipo de servicio";
      break;
  }

    $("#servicioVT").text(cServicio);

    $("#ventanaTerminado").modal('show');
 }

 //MODAL MUESTRA FOLIO
function ventanaDesc(inv,desc,marca,modelo,serie,resp,tipo){
    $("#inv").text(inv);
    $("#desc").text(desc);
    $("#marca").text(marca);
    $("#modelo").text(modelo);
    $("#serie").text(serie);
    $("#resp").text(resp);
    $("#tipo").text(tipo);

    $("#ventanaDesc").modal('show');
 }

 function llenar_servicios(id_area)
{
    $.ajax({
        url : 'servicios.php',
        data : {'id_area':id_area},
        type : 'POST',
        dataType : 'html',
        success : function(respuesta) {
            $("#servicios").empty();
            $("#servicios").html(respuesta);      
        },
 
        error : function(xhr, status) {
            alert('Disculpe, existió un problema');
        },
    });
}

 function llenar_serviciosSI(id_area)
{
    $.ajax({
        url : 'servicios.php',
        data : {'id_area':id_area},
        type : 'POST',
        dataType : 'html',
        success : function(respuesta) {
            $("#serviciosSI").empty();
            $("#serviciosSI").html(respuesta);      
        },
 
        error : function(xhr, status) {
            alert('Disculpe, existió un problema');
        },
    });
}

 function llenar_serviciosSM(id_area)
{
    $.ajax({
        url : 'servicios.php',
        data : {'id_area':id_area},
        type : 'POST',
        dataType : 'html',
        success : function(respuesta) {
            $("#serviciosSM").empty();
            $("#serviciosSM").html(respuesta);      
        },
 
        error : function(xhr, status) {
            alert('Disculpe, existió un problema');
        },
    });
}

function msjElegido(persona){
swal({
  title: 'El servicio ya ha sido separado',
  html: $('<div>')
    .addClass('some-class')
    .text(persona),
  animation: false,
  customClass: 'animated rubberBand'
})
}

function MSJseleccionarTexto(id){
document.getElementById(id).selectionStart = 0;
alertify.message('<b>Puedes modificar el texto</b>',2);
}

function MSJmostrarValor(valor){
alertify.message('<b>Has elegido el servicio : </b>'+ valor,3);
$('#botonI').attr("disabled", false);
$('#botonSM').attr("disabled", false);
$('#botonSI').attr("disabled", false);
}


function MSJaccion(){
alertify.message('<b>Escribe la solución al servicio. </b>',3);
}

function cancelarSeleccion(valor){

swal({
  title: 'Estas seguro de realizar esta operacion?',
  text: "!Esta apunto de cancelar el tipo de servicio¡",
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Si, deseo cancelar el tipo de servicio!',
  cancelButtonText: 'Salir!'
}).then(function () {
    var idServ = valor;
    $.ajax({
        url:"cancelarSel.php",
        type:"POST",
        dateType:"html",
        data:{'idServ':idServ},
        success:function(respuesta){
            // $('#inventarios').hide();
            $("#modalEditar").modal("hide");
            $("#modalSinMob").modal("hide");
            $("#modalSinInv").modal("hide");
            llenarSolPendientes();
        },
        error:function(xhr,status){
            alert("no se muestra");
        }
    });
})
}

function solTerminox(fecha,hora,folio,persona,extencion,fechas,horas,clasificacion,fechat,horat,accion,tipo,area){
    $("#modalTerminox").modal('show');
    document.getElementById("fechapet2").innerHTML=fecha;
    document.getElementById("horapet2").innerHTML=hora;
    document.getElementById("folio2").innerHTML=folio;
    document.getElementById("persona2").innerHTML=persona;
    document.getElementById("extencion2").innerHTML=extencion;
    document.getElementById("fechaser2").innerHTML=fechas;
    document.getElementById("horaser2").innerHTML=horas;
    switch(clasificacion){
                case 'Con inventario':
                    document.getElementById("clasi2").innerHTML="El servicio incluye mobiliario con un inventario registrado";
                break;
                case 'Sin inventario':
                    document.getElementById("clasi2").innerHTML="El servicio incluye mobiliario sin un inventario registrado";
                break;
                case 'Especial':
                    document.getElementById("clasi2").innerHTML="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
                break;
                default:
                   document.getElementById("clasi2").innerHTML="Aún no se ha seleccionado ningun tipo de servicio";
                  break;        
    }
    document.getElementById("fechater").innerHTML=fechat;
    document.getElementById("horater").innerHTML=horat;
    document.getElementById("solucion").innerHTML=accion;
    document.getElementById("tipo").innerHTML=tipo;
    document.getElementById("area").innerHTML=area;
    // $('.modal').on('shown.bs.modal', function (e) {
    // $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    // });

}

function guardarFechaSol(){
swal({
  title: 'Eligiendo Servicio',
  text: "Deseas elegir este servicio?",
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Si, eligo este servicio!',
  cancelButtonText: 'Salir!'
}).then(function () {

// --------------------------------
    $.ajax({
        url:"servicioTomado.php",
        type:"POST",
        dateType:"html",
        data:{'id':id},
        success:function(respuesta){
            
            //aqui es donde se especifican las funciones en caso de que la peticion sea correcta
            // $("#modalConfirmacion").modal('hide');
            swal("¡Servicio Elegido!", "El servicio ha sido separado a tu Lista", "success");
            llenarSolPendientes();
        },
        error:function(xhr,status){
            //no se encontro el archivo donde se procesa la peticion Ajax
            alert("no se muestra");
        }
    });
// --------------------------------
})
}

function cambiarFecha(folio,persona,accion,tipo,fechaSol,horaSol,fechaServ,horaServ){
    $("#modalCambiarFecha").modal('show');
    document.getElementById("folio3").innerHTML=folio;
    document.getElementById("persona3").value=persona;
    document.getElementById("fechaSol").value=fechaSol;
    document.getElementById("horaSol").value=horaSol;
    document.getElementById("fechaServ").value=fechaServ;
    document.getElementById("horaServ").value=horaServ;
    document.getElementById("foliox").value=folio;
    // $('.modal').on('shown.bs.modal', function (e) {
    // $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    // });

}

function actFecha(){

    var fechaSol  = document.getElementById("fechaSol").value;
    var horaSol   = document.getElementById("horaSol").value;
    var fechaServ = document.getElementById("fechaServ").value;
    var horaServ  = document.getElementById("horaServ").value;
    var folio     = document.getElementById("foliox").value;
    var numero    = document.getElementById("num").value;
    
    // alert(fechaSol+' '+horaSol+' '+ fechaSol+ ' '+fechaServ+' '+ fechaServ+ ' '+folio);
    
    $.ajax({
    url:"cambiarFecha.php",
    type:"POST",
    dateType:"html",
    data:{'fechaSol':fechaSol,'horaSol':horaSol,'fechaServ':fechaServ,'horaServ':horaServ,'folio':folio},
    success:function(respuesta){
        
        // swal("¡Datos Actualizados!", "Se ha actualizado la información", "success");
        alertify.success('Registro actualizado',2);
        $("#modalCambiarFecha").modal('hide');

        var indentificador ="#"+"fila"+numero
        $(indentificador).addClass("registroVerde");

    },
    error:function(xhr,status){
        //no se encontro el archivo donde se procesa la peticion Ajax
        alert("no se muestra");
    }
});
}

function cambiarFechaX(folio,persona,accion,tipo,fechaSol,horaSol,fechaServ,horaServ,num){
    $("#modalCambiarFecha").modal('show');
    document.getElementById("folio3").innerHTML=folio;
    document.getElementById("persona3").value=persona;
    // document.getElementById("fechaSol").value=fechaSol;
    // document.getElementById("horaSol").value=horaSol;
    // document.getElementById("fechaServ").value=fechaServ;
    // document.getElementById("horaServ").value=horaServ;
    document.getElementById("foliox").value=folio;
    document.getElementById("num").value=num;
    $.ajax({
        type: 'post',
        url: 'llenarModal.php',
        dataType: 'json',
        data:{'folio':folio},
        success: function(res){
            //alert(res.resultado);
            var mystr = res.resultado;//guardo la variable resultante en otra
            var myarr = mystr.split("|");//le paso la funcion split para cortar la cadena
            var var1 = myarr[0];//guardo el primer elemento del arreglo
            var var2 = myarr[1];//guardo el segundo elemento del arreglo
            var var3 = myarr[2];//guardo el segundo elemento del arreglo
            var var4 = myarr[3];//guardo el segundo elemento del arreglo

        document.getElementById("fechaSol").value=var1;
        document.getElementById("horaSol").value=var2;
        document.getElementById("fechaServ").value=var3;
        document.getElementById("horaServ").value=var4;

        },
        error:function(xhr,status){
            alert("No se Ha podido ingresar");
        },
    });
}

function solProceso(fecha,hora,folio,persona,extencion,fechas,horas,clasificacion){
    $("#modalProceso").modal('show');
    document.getElementById("fechapet1").innerHTML=fecha;
    document.getElementById("horapet1").innerHTML=hora;
    document.getElementById("folio1").innerHTML=folio;
    document.getElementById("persona1").innerHTML=persona;
    document.getElementById("extencion1").innerHTML=extencion;
    document.getElementById("fechaser1").innerHTML=fechas;
    document.getElementById("horaser1").innerHTML=horas;
    switch(clasificacion){
                case 'Con inventario':
                    document.getElementById("clasi1").innerHTML="El servicio incluye mobiliario con un inventario registrado";
                break;
                case 'Sin inventario':
                    document.getElementById("clasi1").innerHTML="El servicio incluye mobiliario sin un inventario registrado";
                break;
                case 'Especial':
                    document.getElementById("clasi1").innerHTML="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
                break;
                default:
                   document.getElementById("clasi1").innerHTML="Aún no se ha seleccionado ningun tipo de servicio";
                  break;        
    }


    // $('.modal').on('shown.bs.modal', function (e) {
    // $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    // });

}

function solTermino(fecha,hora,folio,persona,extencion,fechas,horas,clasificacion,fechat,horat,accion,tipo){
    $("#modalTermino").modal('show');
    document.getElementById("fechapet2").innerHTML=fecha;
    document.getElementById("horapet2").innerHTML=hora;
    document.getElementById("folio2").innerHTML=folio;
    document.getElementById("persona2").innerHTML=persona;
    document.getElementById("extencion2").innerHTML=extencion;
    document.getElementById("fechaser2").innerHTML=fechas;
    document.getElementById("horaser2").innerHTML=horas;
    switch(clasificacion){
                case 'Con inventario':
                    document.getElementById("clasi2").innerHTML="El servicio incluye mobiliario con un inventario registrado";
                break;
                case 'Sin inventario':
                    document.getElementById("clasi2").innerHTML="El servicio incluye mobiliario sin un inventario registrado";
                break;
                case 'Especial':
                    document.getElementById("clasi2").innerHTML="El servicio pertenece a una peticion especial y en la cual no hay mobiliario involucrado";
                break;
                default:
                   document.getElementById("clasi2").innerHTML="Aún no se ha seleccionado ningun tipo de servicio";
                  break;        
    }
    document.getElementById("fechater").innerHTML=fechat;
    document.getElementById("horater").innerHTML=horat;
    document.getElementById("solucion").innerHTML=accion;
    document.getElementById("tipo").innerHTML=tipo;

    // $('.modal').on('shown.bs.modal', function (e) {
    // $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    // });

}

function areaSolicitada(area,servicio,fecha,hora,folio){
    $("#modalArea").modal('show');
    document.getElementById("areaSolicitada").innerHTML=area;
    document.getElementById("desc").innerHTML=servicio;
    document.getElementById("fechapet").innerHTML=fecha;
    document.getElementById("horapet").innerHTML=hora;
    document.getElementById("folio").innerHTML=folio;

    // $('.modal').on('shown.bs.modal', function (e) {
    // $(this).find('input, textarea, select').filter(':visible:first').focus(); 
    // });

}

function liberar(id){
swal({
  title: 'Liberar Servicio',
  text: "¿ Estas seguro de querer liberar el servicio ?",
  type: 'warning',
  showCancelButton: true,
  confirmButtonColor: '#3085d6',
  cancelButtonColor: '#d33',
  confirmButtonText: 'Si, liberar este servicio!',
  cancelButtonText: 'No, cancelar acción!',
  confirmButtonClass: 'btn btn-success',
  cancelButtonClass: 'btn btn-danger'
  // buttonsStyling: false
}).then(function () {
        $.ajax({
            url:"servicioLiberado.php",
            type:"POST",
            dateType:"html",
            data:{'id':id},
            success:function(respuesta){
                
                //aqui es donde se especifican las funciones en caso de que la peticion sea correcta
              swal(
                'Liberado!',
                'El servicio ha sido liberado.',
                'success'
              );
                llenarListax();
            },
            error:function(xhr,status){
                //no se encontro el archivo donde se procesa la peticion Ajax
                alert("no se muestra");
            }
        });
}, function (dismiss) {
  // dismiss can be 'cancel', 'overlay',
  // 'close', and 'timer'
  if (dismiss === 'cancel') {
    swal(
      'Cancelado',
      'No has liberado el servicio',
      'error'
    )
  }
})
}

function probar(num){
    // alert(num);
    var indentificador ="#"+"fila"+num
    $(indentificador).addClass("registroVerde");
}