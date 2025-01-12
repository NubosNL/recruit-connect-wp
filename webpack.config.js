const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        index: './blocks/src/index.js',
        style: './blocks/src/style.scss',
        editor: './blocks/src/editor.scss'
    },
    output: {
        path: path.resolve(__dirname, 'blocks/build'),
        filename: '[name].js'
    }
};
