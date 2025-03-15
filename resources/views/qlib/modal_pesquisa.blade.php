<div class="modal fade" id="pesquisar" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content text-left">
                <div class="modal-header">
                        <h5 class="modal-title">{{__('Encontrar beneficiário')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
            <div class="modal-body">
                <form action="" method="get">
                    <div class="row">
                        <div class="form-group col-md-4">
                        <label for="">{{__('Consultar por')}}</label>
                        <select class="form-control" onchange="alterar_urlAutocomplete(this,'#auto-form1','.autocomplete-pesq')" name="tipo_campo" id="tipo_campo">
                            <option value="nome">Nome</option>
                            <option value="cpf">CPF</option>
                            {{-- <option value="config[rg]">RG</option> --}}
                        </select>
                        </div>
                        <div class="input-group col-md-8" style="height: 38px;margin-top: 31px;">
                            <div class="form-outline" style="width: 90%">
                                {{-- <label class="form-label" for="form1">Search</label> --}}
                            <input type="search" id="auto-form1" name="" placeholder="informe o nome ou documento" class="form-control autocomplete-pesq" url="{{ route('users.index') }}?a-campo=nome" />
                            </div>
                            <button type="button" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('Fechar')}}</button>
                {{-- <button type="button" class="btn btn-primary">Save</button> --}}
            </div>
        </div>
    </div>
</div>

<script>
    function alterar_urlAutocomplete(obj,id,classe){
        if(obj.value){
            var url = '{{ route('users.index')}}'+'?a-campo='+obj.value;
            var inp = document.querySelector(id);
            inp.setAttribute('url',url);
            if(obj.value=='cpf'){
                $(classe).inputmask('999.999.999-99');
            }else{
                $(classe).inputmask('remove');
            }
            $(classe).focus();
        }
        lib_autocompleteGeral(classe,function(ui,el){
            console.log(ui);
            try {
                if(ui.id){
                    let ur = '/beneficiarios/'+ui.id+'?redirect='+window.location.href;
                    window.location = ur;
                }
            } catch (error) {
                console.log(error);
            }
        });
    }

    $('[data-dismiss="modal"]').on('click', function(){
        $('.navbar-search-block.navbar-search-open').hide();
    });

    window.onload = function(){
        $('.autocomplete-pesq').on('click', function(){
            $(this).val('');
        });
    };

</script>
