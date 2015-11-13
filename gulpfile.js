var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix
    .copy('node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
         'resources/assets/js/bootstrap.js')
    .copy('vendor/components/jquery/jquery.js',
         'resources/assets/js/jquery.js')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyphicons-halflings-regular.eot',
          'public/fonts/bootstrap/')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyphicons-halflings-regular.svg',
        'public/fonts/bootstrap/')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyphicons-halflings-regular.ttf',
        'public/fonts/bootstrap/')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyphicons-halflings-regular.woff',
        'public/fonts/bootstrap/')
    .copy('node_modules/bootstrap-sass/assets/fonts/bootstrap/glyphicons-halflings-regular.woff2',
        'public/fonts/bootstrap/')
    .copy('vendor/infinety/sweetalert/src/libraries/sweetalert.css',
        'resources/assets/css')
    .copy('vendor/infinety/sweetalert/src/libraries/sweetalert.js',
        'resources/assets/js')
    .scripts([
        'jquery.js',
        'bootstrap.js',
        'sweetalert.js',
        'cglib.js'
        ])
    .sass('app.scss','resources/assets/css')
    .styles([
        'app.css',
        'sweetalert.css'
    ], 'public/css/winglib.css');
});
