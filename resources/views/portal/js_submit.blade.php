<script>
    $(function(){
        $("#{{$config['frm_id']}}").validate({
            submitHandler: function(form) {
                // some other code
                // maybe disabling submit button
                // then:
                //sub(form);
                //alert('exetua');
                submitFormulario($("#{{$config['frm_id']}}"),function(res){

                    let btn_press = $('#btn-press-salv').html();
                    if(res.exec){
                        //lib_formatMensagem('.mens',res.mens,res.color);
                        // alerta(res.mens+'<div class="col-md-12 mt-3 text-center"><i class="fa fa-check text-'+res.color+' fa-2x" aria-hidden="true"></i></div>','modal-mens','');
                        // var redirect = $('[btn-volter="true"]').attr('redirect');
                        // if(typeof redirect=='undefined'){
                        //     redirect = '/';
                        // }
                        // $('[data-dismiss="modal"]').on('click',function(){
                        //     window.location = redirect;
                        // });
                        alerta52(res.mens +'<div class="col-md-12 mt-3 text-center"><i class="fa fa-check text-'+res.color+' fa-2x" aria-hidden="true"></i></div>',title='',function(e){
                            var redirect = res.redirect?res.redirect : $('[btn-volter="true"]').attr('redirect');
                            if(typeof redirect=='undefined'){
                                redirect = '/';
                            }
                            var btn = '<button type="button" onclick="redirect(\''+redirect+'\')" data-bs-dismiss="modal" class="btn btn-primary">Fechar</button>';
                            $('#modal-mensagem .modal-footer').html(btn);
                        });
                    }else{
                        lib_formatMensagem('.mens',res.mens,res.color);
                        return;
                    }

                    // if(btn_press=='sair'){
                    //     if(pop){
                    //             window.opener.popupCallback_vinculo(res); //Call callback function
                    //             window.close(); // Close the current popup
                    //             return;
                    //     }
                    //     var redirect = $('[btn-volter="true"]').attr('redirect');

                    //     if(redirect){
                    //         if(pop){
                    //             window.opener.popupCallback(function(){
                    //                 alert('pop some data '+redirect);
                    //             }); //Call callback function
                    //             window.close(); // Close the current popup
                    //             return;
                    //         }else{
                    //             window.location = redirect;
                    //         }
                    //     }else if(res.return){
                    //         if(pop){
                    //             window.opener.popupCallback(function(){
                    //                 alert('pop some data '+res.return);
                    //             }); //Call callback function
                    //             window.close(); // Close the current popup
                    //             return;
                    //         }else{
                    //             window.location = res.return;
                    //         }
                    //     }
                    // }else if(btn_press=='permanecer'){
                    //     if(res.redirect){
                    //         window.location = res.redirect;
                    //     }
                    // }
                    if(res.errors){
                        alert('erros');
                        console.log(res.errors);
                    }
                });
                /*
                $(form).submit(function(e){
                    e.preventDefault();

                });
                */

            }
        });
        function btnPres(obj){
            $('#btn-press-salv').remove();
            var btn = '<span id="btn-press-salv" class="d-none">'+obj.attr('btn')+'</span>';
            $(btn).insertAfter(obj);
        }
        $('[type="submit"]').on('click',function(e){
            btnPres($(this));
        });
    });
</script>
