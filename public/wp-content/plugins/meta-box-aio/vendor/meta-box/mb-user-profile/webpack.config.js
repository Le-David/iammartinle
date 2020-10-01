const path = require('path');

module.exports = {
  devtool: '',
  entry: './assets/js/index.js',
  output: {
    filename: 'user-profile.js',
    path: path.resolve(__dirname, 'assets'),
  }
};