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
                try {
                    if(res.exec){
                        lib_formatMensagem('.mens',res.mens,res.color);
                    }
                    if(btn_press=='sair'){
                        if(pop){
                                window.opener.popupCallback_vinculo(res); //Call callback function
                                window.close(); // Close the current popup
                                return;
                        }
                        var redirect = $('[btn-volter="true"]').attr('redirect');
                        if(redirect){
                            if(pop){
                                window.opener.popupCallback(function(){
                                    alert('pop some data '+redirect);
                                }); //Call callback function
                                window.close(); // Close the current popup
                                return;
                            }else{
                                try {
                                    if(res.redirect){
                                        window.location = res.redirect;
                                        return;
                                    }
                                } catch (error) {
                                    window.location = redirect;
                                    console.log(error);
                                }
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
                        alert('erros');
                        console.log(res.errors);
                    }

                } catch (e) {
                    console.log(e);
                }
            },function(res){
                if(res.errors){
                    alert('erros');
                    console.log(res.errors);
                }
            },compleurl);
        });
    });
</script>
