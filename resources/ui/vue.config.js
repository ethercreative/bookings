const PhpManifestPlugin = require('webpack-php-manifest')
	, WebpackCdnPlugin = require('webpack-cdn-plugin');

/**
 * Fix shit
 *
 * A shitty way of handling crafts cpresources urls when chunk splitting
 */
class FixShit {
	apply (compiler) {
		compiler.plugin('emit', function(compilation, callback) {
			const jsFiles = Object.keys(compilation.assets)
				.filter(fileName => /\.js$/.test(fileName));

			jsFiles.forEach(fileName => {
				compilation.assets[fileName].children.forEach(child => {
					if (child._value) {
						// Fix the baseUrl
						child._value = child._value.replace(
							'"/##FIX_SHIT##/"',
							"(() => { const _ = document.getElementsByTagName(\"script\"); return _[_.length-1].src.split('js/vendor.')[0]; })()"
						);

						// Rename webpack's chunk loader function to prevent any
						// possible issues with other webpack-compiled code
						child._value = child._value.replace(
							/webpackJsonp/g,
							"bookingsWebpackJsonp"
						);
					}
				});
			});

			callback();
		});
	}
}

const config = {
	baseUrl: "/##FIX_SHIT##/",
	outputDir: "../../src/web/assets/ui/dist",
	css: {
		sourceMap: true,
	},
};

if (process.env.NODE_ENV === "production") {
	config.configureWebpack = {
		plugins: [
			new FixShit(),

			// Generate a PHP file with our compiled asset file names
			new PhpManifestPlugin(),

			// Ensure we don't include Vue in the compiled build
			// (we'll use the one bundled with Craft)
			new WebpackCdnPlugin({
				modules: [{
					name: "vue",
					var: "Vue",
				}],
			}),
		],
	};

	config.chainWebpack = config => {
		// Ensure the manifest file isn't inlined into index.html & prevent
		// index.html from being generated
		config.plugins
			.delete('split-manifest')
			.delete('inline-manifest')
			.delete('html');
	};
}

module.exports = config;