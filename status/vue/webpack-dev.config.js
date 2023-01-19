const path = require('path');
const {getBaseWPConfig, getBaseTSConfig, scanVueAndUpdateConf} = require('../../javascript/webpack/webpack.utils');

module.exports = new Promise(
  (resolve) => {
    let confWP = getBaseWPConfig(
      path.resolve(__dirname, 'components/app.js'),
      path.resolve(__dirname, 'dist'),
      './status/vue/dist'
    );
    confWP.mode  = 'development';
    confWP.watch = true;
    let confTS = getBaseTSConfig('./dist', './components/**');
    scanVueAndUpdateConf(confWP, confTS, __dirname, path.resolve(__dirname, 'components'), (conf) => {
      resolve(conf)
    });
  }
);
