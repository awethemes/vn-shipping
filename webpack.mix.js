const mix = require('laravel-mix');

const DependencyExtractionWebpackPlugin = require(
  '@wordpress/dependency-extraction-webpack-plugin'
);

/**
 * The externals library.
 *
 * @type {Object}
 */
const externals = {
  'jquery': 'jQuery',
  'lodash': 'lodash'
};

mix.sass('resources/scss/admin.scss', 'dist/');

mix.js('resources/js/checkout.js', 'dist/');
mix.js('resources/js/edit-order.js', 'dist/');
mix.js('resources/js/order-shipping.js', 'dist/').react();

mix.sourceMaps(false, 'source-map');
if (mix.inProduction()) {
  mix.version();
}

mix.setPublicPath('./');
mix.disableSuccessNotifications();

mix.browserSync({
  proxy: process.env.MIX_BROWSER_SYNC_URL || 'http://wp.local',
  files: ['dist/*.js', 'dist/*.css']
});

mix.webpackConfig({
  externals,
  output: {
    libraryTarget: 'window'
  },
  plugins: [
    new DependencyExtractionWebpackPlugin()
  ]
});

mix.options({
  processCssUrls: false,
  fileLoaderDirs: {
    images: 'dist/img'
  }
});