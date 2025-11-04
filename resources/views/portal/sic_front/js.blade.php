<script src="{{url('/')}}/vendor/jquery/jquery.min.js"></script>
<script src="{{url('/')}}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{{url('/')}}/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="{{url('/')}}/DataTables/datatables.min.js" ></script>
<script src="{{url('/')}}/DataTables/DataTables-1.11.5/js/dataTables.bootstrap4.min.js" ></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js" ></script>
<script src="{{url('/')}}/js/jquery.maskMoney.min.js"></script>
<script src="{{url('/')}}/js/jquery-ui.min.js"></script>
<script src="{{url('/')}}/js/jquery.inputmask.bundle.min.js"></script>
<script src="{{url('/')}}/summernote/summernote.min.js"></script>
<script src=" {{url('/')}}/js/lib.js"></script>
<script type="text/javascript">
    $(function(){
        $('a.print-card').on('click',function(e){
            openPageLink(e,$(this).attr('href'),"{{date('Y')}}");
        });
        $('[mask-cpf]').inputmask('999.999.999-99');
        $('[mask-cnpj]').inputmask('99.999.999/9999-99');
        $('[mask-data]').inputmask('99/99/9999');
        $('[mask-cep]').inputmask('99.999-999');
    });
</script>
