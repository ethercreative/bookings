const OUT = require('path').resolve(__dirname, '../../src/web/assets/cp/dist') + '/';

module.exports = {
	// The filename of the manifest file. If set to null, not manifest will
	// generate.
	manifest: OUT + "manifest.json",

	less: {
		// If set to false, Less compilation will not run
		run: true,

		// An array of entry Less file paths. Must be strings.
		entry: [
			"less/cp.less",
		],

		// An array of output CSS file paths. Must match the entry paths.
		// Output names can contain: "[hash:20]": a random hash (with a given
		// length)
		output: [
			OUT + "cp.[hash:20].css",
		],
	},

	js: {
		// If set to false, JS compilation will not run
		run: false,

		// An array of entry JS file paths
		// See https://webpack.js.org/configuration/entry-context/#entry for
		// supported entries
		entry: {
			cp: "./js/cp.js",
		},

		// An array of output JS file paths. Must match input paths.
		// See https://webpack.js.org/configuration/output/
		// for supported output configs
		output: {
			path: OUT,
			publicPath: "/admin/cpresources/bookings/",
			filename: "[name].[hash:20].js",
			chunkFilename: "chunks/[name].[chunkhash].js",
		},
	},

	critical: {
		run: false,
	},

	browserSync: {
		run: false,
	},
};
