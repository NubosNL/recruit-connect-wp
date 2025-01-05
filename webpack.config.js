const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: {
        blocks: './src/blocks.js',
        frontend: './src/frontend.js'
    },
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: '[name].js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react']
                    }
                }
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader'
                ]
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css'
        })
    ],
    externals: {
        'react': 'React',
        'react-dom': 'ReactDOM',
        '@wordpress/blocks': 'wp.blocks',
        '@wordpress/components': 'wp.components',
        '@wordpress/element': 'wp.element',
        '@wordpress/i18n': 'wp.i18n'
    }
};
