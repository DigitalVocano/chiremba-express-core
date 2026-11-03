// webpack.mix.js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       // Tailwind CSS and Autoprefixer are handled by postcss.config.js
   ]);

// If you want to version your assets in production
if (mix.inProduction()) {
    mix.version();
}
