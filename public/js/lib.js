const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const pop = urlParams.get('popup');
function uniqid(prefix, more_entropy) {
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +    revised by: Kankrelune (http://www.webfaktory.info/)
  // %        note 1: Uses an internal counter (in php_js global) to avoid collision
  // *     example 1: uniqid();
  // *     returns 1: 'a30285b160c14'
  // *     example 2: uniqid('foo');
  // *     returns 2: 'fooa30285b1cd361'
  // *     example 3: uniqid('bar', true);
  // *     returns 3: 'bara20285b23dfd1.31879087'
  if (typeof prefix == "undefined") {
    prefix = "";
  }
  var retId;
  var formatSeed = function (seed, reqWidth) {
    seed = parseInt(seed, 10).toString(16); // to hex str
    if (reqWidth < seed.length) { // so long we split
      return seed.slice(seed.length - reqWidth);
    }
    if (reqWidth > seed.length) { // so short we pad
      return Array(1 + (reqWidth - seed.length)).join('0') + seed;
    }
    return seed;
  };
  // BEGIN REDUNDANT
  if (!this.php_js) {
    this.php_js = {};
  }
  // END REDUNDANT
  if (!this.php_js.uniqidSeed) { // init seed with big random int
    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
  }
  this.php_js.uniqidSeed++;

  retId = prefix; // start with prefix, add current milliseconds hex string
  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
  if (more_entropy) {
    // for more entropy we add a float lower to 10
    retId += (Math.random() * 10).toFixed(8).toString();
  }
  return retId;
}
function lib_urlAtual(){
  return window.location.href;
}
function goToByScroll2(seletor) {
    // Remove "link" from the ID
    seletor = seletor.replace("link", "");
    // Scroll
	$('html,body').animate({
        scrollTop: $(seletor).offset().top
    }, 'slow');
}
function redirect(url) {
  window.location = url;
}
function redirect_blank(url) {
  var a = document.createElement('a');
  a.target="_blank";
  a.href=url;
  a.click();
}
function lib_urlAmigavel(valor){
	valor = valor.replace('?', "");
	//valor = valor.replace('-', '');
	//valor = valor.replace('-', '');
	valor = valor.replace(/[á|ã|â|à]/gi, "a");
	valor = valor.replace(/[é|ê|è]/gi, "e");
	valor = valor.replace(/[í|ì|î]/gi, "i");
	valor = valor.replace(/[õ|ò|ó|ô]/gi, "o");
	valor = valor.replace(/[ú|ù|û]/gi, "u");
	valor = valor.replace(/[ç]/gi, "c");
	valor = valor.replace(/[ñ]/gi, "n");
	valor = valor.replace(/[á|ã|â]/gi, "a");
	valor = valor.replace('(', "");
	valor = valor.replace('}', "");
	valor = valor.replace('/', "");
	valor = valor.replace(/[\s]/gi, '-'); //Transforma espaço em traço
	valor = valor.replace('---', "-");
	valor = valor.replace('--', "-");
	valor = valor.toLowerCase();
	return valor;
}
function encodeArray(arr){
    var ar = JSON.stringify(arr);
    var encode = btoa(ar);
    return encode
}
function decodeArray(arr){
	var decode = JSON.parse(atob(arr));
	return decode
}
function __translate(val,val2){
	return val;
}
function lib_formatMensagem(locaExive,mess,style,tempo){
	var mess = "<div class=\"alert alert-"+style+" alert-dismissable\" role=\"alert\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">X</button><i class=\"fa fa-exclamation-triangle\"></i>&nbsp;"+mess+"</div>";
	if(typeof(tempo) == 'undefined')
		var tempo = 4000;
    if(tempo>0)
	setTimeout(function(){$(".alert-"+style+"").hide('slow')}, tempo);
	$(locaExive).html(mess);
}
function abrirjanela(url, nome, w, h, param){
	if(param.length > 1)
		var popname = window.open(url, nome, 'width='+w+', height='+h+', scrollbars=yes, '+param);
	else
		var popname = window.open(url, nome, 'width='+w+', height='+h+', scrollbars=yes');
	popname.window.focus();
}
function abrirjanela1(url, nome, w, h, param){
	var largura = $( window ).width() - w;
	var altura   = $( window ).height();
	altura =new Number(altura) + new Number(h);
	var left = new Number(largura);
	//left = (left) - (left/Number(2));
	if(param.length > 1)
		var popname = window.open(url, nome, 'width='+largura+', height='+altura+', left='+10+' scrollbars=yes, '+param);
	else
		var popname = window.open(url, nome, 'width='+largura+', height='+altura+', scrollbars=yes,left='+10+'');
	 popname.window.focus();
}
function abrirjanelaPadrao(url,windo){
	if(typeof windo == 'undefined'){
		windo = "novo_cada";
	}
	var meio = (screen.availWidth - 200)/((screen.availWidth-200)/50);
	if($(window).width() > 900){
		var wid = screen.availWidth - 100;
		//var height = $( window ).height() - ($( window ).height()/4);
		var height = screen.availHeight-90;
	}else{
		var wid = $(window).width();
		var height = screen.availHeight;
		//var height = $(document).height();
		//height = new Number(height) - new Number(100);
	}
	//alert(height);
	abrirjanela(url, windo, wid, height, "left="+meio+",toolbar=no, location=no, directories=no, status=no, menubar=no");
}
function abrirjanelaFull(url,windo){
	if(typeof windo == 'undefined'){
		windo = "janelaFull";
	}
	var params = [
		'height='+screen.height,
		'width='+screen.width,
		'fullscreen=yes' // only works in IE, but here for completeness
	].join(',');

	var popup = window.open(url, windo, params);
	popup.window.focus();
	popup.moveTo(0,0);
	//abrirjanela(url, windo, screen.availWidth, screen.availHeight, ",toolbar=no, location=no, directories=no, status=no, menubar=no");
}
function abrirjanelaPadraoConsulta(url){
	if($(window).width() > 800){
		var meio = 1050 / (6);
		var wid = 1050;
		var height = $( window ).height();
	}else{
		var meio = $(window).width() / (6);
		var wid = $(window).width();
		var height = $( window ).height();
	}
	abrirjanela(url, "consultaCliente", wid, height, "left="+meio+",toolbar=no, location=no, directories=no, status=no, menubar=no");
}
function openPageLink(ev,url,ano){
  ev.preventDefault();
  var u = url.trim()+'?ano='+ano;
	abrirjanelaPadrao(u);
	//window.location = u;
}
function cancelEdit(id,ac){
  //var temaImput = '<input type="{type}" style="width:{wid}px" name="{name}" value="{value}" class="form-control text-center"> {btn}';
  if(typeof ac == 'undefined'){
    ac = 'edit';
  }
  var temaImput = '{value}';
  var arr = ['publicacao','video','hora','revisita','estudo','obs'];
  var selId = $('#'+id);
  for (var i = 0; i < arr.length; i++) {
    var eq = (i+1);
    var td = $('#'+id+' td:eq('+eq+')');
    var s = $('#'+id+' input['+arr[i]+']');
    if(i==0){
      selId.removeAttr('exec');
    }
    if(i==5){
       var wid='200';
       var t='text';
       td.removeClass('d-flex');
    }else{
      var wid='100';
      var b='';
      var t='number';
    }
    var v = s.val();
    if(ac=='del'){
      var c = temaImput.replace('{value}','');
    }else{
      var c = temaImput.replace('{value}',v);
    }
    //c = c.replace('{value}',v);
    //c = c.replace('{wid}',wid);
    //c = c.replace('{type}',t);
    //c = c.replace('{btn}',b);
    td.html(c);
  }
}
function alerta(msg,id,title,tam,fechar,time,fecha){

	if(typeof(fechar) == 'undefined')
        fechar = true;
    if(typeof(title) == 'undefined')
    title = 'Janela modal';
    if(typeof(fecha) != 'undefined')
        fecha = fecha;
    else
        fecha = '';
	if(typeof(id) == 'undefined')
    id = 'meuModal';
	if(typeof(tam) == 'undefined')
    tam = '';
	if(typeof(time) == 'undefined')
        time = 2000;
    if(fechar)
        fechar = '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button></div>';
    var modalHtml = '<div class="modal fade" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">'+
            '<div class="modal-dialog '+tam+'" role="document">'+
                '<div class="modal-content">'+
                    '<div class="modal-header">'+
                        '<h5 class="modal-title">'+title+'</h5>'+
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                                '<span aria-hidden="true">&times;</span>'+
                            '</button>'+
                    '</div>'+
                    '<div class="modal-body">'+msg+
                    '</div>'+fechar+
                '</div>'+
            '</div>'+
        '</div>';
        $('#'+id).remove();
	  var bodys = $(document.body).append(modalHtml);

	  $("#"+id).modal({backdrop: 'static'});
	if(fecha == true)
	setTimeout(function(){$("#"+id).modal("hide")}, time);
}
function alerta5(msg,id,title,tam,fechar,time,fecha){
    if(typeof(fechar) == 'undefined')
        fechar = true;
    if(typeof(title) == 'undefined')
    title = 'Janela modal';
    if(typeof(fecha) != 'undefined')
        fecha = fecha;
    else
        fecha = '';
	if(typeof(id) == 'undefined')
    id = 'meuModal';
	if(typeof(tam) == 'undefined')
    tam = '';
	if(typeof(time) == 'undefined')
        time = 2000;
    if(fechar)
        fechar = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
        var html = '<div class="modal fade" id="'+id+'" tabindex="-1" data-bs-backdrop="static">'+
        '<div class="modal-dialog '+tam+'">'+
            '<div class="modal-content">'+
                '<div class="modal-header">'+
                '<h5 class="modal-title">'+title+'</h5>'+
                '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'+
                '</div>'+
                '<div class="modal-body">'+
                msg+
                '</div>'+
                '<div class="modal-footer">'+
                fechar+
                '</div>'+
            '</div>'+
            '</div>'+
        '</div>';
    // var myInput = document.getElementById('myInput')
    $('#'+id).remove();
    $(html).insertAfter('header');
    // let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById(id)) // Returns a Bootstrap modal instance
    // // Show or hide:
    // modal.show();
    // modal.hide()
}
function alerta52(msg,title,funCall){
    if(typeof(fechar) == 'undefined')
        fechar = true;
    if(typeof funCall == 'undefined'){
        funCall = function(ev){
            console.log(ev);
        }
    }// var sel = document.querySelector();
    document.querySelector('#modal-ms-title').innerHTML = title;
    document.querySelector('#modal-mensagem').querySelector('.modal-body').innerHTML = msg;
    document.querySelector('[data-bs-target="#modal-mensagem"]').click();
    funCall();
}
function editarAssistencia(obj){
    var sele = obj.attr('sele');
    var arr = sele.split('_');
    var ac = arr[0];
    var id = 0;
    var s = $('[sele="'+sele+'"] .l1');
    if(arr[0]=='edit'){
      id = arr[1];
      var d = '';
      var valor = s.find('span').html();
      valor = valor.trim();
    }else{
      var d = s.find('[name="dados"]').val();
      valor = 0;
    }
    var tema = '<form id="frm_'+sele+'">'+
                    '<div class="input-group">'+
                      '<input type="number" class="form-control" style="width:56px" name="qtd" value="{value}" placeholder="0">'+
                      '<input type="hidden" class="form-control" name="ac" value="{ac}">'+
                      '<input type="hidden" class="form-control" name="id" value="{id}">'+
                      '<span class="input-group-btn">'+
                        '<button class="btn btn-primary" onclick="salvarAssitencia(\'frm_'+sele+'\',\''+d+'\');" type="button"><i class="fa fa-check"></i></button>'+
                        '<button class="btn btn-secondary" onclick="cancelEditAssistencia(\'frm_'+sele+'\',{valor})" type="button"><i class="fa fa-times"></i></button>'+
                      '</span>'+
                    '</div>'+
                  '</form>';
    var nv = tema.replace('{value}',valor);
    nv = nv.replace('{ac}',ac);
    nv = nv.replace('{id}',id);
    nv = nv.replace('{valor}',valor);
    s.find('span').html(nv);
    s.find('[name="qtd"]').select();
}
function cancelEditAssistencia(frm,qtd){
  var sele = frm.replace('frm_','');
  var s = $('[sele="'+sele+'"] .l1').find('span');
  var tema = '{qtd}';
  var nv = tema.replace('{qtd}',qtd);
  //nv = nv.replace('{ac}',ac);
  //nv = nv.replace('{id}',id);
  s.html(nv);
}
function salvarAssitencia(frm,dados){
  //var var_cartao = atob(arr['var_cartao']);
        $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
           }
       });

       //var state = jQuery('#btn-save').val();
       var f = $('#'+frm);
       var ac = f.find('[name="ac"]').val();
       var RAIZ = $('[name="raiz"]').val();
       if(ac=='cad'){
         var type = "POST";
         var ajaxurl = RAIZ+'/assistencias';
       }else{
         var id = f.find('[name="id"]').val();
         var type = "POST";
         var ajaxurl = RAIZ+"/assistencias/"+id;
       }
       $.ajax({
           type: type,
           url: ajaxurl,
           data: f.serialize()+'&dados='+dados,
           dataType: 'json',
           success: function (data) {
             if(data.exec){
               cancelEditAssistencia(frm,data.data.qtd);
							 if(data.mens){
								 lib_formatMensagem('.mens',data.mens,'success');
							 }
						 }else{
							 lib_formatMensagem('.mens',data.mens,'danger');
						 }
             if(data.data.dados[0].semanas[6].qtd){
               var totalR1 = data.data.dados[0].semanas[6].qtd;
               $('[sele="total_0_6"] span').html(totalR1);
             }
             if(data.data.dados[0].semanas[7].qtd){
               var mediaR1 = data.data.dados[0].semanas[7].qtd;
               $('[sele="media_0_7"] span').html(mediaR1);
             }
             if(data.data.dados[1].semanas[6].qtd){
               var totalR1 = data.data.dados[1].semanas[6].qtd;
               $('[sele="total_1_6"] span').html(totalR1);
             }
             if(data.data.dados[1].semanas[7].qtd){
               var mediaR1 = data.data.dados[1].semanas[7].qtd;
               $('[sele="media_1_7"] span').html(mediaR1);
             }

           },
           error: function (data) {
               console.log(data);
           }
       });
}
function mask(o, f) {
	setTimeout(function() {
		var v = clientes_mascaraTelefone(o.value);
		if (v != o.value && o.value!='') {
		  o.value = v;
		}
	  }, 1);
}
function clientes_mascaraTelefone(v) {
	var r = v.replace(/\D/g, "");
	  r = r.replace(/^0/, "");
	  if (r.length > 10) {
		r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1)$2-$3");
	  } else if (r.length > 5) {
		r = r.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1)$2-$3");
	  } else if (r.length > 2) {
		r = r.replace(/^(\d\d)(\d{0,5})/, "($1)$2");
	  } else {
		r = r.replace(/^(\d*)/, "($1");
	  }
	  return r;
}
function confirmDelete(obj){
    var id = obj.data('id');
    if(window.confirm('DESEJA EXCLUIR O CADASTRO?\n\nAo prosseguir com esta ação todas as informações serão excluídas permanentemente!!')){
        // $('#frm-'+id).submit();
        submitFormulario($('#frm-'+id),function(res){
            if(res.mens){
                lib_formatMensagem('.mens',res.mens,res.color);
            }
            if(res.return){
                location.reload();
                //window.location = res.return
            }
            if(res.exec){
                $('#tr_'.id).remove();
            }
            if(res.errors){
                alert('erros');
                console.log(res.errors);
            }
        });
    }

}
function urlAtual(){
    return window.location.href;
}
function isEmpty(str) {
    return (!str || 0 === str.length);
}
function __translate(val,val2){
      return val;
}
function lib_trataAddUrl(campo,valor,urlinic){
	var ret = '';
	if(typeof urlinic == 'undefined')
		var urla = urlAtual();
	else
		var urla = urlinic;
	var urlAtua = urla.split('?');
	if(typeof urlAtua[1]=='undefined'){
		urlAtua[1] = '';
	}
	var urlA1 = urlAtua[1];
	var opc = 1;
	urlA1 = urlA1.replace('&=','',urlAtua[1]);
	if(opc==1){
			ret += urlAtua[0]+'?';
			var arr_url = urlAtua[opc];
			arr_url = arr_url.split('&');
			var mudou = false;
			arr_url.forEach(function (element, index) {
				//console.log("[" + index + "] = " + element);
				var arr_vu = element.split('=');
				if(!isEmpty(arr_vu[0])){
					if(arr_vu[0]==campo){
						mudou = true;
						ret += arr_vu[0] +'='+valor+'&';
					}else{
						ret += arr_vu[0] +'='+arr_vu[1]+'&';
					}
				}

			});
			if(!mudou){
				ret += '&'+campo+'='+valor;
			}
			ret = ret.replace('&&','&');
			ret = ret.replace('?&','?');
			ret = ret.replace('/?','?');
	}
	console.log(ret);
	return ret;
}
function lib_trataRemoveUrl(campo,valor,urlinic){
	var ret = '';
   if(typeof urlinic == 'undefined')
		var urla = urlAtual();
	else
		var urla = urlinic;
	var urlAtua = urla.split('?');
	var urlA1 = urlAtua[1];
	var opc = 1;
	urlA1 = urlA1.replace('&=','',urlAtua[1]);
	if(opc==1){
			ret += urlAtua[0]+'?';
			var arr_url = urlAtua[opc];
			arr_url = arr_url.split('&');
			var mudou = false;
			arr_url.forEach(function (element, index) {
				//console.log("[" + index + "] = " + element);
				var arr_vu = element.split('=');
				if(!isEmpty(arr_vu[0])){
					if(arr_vu[0]==campo){
						mudou = true;
						//ret += arr_vu[0] +'='+valor+'&';
					}else{
						ret += arr_vu[0] +'='+arr_vu[1]+'&';
					}
				}

			});
			if(!mudou){
				ret += '&'+campo+'='+valor;
			}
			ret = ret.replace('&&','&');
			//alert('&&','&');
			//console.log(urlAtua[1]);
	}
	return ret;
}
function visualizaArquivos(token_produto,ajaxurl,painel){
    if(typeof painel=='undefined'){
        painel = '';
    }
    $.ajax({
        type: 'GET',
        url: ajaxurl,
        data: {
            token_produto:token_produto,//ou post_id
        },
        dataType: 'json',
        success: function (data) {

          if(data.exec && data.arquivos){
            var list = listFiles(data.arquivos,token_produto,painel);
            $('#lista-files').html(list);
            $( ".sortable" ).sortable();

            if(data.mens){
              lib_formatMensagem('.mens',data.mens,'success');
            }
          }else{
            lib_formatMensagem('.mens',data.mens,'danger');
          }
        },
        error: function (data) {
            console.log(data);
        }
    });
}
function visualizaArquivos2(token_produto,ajaxurl,painel){
    if(typeof painel=='undefined'){
        painel = 'biddings';
    }
    $.ajax({
        type: 'GET',
        url: ajaxurl,
        data: {
            token_produto:token_produto,//ou post_id
            local:'biddings',//ou post_id
        },
        dataType: 'json',
        success: function (data) {

          if(data.exec && data.arquivos){
            var list = listFiles(data.arquivos,token_produto,painel);
            $('#lista-files').html(list);
            $( ".sortable" ).sortable();
            if(data.mens){
              lib_formatMensagem('.mens',data.mens,'success');
            }
          }else{
            lib_formatMensagem('.mens',data.mens,'danger');
          }
        },
        error: function (data) {
            console.log(data);
        }
    });
}
function listFiles(arquivos,token_produto,painel){
    if(typeof token_produto == 'undefined'){
        token_produto = '';
    }
    if(typeof painel == 'undefined'){
        painel = '';
    }
    var ret = __translate('Nenhum arquivo');
    if(arquivos.length>0){
        try {

            if(painel=='i_wp'){
                var tema1 = '<div class="list-group">{li}</div>';
                var tema2 = '<div class="list-group-item d-flex justify-content-between align-items-center px-0" id="item-{id}">'+
                '<a href="{href}" class="venobox"><img src="{href}" alt="{nome}" style="width: 100%"></a>'+
                '<span style="position: absolute;top:2px;right:2px">'+
                '<button type="button" {event} class="btn btn-default" title="Excluir"><i class="fas fa-trash "></i></button>'+
                '</span>'+
                '</div>';
            }else if(painel=='biddings'){
                var tema1 = '<form id="files"><table class="table"><thead><tr><th title="'+__translate('Visualizar')+'">Ver</th><th>Nome</th><th>Ação</th></tr></thead><tbody class="sortable">{li}</tbody></table></form>';
                var tema2 = '<tr class="" id="item-{id}">'+
                '<td class="pl-0 pr-0"> <i class="fas fa-arrows-alt-v mr-2" style="cursor:pointer" title="'+__translate('Arraste e solte para mudar a ordem')+'"></i> <a href="{href}" title="'+__translate('Ver o arquivo')+'" target="_blank">{icon}</a></td>'+
                '<td style=""><input type="hidden" name="order[]" value="{id}" /><input title="'+__translate('Editar o nome do arquivo')+'" type="text" value="{nome}" name="file[{id}][title]" class="form-control" /></td>'+
                '<td style="" class="text-right"><button {event_edit} type="button" title="'+__translate('Gravar o nome do arquivo')+'" class="btn btn-outline-secondary mr-1"><i class="fa fa-check"></i></button><button  title="'+__translate('Excluir o arquivo')+'" type="button" {event} class="btn btn-outline-danger" title="Excluir"><i class="fas fa-trash "></i></button></td>'+
                '</tr>';
            }else{
                // var tema1 = '<ul class="list-group">{li}</ul>';
                // var tema2 = '<li class="list-group-item d-flex justify-content-between align-items-center" id="item-{id}">'+
                // '<a href="{href}" target="_blank">{icon} {nome}</a>'+
                // '<button type="button" {event} class="btn btn-default" title="Excluir"><i class="fas fa-trash "></i></button></li>';
                var tema1 = '<form id="files"><table class="table"><thead><tr><th title="'+__translate('Visualizar')+'">Ver</th><th>Nome</th><th>Ação</th></tr></thead><tbody class="sortable">{li}</tbody></table></form>';
                var tema2 = '<tr class="" id="item-{id}">'+
                '<td class="pl-0 pr-0"> <i class="fas fa-arrows-alt-v mr-2" style="cursor:pointer" title="'+__translate('Arraste e solte para mudar a ordem')+'"></i> <a href="{href}" title="'+__translate('Ver o arquivo')+'" target="_blank">{icon}</a></td>'+
                '<td style=""><input type="hidden" name="ordem[]" value="{id}" /><input title="'+__translate('Editar o nome do arquivo')+'" type="text" value="{nome}" name="file[{id}][title]" class="form-control" /></td>'+
                '<td style="" class="text-right"><button {event_edit} type="button" title="'+__translate('Gravar o nome do arquivo')+'" class="btn btn-outline-secondary mr-1"><i class="fa fa-check"></i></button><button  title="'+__translate('Excluir o arquivo')+'" type="button" {event} class="btn btn-outline-danger" title="Excluir"><i class="fas fa-trash "></i></button></td>'+
                '</tr>';

            }
            var li = '';
            var temaIcon = '<i class="fas fa-file{tipo} fa-2x"></i>',tenant_asset=document.getElementById('tenant_asset').value;
            for (let index = 0; index < arquivos.length; index++) {
                const arq = arquivos[index];
                if(painel=='i_wp'){
                    var href = arq.guid;
                    var id = arq.ID;
                }else if(painel=='biddings'){
                    var href = tenant_asset+'/'+arq.file_path;
                    var id = arq.id;
                }else{
                    var id = arq.id;
                    var href = tenant_asset+'/'+arq.pasta;
                    // var href = '/storage/'+arq.pasta;
                }
                console.log(arq);
                console.log(tenant_asset);

                var event = 'onclick="excluirArquivo(\''+id+'\',\''+painel+'\')"',event_edit = 'onclick="save_name_attachment(\''+id+'\');"';
                var icon = '';
                if(painel=='biddings'){
                    li += tema2.replaceAll('{event}',event);
                    li = li.replaceAll('{event_edit}',event_edit);
                    li = li.replaceAll('{nome}',arq.title);
                    li = li.replaceAll('{order}',arq.order);
                    li = li.replaceAll('{id}',id);
                    li = li.replaceAll('{href}',href);
                    var tfr = arq.extension;
                    if(tfr=='pdf' || tfr=='PDF'){
                        var tipo = '-pdf';
                    }else if(tfr=='docx' || tfr=='doc'){
                        var tipo = '-word';
                    }else if(tfr=='xls' || tfr=='xlsx'){
                        var tipo = '-excel';
                    }else if(tfr=='jpg' || tfr=='png' || tfr=='jpeg'){
                        var tipo = '-image';
                    }else{
                        var tipo = '';
                    }
                    icon = temaIcon.replace('{tipo}',tipo);
                }else{
                    event_edit = 'onclick="save_name_attachment(\''+id+'\',\'uploads\');"'
                    li += tema2.replaceAll('{event}',event);
                    li = li.replaceAll('{event_edit}',event_edit);
                    li = li.replaceAll('{nome}',arq.nome);
                    li = li.replaceAll('{order}',arq.ordem);
                    li = li.replaceAll('{id}',id);
                    li = li.replaceAll('{href}',href);
                    var tfr = arq.extension;
                    if(tfr=='pdf' || tfr=='PDF'){
                        var tipo = '-pdf';
                    }else if(tfr=='docx' || tfr=='doc'){
                        var tipo = '-word';
                    }else if(tfr=='xls' || tfr=='xlsx'){
                        var tipo = '-excel';
                    }else if(tfr=='jpg' || tfr=='png' || tfr=='jpeg'){
                        var tipo = '-image';
                    }else{
                        var tipo = '';
                    }
                    icon = temaIcon.replace('{tipo}',tipo);
                }
                if(painel=='i_wp'){
                    if(conf = arq.config){
                        var config = JSON.parse(conf);
                        if(config.extenssao == 'jpg' || config.extenssao=='png' || config.extenssao == 'jpeg'){
                            var tipo = '-image';
                        }else if(config.extenssao == 'doc' || config.extenssao == 'docx') {
                            var tipo = '-word';
                        }else if(config.extenssao == 'xls' || config.extenssao == 'xlsx') {
                            var tipo = '-excel';
                        }else{
                            var tipo = '-download';
                        }
                        icon = temaIcon.replace('{tipo}',tipo);
                    }
                }
                li = li.replace('{icon}',icon);
            }
            ret = tema1.replace('{li}',li);
        } catch (error) {
            ret = 'erro ao renderizar lista entre em contato com o suporte';
            console.log(error);
        }
    }
    return ret;
}
function list_arquivos_biddings(sel){
    console.log(sel);

    var code_arquivos = document.querySelector(sel).value,arquivos = decodeArray(code_arquivos);
    var mont_file = listFiles(arquivos,false,'biddings');
    console.log(arquivos);
    document.querySelector('#lista-files').innerHTML = mont_file;
    $( ".sortable" ).sortable();
}
function list_arquivos(sel){
    var code_arquivos = document.querySelector(sel).value,arquivos = decodeArray(code_arquivos);
    var mont_file = listFiles(arquivos,false);
    console.log(arquivos);
    document.querySelector('#lista-files').innerHTML = mont_file;
    $( ".sortable" ).sortable();
}
function save_name_attachment(id,local){
    var title = document.querySelector('[name="file['+id+'][title]"]').value;
    ajaxurl = '/admin/ajax/attachments/'+id
    $.ajaxSetup({
        headers: {
           'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            id:id,
            nome:title,
            local:local,
        },
        dataType: 'json',
        success: function (data) {
            lib_formatMensagem('.mens',data.mens,data.color);
        },
        error: function (data) {
            console.log(data);
        }
    });
}
function excluirArquivo(id,painel){
    var ajaxurl = '/admin/uploads/'+id+'/delete';
    $.ajaxSetup({
        headers: {
           'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            id:id,
            painel:painel,
        },
        dataType: 'json',
        success: function (data) {
           if(data.exec){
                if(data.dele_file){
                   lib_formatMensagem('.mens','Arquivo excluido com sucesso!','success');
                   $('#item-'+id).remove();
                   $('#enviar-arquivo').show();
                }
          }else{
            lib_formatMensagem('.mens','Erro ao excluir entre em contato com o suporte','danger');
          }


        },
        error: function (data) {
            console.log(data);
        }
    });
}
function carregaDropZone(seletor){
    $(seletor).dropzone({ url: "/file/post" });
}
function submitFormulario(objForm,funCall,funError,compleUrl){
    if(typeof funCall == 'undefined'){
        funCall = function(res){
            console.log(res);
        }
    }
    if(typeof funError == 'undefined'){
        funError = function(res){
            lib_funError(res);
        }
    }
    if(typeof compleUrl == 'undefined'){
        compleUrl='';
    }
    var route = objForm.attr('action');
    //console.log(route);
    $.ajax({
        type: 'POST',
        url: route,
        data: objForm.serialize()+'&ajax=s'+compleUrl,
        dataType: 'json',
        beforeSend: function(){
            $('#preload').fadeIn();
        },
        success: function (data) {
            $('#preload').fadeOut("fast");
            funCall(data);
        },
        error: function (data) {
            $('#preload').fadeOut("fast");
            if(data.responseJSON.errors){
                funError(data.responseJSON.errors);
                console.log(data.responseJSON.errors);
            }else{
                lib_formatMensagem('.mens','Erro','danger');
            }
        }
    });
}
function submitFormFile(objForm,funCall,funError,compleUrl){
    if(typeof funCall == 'undefined'){
        funCall = function(res){
            console.log(res);
        }
    }
    if(typeof funError == 'undefined'){
        funError = function(res){
            lib_funError(res);
        }
    }
    if(typeof compleUrl == 'undefined'){
        compleUrl='';
    }
    var formData = new FormData();
    var files = $('input[type=file]');
    for (var i = 0; i < files.length; i++) {
        if (files[i].value != "" || files[i].value != null) {
            formData.append(files[i].name, files[i].files[0]);
        }
    }
    var formSerializeArray = objForm.serializeArray();
    for (var i = 0; i < formSerializeArray.length; i++) {
        formData.append(formSerializeArray[i].name, formSerializeArray[i].value)
    }
    formData.append('ajax','s');
    var route = objForm.attr('action');
    //console.log(route);
    $.ajax({
        type: 'POST',
        url: route,
        //data: formData+'&ajax=s'+compleUrl,
        data: formData,
        dataType: 'json',
        beforeSend: function(){
            $('#preload').fadeIn();
        },
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            $('#preload').fadeOut("fast");
            funCall(data);
        },
        error: function (data) {
            $('#preload').fadeOut("fast");
            if(data.responseJSON.errors){
                funError(data.responseJSON.errors);
                console.log(data.responseJSON.errors);
            }else{
                lib_formatMensagem('.mens','Erro','danger');
            }
        }
    });
}
function getAjax(config,funCall,funError){

    if(typeof config.url == 'undefined'){
        alert('informe a Url');
        return false;
    }
    if(typeof config.type == 'undefined'){
        config.type = 'GET';
    }
    if(typeof config.dataType == 'undefined'){
        config.dataType = 'json';
    }
    if(typeof config.data == 'undefined'){
        config.data = {ajax:'s'};
    }
    if(typeof funCall == 'undefined'){
        funCall = function(res){
            console.log(res);
        }
    }
    if(typeof funError == 'undefined'){
        funError = function(res){
            $('#preload').fadeOut("fast");
            lib_funError(res);
        }
    }
    if(typeof config.csrf == 'undefined'){
        config.csrf = '';
    }
    if(config.csrf){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
    }
    $.ajax({
        type: config.type,
        url: config.url,
        data: config.data,
        dataType: config.dataType,
        beforeSend: function(){
            $('#preload').fadeIn();
        },
        success: function (data) {
            funCall(data);
        },
        error: function (data) {
            $('#preload').fadeOut("fast");
            if(data.errors){
                funError(data.errors);
                console.log(data.errors);
            }else{
                lib_formatMensagem('.mens','Erro','danger');
            }
        }
    });
}
function submitFormularioCSRF(objForm,funCall,funError){
    if(typeof funCall == 'undefined'){
        funCall = function(res){
            console.log(res);
        }
    }
    if(typeof funError == 'undefined'){
        funError = function(res){
            lib_funError(res);
        }
    }
    var route = objForm.attr('action');
    console.log(route);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'POST',
        url: route,
        data: objForm.serialize()+'&ajax=s',
        dataType: 'json',
        beforeSend: function(){
            $('#preload').fadeIn();
        },
        success: function (data) {
            $('#preload').fadeOut("fast");
            funCall(data);
        },
        error: function (data) {
            $('#preload').fadeOut("fast");
            if(data.responseJSON.errors){
                funError(data.responseJSON.errors);
                console.log(data.responseJSON.errors);
            }else{
                lib_formatMensagem('.mens','Erro','danger');
            }
        }
    });
}
function lib_funError(res){
    var mens = '';
    Object.entries(res).forEach(([key, value]) => {
        //console.log(key + ' ' + value);
        var s = $('[name="'+key+'"]');
        var v = s.val();
        mens += value+'<br>';
        if(key=='cpf'){
           s.addClass('is-invalid');
        }else{
            if(v=='')
                s.addClass('is-invalid');
            else{
                s.removeClass('is-invalid');
            }
        }
        console.log(s);
    });
    lib_formatMensagem('.mens',mens,'danger',0);

}
function modalGeral(id,titulo,conteudo){
    var m = $(id);
    m.modal({backdrop:'static'});
    m.find('.modal-title').html(titulo);
    m.find('.conteudo').html(conteudo);

}
function renderForm(config,alvo,funCall){
    if(typeof config=='undefined'){
        return ;
    }
    var d = config;

    console.log(d);
    if(d.campos){
        var f = qFormCampos(d.campos);
        if(f){
            var tf = '<form id="{id_form}" action="{action}"><div class="row"><div class="col-md-12 mens"></div>{conte}</div></form>';
            var b = '<button type="button" class="btn btn-primary" f-submit>Salvar <i class="fas fa-chevron-circle-right"></i></button>';
            var m = '#modal-geral';
            tf = tf.replace('{id_form}',d.id_form);
            tf = tf.replace('{conte}',f);
            tf = tf.replace('{action}',d.action);
            modalGeral(m,'Cadastrar '+d.label,tf);
            $('[f-submit]').remove();
            $(b).insertAfter(m+' .modal-footer button');
            try {
                $('[mask-cpf]').inputmask('999.999.999-99');
                $('[mask-cnpj]').inputmask('99.999.999/9999-99');
                $('[mask-data]').inputmask('99/99/9999');
                $('[mask-cep]').inputmask('99.999-999');
                if(n=d.value_transport){
                    var vl = $('[name="'+n+'"]').val();
                    if(vl)
                    $('[name="'+n+'"]').find('option[value='+vl+']').attr('selected','selected');
                }
            } catch (error) {
                console.log(error);
            }
            carregaMascaraMoeda(".moeda");
            $('#'+d.id_form+' #inp-nome').focus();
            $('[f-submit]').on('click',function(){
                if(typeof funCall=='undefined'){
                    funCall = function(res){
                        if(res.mens){
                            lib_formatMensagem('.mens',res.mens,res.color);
                        }
                        if(res.exec){
                            $(m).modal('hide');
                            alvo.append($('<option>', {
                                value: res.idCad,
                                text: res.dados[d.campo_bus]
                            }));
                            alvo.find('option[value='+res.idCad+']').attr('selected','selected').addClass('opcs');
                        }
                    }
                }
                submitFormularioCSRF($('#'+d.id_form),funCall);
            });
        }
        //$('.mens').html(campo_nome);
    }
}
function initSelector(alvo,funCall){
    if(alvo.val()=='cad'){
        var d = decodeArray(alvo.data('selector'));
        //console.log(alvo);
        renderForm(d,alvo);
        alvo.find('option[value=\'\']').attr('selected','selected');
    }
    if(alvo.val()=='ger'){
        var d = decodeArray(alvo.data('selector'));
        window.open(d.route_index,'_blank');
        console.log(d);
    }

}
function lib_vinculoCad(obj){
    if(typeof obj == 'undefined'){
        return;
    }
    var d = decodeArray(obj.data('selector')),ac = obj.data('ac');
    if(ac == 'cad' && d.salvar_primeiro){
        var msg = '<div class="row"><div id="exibe_etapas" class="col-md-12 text-center"><h6>Antes de cadastrar um parceiro é necessário salvar este cadastro!</h6></div><div class="col-md-12 mt-3 text-center"></div></div>';
        var btns = '<button type="button" class="btn btn-primary" salvar-agora>Salvar agora</button>';
        alerta(msg,'modal-cad-vinculo','Atenção','',true,9000,true);
        $(btns).insertAfter('#modal-cad-vinculo .modal-footer button');
        $('[salvar-agora]').on('click',function(){
            $('[btn="permanecer"]').click();
            $('#modal-cad-vinculo').modal('hide');
        });
        return;
    }
    if(typeof d.janela=='undefined'){
        d.janela = '';
    }
    if(d.janela.url){
        var url = d.janela.url+'?popup=true';
        try {
            if(pr=d.janela.param){
                for (let i = 0; i < pr.length; i++) {
                    const el = pr[i];
                    url += '&'+el+'='+$('[name="'+el+'"]').val();
                }
            }
        } catch (e) {
            console.log(e);
        }
        var tag_obj = '<obj class="d-none">'+obj.data('selector')+'</obj>';
        $('obj').remove();
        $(tag_obj).insertBefore('body');
        abrirjanelaPadraoConsulta(url,'vinculo');
    }else{
        renderForm(d,obj,function(res){
            if(res.mens){
                lib_formatMensagem('.mens',res.mens,res.color);
            }
            if(res.exec){
                var mod = '#modal-geral';
                $(mod).modal('hide');
                lib_listDadosHtmlVinculo(res,obj.data('selector'),'cad');
            }
        });
    }
}
function qFormCampos(config){
    if(typeof config == 'undefined'){
        return false;
    }
    const tl = '<label for="{campo}">{label}</label>';
    var tema = {
        text : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        color : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        email : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        tel : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        date : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        number : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        moeda : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        hidden : '<div class="form-group col-{col}-{tam} {class_div} d-none" div-id="{campo}" >{label}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        hidden_text : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" ><b>{label}</b>: {value_text}<input type="{type}" class="form-control {class}" id="inp-{campo}" name="{campo}" aria-describedby="{campo}" placeholder="{placeholder}" value="{value}" {event} /></div>',
        textarea : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<textarea name="{campo}" class="form-control {class}" rows="{rows}" cols="{cols}">{value}</textarea></div>',
        chave_checkbox : '<div class="form-group col-{col}-{tam}"><div class="custom-control custom-switch  {class}"><input type="checkbox" class="custom-control-input" {checked} value="{value}"  name="{campo}" id="{campo}"><label class="custom-control-label" for="{campo}">{label}</label></div></div>',
        select : {
            tm1 : '<div class="form-group col-{col}-{tam} {class_div}" div-id="{campo}" >{label}<select name="{campo}" {event} class="form-control custom-select {class}">{op}</select></div>',
            tm2 : '<option value="{k}" class="opcs" {selected}>{v}</option>'
        }
    };
    var r = '';
    var ret = '';
    if(Object.entries(config).length>0){
        Object.entries(config).forEach(([key, v]) => {
            if(v.js || v.active){
                if(v.type == 'selector' || v.type == 'select'){
                    let op='',arr = v.arr_opc,tm1 = tema['select'].tm1,tm2 = tema['select'].tm2;
                    var value = v.value?v.value:'';
                    Object.entries(arr).forEach(([i, el]) => {
                        op += tm2.replace('{k}',i);
                        var selected = '';
                        if(value==i){
                            var selected = 'selected';
                        }
                        op = op.replaceAll('{selected}',selected);
                        op = op.replace('{v}',el);
                    });
                    var type = v.type;
                    r += tm1.replaceAll('{type}',v.type);
                    var label = tl.replaceAll('{campo}',key);
                    label.replaceAll('{label}',);
                    var classe = v.class?v.class:'';
                    var placeholder = v.placeholder?v.placeholder:'';
                    r = r.replaceAll('{campo}',key);
                    r = r.replaceAll('{label}',v.label);
                    r = r.replaceAll('{value}',value);
                    r = r.replaceAll('{tam}',v.tam);
                    r = r.replaceAll('{event}',v.event);
                    r = r.replaceAll('{col}','md');
                    r = r.replaceAll('{class}',classe);
                    r = r.replaceAll('{op}',op);
                    r = r.replaceAll('{placeholder}',placeholder);
                }else{
                    try {

                        var checked = '',type = v.type,value_text=v.value_text?v.value_text:v.value,label = tl.replaceAll('{campo}',key);
                        label.replaceAll('{label}',);

                        if(type == 'moeda'){
                            v.type = 'tel';
                            v.class += ' moeda';
                        }else if(type == 'hidden_text'){
                            v.type = 'hidden';
                            v.event = '';
                        }else if(type == 'chave_checkbox'){
                            if(v.valor_padrao==v.value){
                                checked = 'checked';
                            }
                        }
                        r += tema[type].replaceAll('{type}',v.type);
                        var value = v.value?v.value:'';
                        var classe = v.class?v.class:'';
                        var class_div = v.class_div?v.class_div:'';
                        var placeholder = v.placeholder?v.placeholder:'';
                        r = r.replaceAll('{campo}',key);
                        r = r.replaceAll('{label}',v.label);
                        r = r.replaceAll('{value}',value);
                        r = r.replaceAll('{value_text}',value_text);
                        r = r.replaceAll('{tam}',v.tam);
                        r = r.replaceAll('{event}',v.event);
                        r = r.replaceAll('{class_div}',class_div);
                        r = r.replaceAll('{col}','md');
                        r = r.replaceAll('{class}',classe);
                        r = r.replaceAll('{checked}',checked);
                        r = r.replaceAll('{placeholder}',placeholder);
                    } catch (e) {
                        console.log(e);
                    }
                }
            }
        });
    }
    ret = r;

    return ret;
}
function color_select1_0(val,val1){
    if(val==true){
		$('#tr_'+val1).addClass('table-info');
	}
	if(val==false){
		$('#tr_'+val1).removeClass('table-info');
	}
}
function gerSelect(obj){
	if(obj.is(':checked')){
        $('.table .checkbox').each(function(){
            this.checked = true;
            color_select1_0(this.checked,this.value);
        });
	}else{
        $('.table .checkbox').each(function(){
            this.checked = false;
            color_select1_0(this.checked,this.value);
        });
	}
}
function coleta_checked(obj){
    let id = '';
    obj.each(function (i) {
        id += this.value+'_';
    });
    return id;
}
function dps_salvarEpatas(res,etapa,m){
    $.each(res,function(v,k) {
        var sl = '#tr_'+v+' .etapa';
        $(sl).html(etapa);
    });
    //if(typeof m!='undefined')
    $(m).modal('hide');
}
function janelaEtapaMass(selecionandos){
    if(typeof selecionandos =='undefined'){
        return ;
    }
    if(selecionandos==''){
        var msg = '<div class="row"><div id="exibe_etapas" class="col-md-12 text-center"><p>Por favor selecione um registro!</p></div></div>';
        alerta(msg,'modal-etapa','Alerta','',true,3000,true)
        return;
    }else{
       var msg = '<form id="frm-etapas" action="/familias/ajax"><div class="row"><div id="exibe_etapas" class="col-md-12">seleEta</div></div></form>',btnsub = '<button type="button" id="submit-frm-etapas" class="btn btn-primary">Salvar</button>',m='modal-etapa';

       alerta(msg,m,'Editar Etapas');
       $.ajax({
            type:"GET",
            url:"/familias/campos",
            dataType:'json',
            success: function(res){
                res.etapa.type = 'select';
                res.etapa.tam = '12';
                res.etapa.option_select = true;
                var conp = {etapa:res.etapa};
                var et = qFormCampos(conp);
                et += '<input type="hidden" name="opc" value="salvar_etapa_massa"/>';
                et += '<input type="hidden" name="ids" value="'+selecionandos+'"/>';
                $('#exibe_etapas').html(et);
                $(btnsub).insertAfter('#'+m+' .modal-footer button');
                $('[mask-cpf]').inputmask('999.999.999-99');
                $('[mask-data]').inputmask('99/99/9999');
                $('[mask-cep]').inputmask('99.999-999');
                carregaMascaraMoeda(".moeda");
                $('#submit-frm-etapas').on('click',function(e){
                    e.preventDefault();
                    submitFormularioCSRF($('#frm-etapas'),function(res){
                        if(res.mens){
                            lib_formatMensagem('.mens',res.mens,res.color);
                        }
                        if(res.exec && (a = res.atualiza)){
                            dps_salvarEpatas(a,res.etapa,'#'+m);
                        }
                    });
                });
            }
       });
    }
}
function carregaMascaraMoeda(s){
    $(s).maskMoney({
        prefix: 'R$ ',
        allowNegative: true,
        thousands: '.',
        decimal: ','
    });
}
function lib_carregaConjuge(frmParce,frmBene){
    var formParce = $(frmParce);
    var formBenef = $(frmBene);
    var idBenef = formBenef.find('[name="id"]').val();
    formParce.find('[name="conjuge"]').val(idBenef);
}
function cursos_carregaUrl(){}
function lib_htmlVinculo(ac,campos,lin){
    var c = decodeArray(campos),idf='#'+c.id_form,arr=c.campos;
    try {
        if(typeof lin == 'undefined'){
            lin = '';
        }
        var tipo=c.tipo;
    } catch (e) {
        var tipo='int';
        console.log(e);
    }
    if(typeof tipo =='undefined'){
        var tipo='int';
    }
    if(ac=='del'){
        if(tipo=='array' && lin){
            var id = c.list[lin].id,trsel = '#tr-'+lin+'-'+id;
        }else{
            var id = c.list.id,trsel = '#tr-'+id;
        }
        if(id){
            var msg = '<div class="row"><div id="mens-id" class="col-md-12 text-center"><h5>Deseja Remover da lista?</h5><p>Para completar é necessário salvar</p><p>Remover da lista não exclui o cadastro</p></div><div class="col-md-12 mt-3 text-center"></div></div>';
            var btnr = '<button type="button" class="btn btn-danger" deletar>Remover Agora!</button>';
            alerta(msg,'modal-del-html_vinculo','Atenção','',true);
            $(btnr).insertAfter('#modal-del-html_vinculo .modal-footer button');
            $('[deletar]').on('click',function(){
                if(tipo=='array' && lin){
                    $('#table-html_vinculo-'+c.campo+' '+trsel).remove();
                }else{
                    $('#table-html_vinculo-'+c.campo+' '+trsel+' td').html('');
                }
                $('#modal-del-html_vinculo').modal('hide');
                $('[name="'+c.campo+'"]').val('');
            });
        }
    }
    if(ac=='alt'){


        if(Object.entries(arr).length>0){
            Object.entries(arr).forEach(([k, v]) => {
                if(tipo=='array'){
                    var l = '';
                    try {
                        var list = $('#tr-'+lin+'-')
                        if(l = c.list[lin][k]){
                            c.campos[k].value = l;
                        }else{
                            if(cp=c.campos[k].cp_busca){
                                let ar = cp.split('][');
                                if(ar[1]){
                                    try {
                                        c.campos[k].value = c.list[lin][ar[0]][ar[1]];
                                    } catch (error) {
                                        console.log(error);
                                    }
                                }
                            }
                        }
                        console.log(c);
                    } catch (error) {
                        console.log(error);
                    }
                }else{
                    if(c.list[k]){
                        c.campos[k].value = c.list[k];
                    }else{
                        if(cp=c.campos[k].cp_busca){
                            let ar = cp.split('][');
                            if(ar[1]){
                                try {
                                    c.campos[k].value = c.list[ar[0]][ar[1]];
                                } catch (error) {
                                    console.log(error);
                                }
                            }
                        }
                    }
                }
            });
            renderForm(c,campos,function(res){
                if(res.mens){
                    lib_formatMensagem('.mens',res.mens,res.color);
                }
                if(res.exec){
                    var mod = '#modal-geral';
                    $(mod).modal('hide');
                    lib_listDadosHtmlVinculo(res,campos,ac,lin);
                }
            });
            if(tipo=='array' && lin){
                try {
                    var tid='';
                    if(c.list[lin].id){
                        tid=c.list[lin].id;
                    }else if(c.list.id){
                        tid=c.list.id;
                    }
                    frm = $(idf)
                    var m = '<input type="hidden" name="_method" value="PUT">';
                    frm.attr('action',c.action+'/'+c.tid);
                    frm.find('[name="_method"]').remove();
                    frm.append(m);

                } catch (error) {
                    console.log(error);
                }
            }else{
                if(c.list.id){
                    frm = $(idf)
                    var m = '<input type="hidden" name="_method" value="PUT">';
                    frm.attr('action',c.action+'/'+c.list.id);
                    frm.find('[name="_method"]').remove();
                    frm.append(m);
                }
            }
        }
    }
}
function lib_htmlVinculo2(ac,campos,id,lin){
    var c = decodeArray(campos),idf='#'+c.id_form,arr=c.campos;
    try {
        if(typeof lin == 'undefined'){
            lin='';
        }
        if(typeof id == 'undefined'){
            return 'dados lista indefinida';
        }
        var seleinp = '#inp-list-'+lin+'-'+id;
        var inpli = $(seleinp).val();
        var dl = decodeArray(inpli);
        var tipo=c.tipo;
    } catch (e) {
        var tipo='int';
        console.log(e);
    }
    if(typeof tipo =='undefined'){
        var tipo='int';
    }
    if(ac=='del'){
        if(tipo=='array' && lin){
            var id = id,trsel = '#tr-'+lin+'-'+id;
        }else{
            var id = c.list.id,trsel = '#tr-'+id;
        }
        if(id){
            var msg = '<div class="row"><div id="mens-id" class="col-md-12 text-center"><h5>Deseja Remover da lista?</h5><p>Para completar é necessário salvar</p><p>Remover da lista não exclui o cadastro</p></div><div class="col-md-12 mt-3 text-center"></div></div>';
            var btnr = '<button type="button" class="btn btn-danger" deletar>Remover Agora!</button>';
            alerta(msg,'modal-del-html_vinculo','Atenção','',true);
            $(btnr).insertAfter('#modal-del-html_vinculo .modal-footer button');
            $('[deletar]').on('click',function(){
                if(tipo=='array' && lin){
                    $('#table-html_vinculo-'+c.campo+' '+trsel).remove();
                }else{
                    $('#table-html_vinculo-'+c.campo+' '+trsel+' td').html('');
                }
                $('#modal-del-html_vinculo').modal('hide');
                $('[name="'+c.campo+'"]').val('');
            });
        }
    }
    if(ac=='alt'){
        if(Object.entries(arr).length>0){
            Object.entries(arr).forEach(([k, v]) => {
                if(tipo=='array'){
                    var l = '';
                    try {
                        if(l = dl[k]){
                            c.campos[k].value = l;
                        }else{
                            if(cp=c.campos[k].cp_busca){
                                let ar = cp.split('][');
                                if(ar[1]){
                                    try {
                                        c.campos[k].value = dl[ar[0]][ar[1]];
                                    } catch (error) {
                                        console.log(error);
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.log(error);
                    }
                }else{
                    if(c.list[k]){
                        c.campos[k].value = c.list[k];
                    }else{
                        if(cp=c.campos[k].cp_busca){
                            let ar = cp.split('][');
                            if(ar[1]){
                                try {
                                    c.campos[k].value = c.list[ar[0]][ar[1]];
                                } catch (error) {
                                    console.log(error);
                                }
                            }
                        }
                    }
                }
            });

            renderForm(c,campos,function(res){
                if(res.mens){
                    lib_formatMensagem('.mens',res.mens,res.color);
                }
                if(res.exec){
                    var mod = '#modal-geral';
                    $(mod).modal('hide');
                    lib_listDadosHtmlVinculo(res,campos,ac,lin);
                }
            });
            if(tipo=='array' && dl){
                try {
                    /*
                    var tid='';
                    if(dl[id]){
                        tid=dl[id];
                    }
                    console.log(dl);
                    */
                    frm = $(idf)
                    var m = '<input type="hidden" name="_method" value="PUT">';
                    frm.attr('action',c.action+'/'+dl['id']);
                    frm.find('[name="_method"]').remove();
                    frm.append(m);

                } catch (error) {
                    console.log(error);
                }
            }else{
                if(c.list.id){
                    frm = $(idf)
                    var m = '<input type="hidden" name="_method" value="PUT">';
                    frm.attr('action',c.action+'/'+c.list.id);
                    frm.find('[name="_method"]').remove();
                    frm.append(m);
                }
            }
        }
    }
}
function calculaLinCad(seleTr){
    //calcula numero da ultima linha
    var ret='';
    try {
        var elem = $(seleTr).last().attr('id');
        if(typeof elem=='undefined'){
            return '0';
        }
        var tr=elem.split('-');
        if(tr[1]){
            ret = new Number(tr[1])+1;
        }
    } catch (e) {
        console.log(e);
        return '0';
    }
    return ret;

}
function lib_listDadosHtmlVinculo(res,campos,ac,lin){
    //lin é o numero da linha para o caso do tipo array
    // alert(campos);
    if(typeof ac=='undefined'){
        ac = 'alt';
    }
    if(typeof lin=='undefined'){
        lin = '';
    }
    var dt = decodeArray(campos);
    try {
        var tipo=dt.tipo;
    } catch (e) {
        var tipo='int';
        console.log(e);
    }
    if(typeof tipo =='undefined'){
        var tipo='int';
    }

    if((d=res.dados) && ac =='cad'){
        var table = $('#table-html_vinculo-'+dt.campo);
        lin = calculaLinCad('#table-html_vinculo-'+dt.campo+' tbody tr');
        var tm = $('tm').html();
        var tm0 = '<tr id="tr-{id}">{td}</tr>';
        var tm = '<td id="td-{k}" class="{class}">{v}</td>';
        var data_list = encodeArray(d);
        console.log(d);

        if(t = dt.table){
            console.log(t);
            var td = '';
            $.each(t,function(k,v){
                if(v.type=='text'){
                    td += tm.replaceAll('{k}',k);
                    td = td.replaceAll('{v}',d[k]);
                    td = td.replaceAll('{class}','');
                }else if(v.type=='arr_tab'){
                    var kv = k+'_valor';
                    td += tm.replaceAll('{k}',kv);
                    td = td.replaceAll('{v}',d[kv]);
                    td = td.replaceAll('{class}','');
                }
            });
            if (tipo=='array'&&lin){
                if(lin=='0')
                    lin=0;
                /*try {

                    if(typeof dt.list[lin]=='undefined'){
                        dt.list = [d];
                    }else{
                        dt.list[lin] = d;
                    }
                    console.log(dt);
                } catch (e) {
                    dt.list = [d];
                    console.log(e);
                }*/
            }else{
                dt.list = d;
            }
            var e = encodeArray(dt);
            var btnsAc = '<button type="button" btn-alt="" onclick="lib_htmlVinculo2(\'alt\',\''+e+'\',\''+d['id']+'\',\''+lin+'\')" title="Editar" class="btn btn-outline-secondary"><i class="fas fa-pencil-alt"></i> </button> '+
            '<button type="button" onclick="lib_htmlVinculo2(\'del\',\''+e+'\',\''+d['id']+'\',\''+lin+'\')" class="btn btn-outline-danger" title="Remover"> <i class="fa fa-trash" aria-hidden="true"></i> </button>';
            var tdacao = tm.replaceAll('{k}','tr-acao');
            tdacao = tdacao.replaceAll('{v}',btnsAc);
            tdacao = tdacao.replaceAll('{class}','text-right');
            if(tipo=='array'){
                var tr = tm0.replaceAll('{id}',lin+'-'+d.id);
                var inp = '<input type="hidden" name="'+dt.campo+'[]" value="'+d.id+'" />'+
                '<input type="hidden" value="'+data_list+'" id="inp-list-'+lin+'-'+d.id+'">';
                var tr = tr.replaceAll('{td}',td+inp+tdacao);
                //var tr = tr.replaceAll('{data_list}',data_list);
                table.find('tbody').append(tr);
            }else{
                var tr = tm0.replaceAll('{id}',d.id);
                var tr = tr.replaceAll('{td}',td+tdacao);
                var inp = '<input type="hidden" name="'+dt.campo+'" value="'+d.id+'" />';
                var tr = tr.replaceAll('{data_list}',data_list);
                table.find('tbody tr').remove();
                table.find('tbody').html(tr);
                if(dt.campo){
                    $('[name="'+dt.campo+'"]').remove();
                    var selInp = table.find('tbody');
                    $(inp).insertBefore(selInp);
                }
            }
        }
    }
    if((d=res.dados) && ac =='alt'){
        var table = $('#table-html_vinculo-'+dt.campo);
        if(tipo=='array' && lin){
            var seltr = '#tr-'+lin+'-'+d.id;
            //dt.list = d;
            //console.log(dt.list[lin]);
        }else{
            var seltr = '#tr-'+d.id;
            dt.list = d;
        }
        $.each(d,function(k,v){
            table.find(seltr+' #td-'+k).html(v);
        });
        var e = encodeArray(dt);

        table.find(seltr+' [btn-alt]').attr('data_selector',e);
        table.find(seltr+' #inp-list-'+lin+'-'+d.id).val(encodeArray(d));

        //table.find(seltr+' [btn-alt]').attr('onclick','lib_htmlVinculo(\'alt\',"'+e+'","'+lin+'")');
        table.find(seltr+' [btn-alt]').attr('onclick','lib_htmlVinculo2(\'alt\',"'+e+'","'+d.id+'","'+lin+'")');
        //console.log(dt);
        //alert(ac);
    }
}
function lib_listarCadastro(res,obj){
    if(typeof obj == 'undefined')
    {
        return;
    }
    try {
        if(res.id=='cad'){
            var dt = decodeArray(obj.data('selector'));
            $('[div-id="'+dt.campo+'"] [data-ac="cad"]').click();
            //console.log(dt);
            return;
        }
        if(res.dados){
            lib_listDadosHtmlVinculo(res,obj.data('selector'),'cad');
            obj.val('');
        }
    } catch (error) {
        console.log(error);
    }
}
function lib_abrirModalConsultaVinculo(campo,ac){
    var btnAbrir = $('#row-'+campo+' .btn-consulta-vinculo'),btnFechar = $('#row-'+campo+' .btn-voltar-vinculo'),ef='slow';
    if(ac=='abrir'){
        btnAbrir.hide(ef);
        btnFechar.show(ef);
        $('#inp-cad-'+campo).show(ef);
        $('#inp-cad-'+campo+' input').val('');
        $('#inp-cad-'+campo+' input').focus();
        lib_autocomplete($('#inp-auto-'+campo));
    }
    if(ac=='fechar'){
        btnAbrir.show(ef);
        btnFechar.hide(ef);
        $('#inp-cad-'+campo).hide(ef);
        $('#inp-cad-'+campo+' input').val('');
    }
}
function lib_autocomplete(obs){
    var urlAuto = obs.attr('_url');
    var data_selector = obs.data('selector'),d=decodeArray(data_selector);
    try {
        if(typeof d.janela != 'undefined'){
            if(pr=d.janela.param){
                //console.log(d.janela.param);
                for (let i = 0; i < pr.length; i++) {
                    const el = pr[i];
                    if(i==0){
                        urlAuto += '?'+el+'='+$('[name="'+el+'"]').val();
                    }else{
                        urlAuto += '&'+el+'='+$('[name="'+el+'"]').val();
                    }
                }
            }
        }
    }
    catch (e) {
        console.log(e);
    }
    //console.log(urlAuto);
     obs.autocomplete({
        source: urlAuto,
        search  : function(){$(this).addClass('ui-autocomplete-loading');},
        open    : function(){$(this).removeClass('ui-autocomplete-loading');},
        select: function (event, ui) {
            lib_listarCadastro(ui.item,$(this));
        },
    });
}
function carregaDados(obj,alvo){
    if(typeof local=='undefined'){
        local='';
    }
    if(typeof alvo=='undefined'){
        alvo = function (val) {
            console.log(val);
        };
    }
    var dados = obj.options[obj.selectedIndex].getAttribute('dados');
    if(dados){
        arrd = decodeArray(dados);
    }
    alvo(arrd);
}
function carregaBairro(val){
    if(val==''|| val=='cad'|| val=='ger' || !val)
        return ;
    getAjax({
        url:'/quadras/'+val+'/edit?ajax=s',
    },function(res){
        $('#preload').fadeOut("fast");
        try {
            if(b=res.value.bairro){

                $('[name="bairro"]').val(b);
                $('#txt-bairro').html(res.value.bairro_nome);
            }else{
                $('[name="bairro"]').val('');
                $('#txt-bairro').html('');
            }
        } catch (error) {
            console.log(error);
        }

    });
}
function carregaQuadras(val,selQuadra){
    if(typeof selQuadra=='undefined'){
        selQuadra='quadra';
    }
    if(val==''){
        $('[div-id="'+selQuadra+'"] option.opcs').each(function(){
            $(this).remove();
        });
        return
    }
    getAjax({
        url:'/quadras?ajax=s&filter[bairro]='+val+'&campo_order=id&order=ASC',
    },function(res){
        $('#preload').fadeOut("fast");
        var option_select = '<option value="{value}" class="opcs">{label}</option>';
        var opc = '';
        $('[div-id="'+selQuadra+'"] option.opcs').each(function(v,k){
            $(this).remove();
        });
        if(d=res.dados.data){
            $.each(d,function(k,v){
                //console.log(v);
                opc += option_select.replaceAll('{label}',v.nome);
                opc = opc.replaceAll('{value}',v.id);
            });
            $(opc).insertAfter('[div-id="'+selQuadra+'"] option.option_select');
        }
    });
    $.ajax({
        url:'/quadras?ajax=s&filter[bairro]=1',
        type:'GET',
        success:'GET',
    });
}
function buscaCep1_0(cep_code){
    if( cep_code.length <= 0 ) return;
    cep_code = cep_code.replaceAll('.','').replaceAll('-','');
    $.get("https://viacep.com.br/ws/"+cep_code+"/json/", { code: cep_code },
       function(result){
           console.log(result);
          if( result.cep =='' ){
             alerta(result.message || "Cep não encontrado!");
             return;
          }
          if( result.erro){
              $('#Cep,#Cep,[q-inp="Cep"],[name="edit_cliente[Cep]"]').select();
             lib_formatMensagem('.mens,.mensa',"O cep <b>"+cep_code+"</b> não foi encontrado! <button type=\"button\" onclick='abrirjanelaPadraoConsulta(\"https://buscacepinter.correios.com.br/app/endereco/index.php?t\");' class='btn btn-primary'>"+__translate('Não sei o cep')+"</button>",'danger',9000);
              $('input#Cep,[name="cep"],[q-inp="cep"],[name="edit_cliente[Cep]"]').val('');
             return;
          }
          //$("input#Cep,[name=\"cep\"],[q-inp=\"cep\"]").val( result.cep );
          $('input#Estado,[name="uf"],[q-inp="uf"],[uf="cep"],[name="edit_cliente[Uf]"]').val( result.uf );
          $('input#Cidade,[name="cidade"],[q-inp="cidade"],[cidade="cep"],[name="edit_cliente[Cidade]"]').val( result.localidade );
          $('input#Bairro,[name="bairro"],[q-inp="bairro"],[bairro="cep"],[name="edit_cliente[Bairro]"]').val( result.bairro );
          $('input#Endereco,[name="endereco"],[q-inp="endereco"],[endereco="cep"],[name="edit_cliente[Endereco]"]').val( result.logradouro );
          $('#UF,[name="Uf"],[name="config[uf]"]').val( result.uf );
          $("#Uf").val( result.uf );
          $('#codigoCidade,[name="config_notas[endereco][codigoCidade]"],[codigoCidade="cep"],[name="config[codigoCidade]"],[name="edit_cliente[codigoCidade]"]').val(result.ibge);
          $('#numero,#Numero,[q-inp="numero"],[name="numero"],[numero="cep"]').select();
       });
}
function popupCallback_vinculo(res){
    var obj = $('obj').html();
    var d = decodeArray(obj);
    //console.log(d);
    if(res.mens){
        lib_formatMensagem('.mens',res.mens,res.color);
    }
    if(res.exec){
        lib_listDadosHtmlVinculo(res,obj,'cad');
    }
}
function popupCallback_redirect(url){
    window.location=url;
}
function btVoltar(obj){
    var href = obj.attr('href'),redirect = obj.attr('redirect');
    if(redirect){
        if(pop){
            if(redirect){
                popupCallback_redirect(redirect);
                window.close();
            }
        }else{
            window.location = redirect;
        }
    }else{
        if(pop){
            window.close();

        }else{
            window.location = href;
        }
    }
}
function lib_abrirListaOcupantes(){
    var sel = coleta_checked($('.table .checkbox:checked'));
    if(sel==''){
        var msg = '<div class="row"><div id="exibe_etapas" class="col-md-12 text-center"><p>Por favor selecione um registro!</p></div></div>';
        alerta(msg,'modal-etapa','Alerta','',true,3000,true)
        return;
    }else{
        var url = '/lotes/lista-ocupantes/'+sel;
        abrirjanelaPadraoConsulta(url,'lista-ocupantes');
    }
}
function zoom(c) {
    var s = new Number(50);
    let a = 0;
    let box = document.querySelector('#svg-img');
    let width = box.style.width;
    let top = box.style.top;
    let left = box.style.left;
    let height = box.offsetHeight;
    var w = width.replace('%','');
    var l = left.replace('%','');
    var t = top.replace('%','');
    w = new Number(w);
    if(w==0){
        w=100;
    }
    if(c=='p'){
        a=w+s;
        to=t-s;
        le=l-s;
        box.style.width = (a)+'%';
        box.style.top = (to)+'%';
        box.style.left = (le)+'%';
    }
    if(c=='r'){
        //retorna ao inicio
        box.style.width = '100%';
        box.style.left = '0%';
        box.style.top = '0%';
    }
    if(c=='m'){
        a=w-s;
        to=new Number(t)+new Number(s);
        le=new Number(l)+new Number(s);

        box.style.width = (a)+'%';
        box.style.top = (to)+'%';
        box.style.left = (le)+'%';
    }
}
function lib_conteudoMapa(id,tipo,local){
    if(typeof tipo=='undefined'){
        tipo = 'lotes';
    }
    if(typeof local=='undefined'){
        local = 'quadras';
    }
    if(id &&tipo=='lotes' && local=='quadras'){
        let arr_id = id.split('-');
        getAjax({
            url:'/'+tipo+'?ajax=s',
            data:{
                bairro:arr_id[0],
                quadra:arr_id[1],
                term:new Number(arr_id[2]),
                familias:'s',
            }
        },function(res){
            $('#preload').fadeOut("fast");
            lib_infoMaps({
                res:res,
                bairro:arr_id[0],
                quadra:arr_id[1],
                lote:arr_id[2],
                local:local,
                tipo:tipo,
            });
        });
    }
}
function lib_infoMaps(config){
    if(typeof config.res=='undefined' || typeof config.local=='undefined' || typeof config.tipo=='undefined'){
        return;
    }
    if(typeof config.lote=='undefined'){
        config.lote = 0;
    }
    if(typeof config.quadra=='undefined'){
        config.quadra = 0;
    }
    try {
        let mensPainel = '';
        let tm1 = '<div class="card card-secondary shadow card-outline"><div class="card-header"><h3 class="card-title">{title}</h3>{btn_fechar}</div><div class="card-body {px}"><div class="list-group">{cont}</div></div>';
        let tm2 = '<a class="list-group-item  list-group-item-action py-1 px-2" href="{href}">{label} <i class="fa fa-link ml-3"></i></a>';
        let btn_fechar = '<div class="card-tools"><button onclick="lib_fechaCardOc();" type="button" class="btn btn-tool" data-card-widget="close" title="Collapse"><i class="fas fa-times"></i></button></div>';
        let redirect = '?redirect=/mapas/'+config.local+'/'+config.quadra;
        let redirect2 = '&redirect=/mapas/'+config.local+'/'+config.quadra;
        if(dl=config.res[0].dados){
            let link_lote = '/lotes/'+dl.id+'/edit'+redirect;
            if(fam=dl.familias){
                let cont = '';
                if(fam[0]){

                    for (let i = 0; i < fam.length; i++) {
                        const el = fam[i];
                        let href='/familias/'+el.id+'/show'+redirect;
                        cont += tm2.replace('{href}',href);
                        cont = cont.replace('{label}',el.nome);
                    }
                    if(fam.length>1){
                        var title = 'Ocupantes';
                    }else{
                        var title = 'Ocupante';
                    }
                    mensPainel = tm1.replace('{cont}',cont);
                    mensPainel = mensPainel.replace('{px}','p-0');
                    mensPainel = mensPainel.replace('{title}',title+' <a href="'+link_lote+redirect+'" style="text-decoration:underline">lote '+dl.nome+'</a>');
                }else{
                    cont = 'Ocupante não cadastrado';
                    cont += '<a href="/lotes/create?bairro='+config.bairro+'&quadra='+config.quadra+'&nome='+config.lote+redirect2+'" class="btn btn-primary btn-block mt-3">Cadastrar</a>';

                    mensPainel = tm1.replace('{cont}',cont);
                    mensPainel = mensPainel.replace('{title}','Aviso <a href="'+link_lote+redirect+'">lote '+dl.nome+'</a>');
                }
            }else{
                cont = 'Família não localizada';
                mensPainel = tm1.replace('{cont}',cont);
                mensPainel = mensPainel.replace('{title}','Aviso <a href="'+link_lote+redirect+'">lote '+dl.nome+'</a>');
            }
        }else{
            cont = config.res[0].value;
            let btnCadA = '<a href="/familias/create?bairro='+config.bairro+'&quadra='+config.quadra+'&loteamento='+config.lote+redirect2+'" class="btn btn-primary btn-block mt-3">Cadastrar</a>';
            mensPainel = tm1.replace('{cont}',cont);
            mensPainel = mensPainel.replace('Cadastrar agora?',btnCadA);
            mensPainel = mensPainel.replace('Lote','Cadastro do lote '+config.lote);
            mensPainel = mensPainel.replace('{title}','Atenção');
        }
        if(mensPainel){
            mensPainel = mensPainel.replace('{btn_fechar}',btn_fechar);
            $('.mini-card').addClass('active').html(mensPainel);
        }else{
            $('.mini-card').removeClass('active').html('');
        }
    } catch (error) {
        console.log(error);
    }
}
function lib_fechaCardOc(){
    $('.mini-card').removeClass('active').html('');
}
function lib_typeSlug(obj){
    let v = obj.value;
    let s = lib_urlAmigavel(v);
    $('[type_slug="true"]').val(s);
}
function lib_carregaImageLfm(obj){
    let urlImg = obj.value;
    if(urlImg){
        $('#holder').attr('src',urlImg);
        $('#lfm').hide();
        $('#lfm-remove').removeClass('d-none').addClass('d-block');
    }
    //console.log(obj);
}
function selectTipoUser(tipo){
    var url = window.location.href;
    if(tipo=='pf'){
        $('.div-pf').addClass('d-block').removeClass('d-none');
        $('.div-pj').addClass('d-none').removeClass('d-block');
        var lab_nome = 'Nome completo *';
        var lab_cpf = 'CPF *';
        url = url.replace('/pj','/'+tipo);
        $('[name="cpf"]').inputmask('999.999.999-99');
    }
    if(tipo=='pj'){
        url = url.replace('/pf','/'+tipo);
        $('.div-pf').addClass('d-none').removeClass('d-block');
        $('.div-pj').addClass('d-block').removeClass('d-none');
        var lab_nome = 'Nome do responsável *';
        var lab_cpf = 'CPF do responsável*';
        //$('[name="cpf"]').inputmask('999.999.999/9999-99');
    }
    window.history.pushState("object", "Title", url);
    $('[for="nome"]').html(lab_nome);
    $('[for="cpf"]').html(lab_cpf);
}
function checkTodosAnos() {
    let urlAtual = lib_trataRemoveUrl('ano','');
    window.location=urlAtual;
    $('#preload').show();
}
function lib_autocompleteGeral(cl,funCall){
    var urlAuto = $(cl).attr('url_');
    if(typeof funCall=='undefined'){
        $( cl ).autocomplete({
            source: urlAuto,
            select: function (event, ui) {
                lib_listarCadastro(ui.item,$(this));
            },
        });
    }else{
        $( cl ).autocomplete({
            source: urlAuto,
            select: function (event, ui) {
                funCall(ui.item,$(this));
            },
        });
    }
}
function update_status_post(obj){
    let id = obj.getAttribute('data-id');
    let status = obj.checked;
    let tab = obj.getAttribute('data-tab');
    let campo = obj.getAttribute('data-campo');
    if(typeof campo =='undefined'){
        campo = 'post_status';
    }
    getAjax({
        url:'/admin/ajax/chage_status',
        type: 'POST',
        dataType: 'json',
        csrf: true,
        data:{
            id: id,
            status: status,
            tab: tab,
            campo: campo,
        }
    },function(res){
        $('#preload').fadeOut("fast");
        lib_formatMensagem('.mens',res.mens,res.color);

    },function(err){
        $('#preload').fadeOut("fast");
        console.log(err);
    });
}
function resend_email_sic() {
    getAjax({
        url:'/internautas/send-verific-user',
        type: 'GET',
        dataType: 'json',
        csrf: true
    },function(res){
        $('#preload').fadeOut("fast");

        lib_formatMensagem('.mens',res.mens,res.color,9000);

    },function(err){
        $('#preload').fadeOut("fast");
        console.log(err);
    });
}
function exibeTpc(val){
    if(val=='p'){
        $('.tpc-p').show();
        $('.tpc-a').hide();
    }
    if(val=='a'){
        $('.tpc-p').hide();
        $('.tpc-a').show();
    }
}
function cancelarSulamerica(token,id,obj){
    var no = obj.getAttribute('data-operacao');
    // var token_contrato = obj.getAttribute('data-token_contrato');
    if(!window.confirm('DESEJA PROSSEGUIR COM O CANCELAMENTO?')){
        return;
    }
    try {
        getAjax({
            url:'/api/v1/cancelar',
            type: 'POST',
            dataType: 'json',
            csrf: true,
            data:{
                id: id,
                numeroOperacao: no,
                token_contrato: token
            }
        },function(res){
            $('#preload').fadeOut("fast");
            lib_formatMensagem('.mens',res.mens,res.color);
            dps_cancela(res);
        },function(err){
            $('#preload').fadeOut("fast");
            console.log(err);
        });
    } catch (error) {
        console.log(error);
    }

}
//executado depos do cancelamento
function dps_cancela(res){
    if(res.exec){
        document.querySelector('[btn-volter="true"]').click();
        $('[btn="permanecer"]').hide();
        $('[btn="sair"]').hide();
    }
}
function reativar_cadastro(token,link_a){
    if (typeof link_a=='undefined') {
        alert('Id não informado')
        return
    }
    if(!window.confirm('DESEJA INICIAR O PRECESSO DE REATIVAÇÃO?')){
        return;
    }
    try {
        getAjax({
            url:'/admin/ajax/cliente/reativar/'+token,
            type: 'POST',
            dataType: 'json',
            csrf: true,
            data:{
                token: token
            }
        },function(res){
            $('#preload').fadeOut("fast");
            lib_formatMensagem('.mens',res.mens,res.color);
            dps_reativa(res,link_a);
        },function(err){
            $('#preload').fadeOut("fast");
            console.log(err);
        });
    } catch (error) {
        console.log(error);
    }
}
function dps_reativa(res,link){
    if(res.exec){
        if(link){
            window.location = link+'&bc=false';
            // alert(link);
        }
    }
}
function clica_consulta_cliente(){
    document.querySelector('[data-widget="navbar-search"]').click();
}
function excluir_cliente(id,link_r){
    if (typeof id=='undefined') {
        alert('Id não informado')
        return
    }
    if(!window.confirm('DESEJA EXCLUIR O CADASTRO?\n\nAo prosseguir com esta ação todas as informações serão excluídas permanentemente!!')){
        return;
    }
    try {
        getAjax({
            url:'/admin/ajax/cliente/delete/'+id,
            type: 'POST',
            dataType: 'json',
            csrf: true,
            // data:{
            //     token: token
            // }
        },function(res){
            $('#preload').fadeOut("fast");
            lib_formatMensagem('.mens',res.mens,res.color);
            if(res.exec){
                window.location = link_r;
            }
            // dps_reativa(res,link_a);
        },function(err){
            $('#preload').fadeOut("fast");
            console.log(err);
        });
    } catch (error) {
        console.log(error);
    }
}
function calculaFim(inicio){
    var arr_data = inicio.split('-');
    if(a=arr_data[0]){
        var ano = new Number(a)+1;
        if(m=arr_data[1]){
            if(d=arr_data[2]){
                var as = ano.toString();
                var n_data = ano+'-'+m+'-'+d;
                document.querySelector('[name="config[fimVigencia]"]').value = n_data;
            }
        }
    }
}
