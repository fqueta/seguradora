@php
    $orcamento_html = isset($dados['orcamento_html']) ? $dados['orcamento_html'] : '';
@endphp
<div class="row">
    <div class="col-md-12">
        <button type="button" class="btn btn-secondary btn-block" onclick="consultaRabAdmin()" consulta-rab>Consultar R.A.B</button>
    </div>
    <div class="col-md-12 retorno-pesquisa">
        {!!$orcamento_html!!}
    </div>
</div>
<script>

</script>
