const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        index: './blocks/src/index.js',
        style: './blocks/src/style.scss',
        editor: './blocks/src/editor.scss'
    }
};
