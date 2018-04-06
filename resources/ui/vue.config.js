const webpack = require("webpack")
	, PhpManifestPlugin = require('webpack-php-manifest');

module.exports = {
	outputDir: "../../src/web/assets/ui/dist",
	configureWebpack: {
		plugins: process.env.NODE_ENV === "production" ? [
			new PhpManifestPlugin(),
			new webpack.IgnorePlugin(/^(vue)$/),
		] : [],
	},
	css: {
		sourceMap: true,
	},
};