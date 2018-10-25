const webpack = require('webpack')
	, path = require('path')
	, fs = require('fs')
	, VueLoaderPlugin = require('vue-loader/lib/plugin')
	, MiniCssExtractPlugin = require('mini-css-extract-plugin')
	, UglifyJsPlugin = require('uglifyjs-webpack-plugin')
	, OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin')
	, CleanTerminalPlugin = require('clean-terminal-webpack-plugin')
	, ManifestPlugin = require('webpack-manifest-plugin');

const IS_PROD = process.env.NODE_ENV === 'production';

const devPublicPath = 'https://localhost:8000/';

module.exports = {
	devtool: IS_PROD ? 'source-map' : 'cheap-module-eval-source-map',

	mode: process.env.NODE_ENV || 'development',

	resolve: {
		extensions: ['.js', '.vue'],
		alias: {
			'vue$': 'vue/dist/vue.esm.js',
		},
	},

	entry: {
		BookingsIndex: './src/BookingsIndex.js'
	},

	output: {
		path: IS_PROD ? path.resolve(__dirname, '../../src/web/assets/bookingindex/dist') : '/',
		filename: IS_PROD ? '[name].[hash].js' : 'bundle.js',
		publicPath: IS_PROD ? '/admin/cpresources/bookings/' : devPublicPath,
	},

	module: {
		rules: [
			{
				enforce: 'pre',
				test: /\.(js|vue)$/,
				exclude: /node_modules/,
				use: {
					loader: 'eslint-loader',
					options: {
						// formatter: eslintFormatter,
						baseConfig: {
							extends: 'plugin:vue/essential',
							parserOptions: {
								parser: "babel-eslint",
								ecmaVersion: 7,
								sourceType: "module"
							},
							rules: {
								eqeqeq: [1, "smart"],
								semi: [1, "always"],
								"no-loop-func": [2],
								"no-unused-vars": [1],
								"no-console": [1],
								"no-mixed-spaces-and-tabs": [0],
							},
							env: {
								browser: true,
								es6: true,
							},
						},
					},
				},
			},

			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},

			{
				test: /\.js$/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [
							[
								require.resolve("@babel/preset-env"),
								{ useBuiltIns: 'usage' },
							],
						],
						plugins: [
							[
								require('@babel/plugin-proposal-decorators'),
								{ 'legacy': true }
							],
							require('@babel/plugin-syntax-dynamic-import'),
							require('@babel/plugin-proposal-class-properties'),
							require('babel-plugin-transform-vue-jsx'),
						],
						cacheDirectory: true,
					},
				},
				exclude: file => (
					/node_modules/.test(file)
					&& !/\.vue\.js/.test(file)
				),
			},

			{
				test: /\.less$/,
				use: [
					IS_PROD ? MiniCssExtractPlugin.loader : 'vue-style-loader',
					{
						loader: 'css-loader',
						options: {
							importLoaders: 1,
							modules: true,
							localIdentName: IS_PROD ? '[hash:base64:8]' : '[local]_[hash:base64:5]',
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							ident: 'postcss',
							plugins: () => [
								require('postcss-flexbugs-fixes'),
							],
							sourceMap: true,
						},
					},
					{
						loader: 'less-loader',
						options: {
							sourceMap: true,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							ident: 'postcss',
							plugins: () => [
								require('postcss-preset-env')({
									autoprefixer: {
										flexbox: 'no-2009',
									},
									stage: 4,
								}),
							],
							sourceMap: true,
						},
					}
				],
			},
		],
	},

	optimization: {
		minimizer: [
			new UglifyJsPlugin({
				cache: true,
				parallel: true,
				sourceMap: true,
			}),
			new OptimizeCSSAssetsPlugin({}),
		],
	},

	devServer: {
		public: devPublicPath,
		publicPath: '',
		host: '0.0.0.0',
		port: 8000,
		https: {
			key: fs.readFileSync('/Users/tam/mamp-ssl/3.dev.key'),
			cert: fs.readFileSync('/Users/tam/mamp-ssl/3.dev.crt'),
		},
		hot: true,
		hotOnly: false,
		inline: true,
		overlay: true,
		compress: true,
		filename: 'bundle.js',
		quiet: false,
		noInfo: false,
		stats: 'minimal',
		watchOptions: {
			poll: true,
		},
		headers: {
			'Access-Control-Allow-Origin': '*',
		},
	},

	plugins: [
		new VueLoaderPlugin(),

		new CleanTerminalPlugin(),

		new webpack.HotModuleReplacementPlugin(),
		new webpack.NamedModulesPlugin(),
		new webpack.DefinePlugin({
			'process.env': {
				NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development')
			}
		}),

		new ManifestPlugin({
			publicPath: '',
		}),

		new MiniCssExtractPlugin({
			filename: IS_PROD ? '[name].[hash].css' : '[name].css',
			chunkFilename: IS_PROD ? '[id].[hash].css' : '[id].css',
		}),
	],

	node: {
		dgram: 'empty',
		fs: 'empty',
		net: 'empty',
		tls: 'empty',
		child_process: 'empty',
	},

	performance: {
		hints: false,
	},

	externals: {
		'vue': 'Vue',
		'vue-router': 'VueRouter',
		'vuex': 'Vuex',
	},
};