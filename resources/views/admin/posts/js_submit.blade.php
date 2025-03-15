<script>
    $(function(){

        $('[type="submit"]').on('click',function(e){
            e.preventDefault();
            let btn_press = $(this).attr('btn');
            @if (App\Qlib\Qlib::qoption('editor_padrao')=='laraberg')
                let content = Laraberg.getContent();
                let compleurl = '&post_content='+content+'&'+$('#imagem-detacada').serialize();
            @else
                let compleurl = '';
            @endif

            submitFormulario($('#{{$config['frm_id']}}'),function(res){
                lib_formatMensagem('.mens',res.mens,res.color);
                // if(res.exec){
                // }
                if(btn_press=='sair'){
                    if(pop){
                            window.opener.popupCallback_vinculo(res); //Call callback function
                            window.close(); // Close the current popup
                            return;
                    }
                    if(res.redirect){
                        var redirect = res.redirect;
                    }else{
                        var redirect = $('[btn-volter="true"]').attr('redirect');
                    }
                    if(redirect){
                        if(pop){
                            window.opener.popupCallback(function(){
                                alert('pop some data '+redirect);
                            }); //Call callback function
                            window.close(); // Close the current popup
                            return;
                        }else{
                            window.location = redirect;
                        }
                    }else if(res.return){
                        if(pop){
                            window.opener.popupCallback(function(){
                                alert('pop some data '+res.return);
                            }); //Call callback function
                            window.close(); // Close the current popup
                            return;
                        }else{
                            window.location = res.return;
                        }
                    }
                }else if(btn_press=='permanecer'){
                    if(res.redirect){
                        window.location = res.redirect;
                    }
                }
                if(res.errors){
                    lib_funError(res);
                    console.log(res.errors);
                }
            },function(res){
                if(res){
                    lib_funError(res);
                }
            },compleurl);
        });
    });
</script>
