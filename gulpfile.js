var elixir = require('laravel-elixir');

elixir(function(mix) {
    mix
    .copy('node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
         'resources/assets/js/bootstrap.js')
    .copy('vendor/components/jquery/jquery.js',
         'resources/assets/js/jquery.js')
    .scripts([
        'jquery.js',
        'bootstrap.js',
        'cglib.js'
        ])
    .sass('app.scss');
});
