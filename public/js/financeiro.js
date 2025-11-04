function financeiro_create_receita_mensal(obj){
    var cmc = obj.getAttribute('campos-mensais'),cm=decodeArray(cmc);
    const d = {
        campos:cm,
        action:obj.getAttribute('action'),
        id_form:obj.getAttribute('id_form'),
        label:obj.getAttribute('label'),
    }

    renderForm(d,obj,function(res){
        if(res.mens){
            lib_formatMensagem('.mens',res.mens,res.color);
        }
        if(res.exec){
            var mod = '#modal-geral';
            $(mod).modal('hide');
            // lib_listDadosHtmlVinculo(res,obj.data('selector'),'cad');
            console.log(res);

        }
    });


}
