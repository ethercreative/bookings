const PhpManifestPlugin = require('webpack-php-manifest')
	, WebpackCdnPlugin = require('webpack-cdn-plugin');

module.exports = {
	outputDir: "../../src/web/assets/ui/dist",
	configureWebpack: {
		plugins: process.env.NODE_ENV === "production" ? [
			// Generate a PHP file with our compiled asset file names
			new PhpManifestPlugin(),

			// Ensure we don't include Vue in the compiled build
			// (we'll use the one bundled with Craft)
			new WebpackCdnPlugin({
				modules: [{
					name: "vue",
					var: "Vue",
				}]
			}),
		] : [],
	},
	chainWebpack: config => {
		// Ensure the manifest file isn't inlined into index.html
		config.plugins
			.delete('split-manifest')
			.delete('inline-manifest')
	},
	css: {
		sourceMap: true,
	},
};