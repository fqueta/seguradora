<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Logo</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="{{route('sic.internautas.relatorios')}}">E-Sic<span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('cad.internautas',['tipo'=>'pf'])}}">Cadastrar P.F</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('cad.internautas',['tipo'=>'pj'])}}">Cadastrar P.J</a>
        </li>
        <!--
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
            Dropdown
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Something else here</a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled">Disabled</a>
        </li>-->
      </ul>
      @can('is_user_front')
      <a href="/internautas/logout" class="btn btn-outline-danger my-2 my-sm-0">Sair</a>
      @elsecan('is_user_back')
      <a href="/internautas/logout" class="btn btn-outline-secondary my-2 my-sm-0">Logado como admin, Sair?</a>
      <a href="/admin/home" class="btn btn-outline-primary my-2 my-sm-0">Painel</a>
      @else
      <a href="/login" class="btn btn-outline-success my-2 my-sm-0">Logar</a>
      @endcan
    </div>
  </nav>
