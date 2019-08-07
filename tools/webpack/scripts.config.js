const VueLoaderPlugin = require('vue-loader/lib/plugin')
const path = require('path');

module.exports = (env) => {
    env = env || {};
    return {
        name: 'scripts',
        mode: env.prod ? 'production' : 'development',
        entry: './resources/js/src/app/index.js',
        output: {
            filename: '../../../resources/js/dist/wp-ceres' + (env.pRrod ? '.min' : '') + '.js',
            path: path.resolve(__dirname, 'dist')
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.js'
            },
        },
        devtool: 'source-map',
        module: {
            rules: [
                {
                    test: /\.vue$/,
                    loader: 'vue-loader'
                },
                {
                    test: require.resolve('jquery'),
                    use: [
                        {
                            loader: 'expose-loader',
                            options: '$'
                        },
                        {
                            loader: 'expose-loader',
                            options: 'jQuery'
                        }
                    ]
                },
                {
                    test: /\.m?js$/,
                    exclude: /(node_modules)/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                }
            ]
        },
        plugins: [
            new VueLoaderPlugin()
        ]
    }
};
