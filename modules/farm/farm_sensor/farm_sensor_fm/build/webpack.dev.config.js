const baseConfig = require('./webpack.config')
const webpack = require('webpack')
const merge = require('webpack-merge')
const HtmlWebpackPlugin = require('html-webpack-plugin');
const FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin')
const portfinder = require('portfinder')

const HOST = process.env.HOST
const PORT = process.env.PORT && Number(process.env.PORT)

const devConfig = merge(baseConfig, {
  mode: 'development',
  entry: {
    app: './build/index.js'
  },
  output: {
    filename: '[name].js',
    path: __dirname
  },
  devtool: 'cheap-module-eval-source-map',
  devServer: {
    clientLogLevel: 'warning',
    contentBase: false, 
    host: HOST || 'localhost',
    hot: true,
    port: PORT || 8080,
    proxy: [{
      logLevel: 'debug',
      context: [
        '/'
      ],
      target: 'http://localhost:80',
      changeOrigin: true,
      secure: false
    }],
    quiet: true,
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"development"',
        PLATFORM: '"dev"'
      }
    }),
    new webpack.HotModuleReplacementPlugin(),
    new webpack.NamedModulesPlugin(), // HMR shows correct file names in console on update.
    new webpack.NoEmitOnErrorsPlugin(),
    new HtmlWebpackPlugin({
      filename: 'index.html',
      template: 'build/index.dev.html',
      inject: true,
      favicon: 'node_modules/farmos-client/favicon.ico'
    }),
  ]
})

module.exports = new Promise((resolve, reject) => {
  portfinder.basePort = process.env.PORT || devConfig.devServer.port
  portfinder.getPort((err, port) => {
    if (err) {
      reject(err)
    } else {
      // publish the new Port, necessary for e2e tests
      process.env.PORT = port
      // add port to devServer config
      devConfig.devServer.port = port

      // Add FriendlyErrorsPlugin
      devConfig.plugins.push(new FriendlyErrorsPlugin({
        compilationSuccessInfo: {
          messages: [`Your application is running here: http://${devConfig.devServer.host}:${port}`],
        },
      }))

      resolve(devConfig)
    }
  })
})