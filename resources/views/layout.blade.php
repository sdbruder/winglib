<html lang="en"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Sergio Bruder">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico">
    <title>Wing Library</title>

    <link href="/css/winglib.css" rel="stylesheet">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @yield('head')
    @yield('css')
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">WingLib</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="/">Home</a></li>
            <li><a href="/wings/create">Create a wing</a></li>
            <li><a href="/wings">Library</a></li>
            <li><p class="navbar-text">
              <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
              <!-- winglib-468x60 -->
              <ins class="adsbygoogle"
                   style="display:inline-block;width:468px;height:60px"
                   data-ad-client="ca-pub-8746171184192742"
                   data-ad-slot="8864272650"></ins>
              <script>
              (adsbygoogle = window.adsbygoogle || []).push({});
              </script>
            </p></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            @if (Auth::user())
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  {{ Auth::user()->name }} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <li><a href="/user/settings">
                    <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                    Settings
                  </a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="/auth/logout">
                    <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                    Logout
                  </a></li>
                </ul>
              </li>
              <p class="navbar-text avatar">
              <img src="{{ Auth::user()->avatar }}" alt="" class="img-circle" height="48" width="48">
              </p>
            @else
              <li><a data-toggle="modal" data-target="#loginModal">Log In</a></li>
              <form action="/auth/register" class="navbar-form navbar-right" role="search">
                <button type="submit" class="btn btn-danger">Create an account</button>
              </form>
            @endif
            <!-- <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                Dropdown <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="#">Separated link</a></li>
              </ul>
            </li> -->
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    @yield('before-content')

    <div class="container">
      @yield('content')
    </div><!-- /.container -->

    <!-- Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Login</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="/auth/twitter" class="btn btn-lg btn-block btn-social btn-twitter">
                                <span class="fa fa-twitter"></span>
                                Login with Twitter
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/auth/facebook" class="btn btn-lg btn-block btn-social btn-facebook">
                                <span class="fa fa-facebook"></span>
                                Login with Facebook
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="/auth/google" class="btn btn-lg btn-block btn-social btn-google">
                                <span class="fa fa-google"></span>
                                Login with Google
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="strike"><span>or</span></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <a href="/auth/login" class="btn btn-lg btn-block btn-social btn-github">
                                <span class="glyphicon glyphicon-user"></span>
                                Login with email
                            </a>
                        </div>
                        <div class="col-md-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Javascript stuff -->
    <script src="/js/all.js"></script>
    @yield('javascript')

    @include('include.sweetalert')
</body></html>
