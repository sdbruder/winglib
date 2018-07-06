# Wing Library

Base code for http://winglib.com.

WingLib is a online open library of wing designs. Enjoy!

- Its a library of multi-paneled wings with a center of gravity calculator;
- Built with Laravel 5.1.* with a MySQL backend;
- 2 Models only, users and wings with an N-to-N relation between them;
- Local and OAuth logins with Twitter, Facebook and Google;
- Wing image is made via JS with canvas and uploaded to the server;
- CG calc is also done via JS;
- infinety/sweetalert composer package was abandoned, we adopted the package.

Nothing fancy server side, its a really simple laravel app. The interesting part is JS, in fact based into a simpler online calc tool for CG from http://wingcgcalc.bruder.com.br/.

**PS:** As this was build with a old laravel version there will be some problems regarding the crypt library with newer php versions.

## Pre requisites

- `valet` or `homestead`;
- correctly defined .env file in the root directory;
- `php artisan migrate:install`;
- `php artisan migrate:refresh`;
- `npm install` is used for gulp and to get css and js libraries;
- `gulp` (globally and locally) used to generate public/* content (css, js, etcetera);
