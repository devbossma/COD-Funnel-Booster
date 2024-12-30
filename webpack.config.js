const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');

// Define entries
const entries = {
    'setup-wizard': './src/components/setup-wizard/index.js',
    'dashboard': './src/components/dashboard/index.js',
};

// Create entry points with proper paths
const entryPoints = {};
Object.keys(entries).forEach(name => {
    entryPoints[`js/${name}/${name}`] = entries[name];
});

module.exports = {
    ...defaultConfig,
    entry: entryPoints,
    output: {
        path: path.resolve(process.cwd(), 'assets/build'),
        filename: '[name].js',
        clean: true
    },
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            '@': path.resolve(__dirname, 'src/'),
        },
        extensions: ['.js', '.jsx'],
        fallback: {
            ...defaultConfig.resolve?.fallback,
            promise: require.resolve('core-js/features/promise')
        }
    },
    optimization: {
        minimizer: [
            new CssMinimizerPlugin({
                minimizerOptions: {
                    preset: [
                        'default',
                        {
                            discardComments: { removeAll: true },
                        },
                    ],
                },
            }),
            new TerserPlugin({
                terserOptions: {
                    format: {
                        comments: false,
                    },
                },
                extractComments: false,
            }),
        ],
    },
    module: {
        rules: [
            // Remove any existing CSS rules from defaultConfig
            ...defaultConfig.module.rules.filter(
                rule => !rule.test?.toString().includes('.css')
            ),
            // Add our custom CSS rule
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 1,
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    require('tailwindcss'),
                                    require('autoprefixer'),
                                    require('postcss-preset-env')({
                                        features: {
                                            'nesting-rules': true
                                        }
                                    }),
                                ],
                            },
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            plugin => plugin.constructor.name !== 'MiniCssExtractPlugin'
        ),
        new MiniCssExtractPlugin({
            filename: ({ chunk }) => {
                const componentPath = chunk.name.split('/');
                componentPath[0] = 'css';
                return `${componentPath.join('/')}.css`;
            },
        }),
    ],
    externals: {
        'react': 'React',
        'react-dom': 'ReactDOM',
        '@wordpress/element': 'wp.element'
    },
};