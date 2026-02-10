const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: {
    app: path.resolve(__dirname, 'frontend/js/app.js'),
    auth: path.resolve(__dirname, 'frontend/js/auth.js'),
    calendar: path.resolve(__dirname, 'frontend/js/chunks/calendar.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'public/assets/build'),
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.s?css$/i,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      },
      {
        test: /\.(woff2?|eot|ttf|otf|svg|png|jpe?g|gif|webp)$/i,
        type: 'asset/resource',
        generator: {
          filename: 'assets/[name][ext][query]',
        },
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
  ],
  devtool: 'source-map',
};
