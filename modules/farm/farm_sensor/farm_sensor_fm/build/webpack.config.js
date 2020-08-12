const path = require('path')
const VueLoaderPlugin = require('vue-loader/lib/plugin')

module.exports = {
  entry: './src/FieldModule/Sensors/js/index.js',
  output: {
    filename: 'index.js',
    path: path.resolve(__dirname, '..', 'dist/FieldModule/Sensors'),
  },
  resolve: {
    extensions: ['.js', '.vue', '.json'],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      '@': path.join(__dirname, '..', 'src'),
    },
    symlinks: false
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader'
      },
      // this will apply to both plain `.js` files
      // AND `<script>` blocks in `.vue` files
      {
        test: /\.js$/,
        loader: 'babel-loader',
        include: [
          path.join(__dirname, '..', 'src'),
          path.join(__dirname, '..', 'node_modules/webpack-dev-server/client'),
          path.join(__dirname, '..', 'node_modules/farmos-client/src'),
        ],
        options: {
          plugins: ['@babel/plugin-proposal-optional-chaining']
        }
      },
      // this will apply to both plain `.css` files
      // AND `<style>` blocks in `.vue` files
      {
        test: /\.css$/,
        use: [
          'vue-style-loader',
          'css-loader'
        ]
      },
      // Uncomment this to run eslint prior to builds.
      // {
      //   enforce: 'pre',
      //   test: /\.(js|vue)$/,
      //   loader: 'eslint-loader',
      //   exclude: /node_modules/
      // }
    ]
  },
  plugins: [
    new VueLoaderPlugin()
  ],
  optimization: {
    splitChunks: {
      //chunks: 'all',
    },
  },
}