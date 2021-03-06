const path = require('path')
const webpack = require('webpack')
const mix = require('laravel-mix')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')
const { SyntaxDynamicImport } = require('babel-plugin-syntax-dynamic-import')

mix
  .js('resources/assets/js/app.js', 'public/js')
  .sass('resources/assets/sass/app.scss', 'public/css')

  .sourceMaps()
  .disableNotifications()

if (mix.inProduction()) {
  mix.version()

  mix.extract([
    'vue',
    'vform',
    'axios',
    'vuex',
    'vue-meta',
    'js-cookie',
    'popper.js',
    'vue-router',
    'vuex-router-sync',
    'bootstrap-vue',
    'vue-multiselect',
    'vue-color',
    'vue-star-rating',
    'vue-progressive-image'
  ])
}

mix.webpackConfig({
  plugins: [
    new BundleAnalyzerPlugin()
    /*
    DAU
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.jQuery': 'jquery',
      Popper: ['popper.js', 'default']
    })
    */
  ],
  resolve: {
    alias: {
      '~': path.join(__dirname, './resources/assets/js')
    }
  }
})
