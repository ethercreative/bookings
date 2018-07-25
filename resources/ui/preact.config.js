import path from "path";
import flowStripTypes from "babel-plugin-transform-flow-strip-types";

export default (config, env, helpers) => {

	// Dev / Production Options
	// -------------------------------------------------------------------------

	// Tell Webpack to include our src folder when style-loader-ing
	const srcDir = path.resolve(__dirname, "src");
	config.module.loaders[4].include = [srcDir]; // Incl. for css modules & prefixing
	config.module.loaders[5].exclude = [srcDir]; // Excl. for node_modules css

	// Add the strip flow types plugin to babel
	let { rule } = helpers.getLoadersByName(config, "babel-loader")[0];
	rule.options.plugins.push(flowStripTypes);

	// Delete the polyfills entry point
	// (we want them in our bundle not separate, so we're importing them in index.js)
	delete config.entry.polyfills;

	// Production Only Options
	// -------------------------------------------------------------------------

	if (!env.production)
		return;

	// Remove plugins we don't need
	[
		"Object", // <~~ CopyWebpackPlugin (@see https://github.com/developit/preact-cli/issues/577#issuecomment-407741346)
		"HtmlWebpackPlugin",
		"HtmlWebpackExcludeAssetsPlugin",
		"PushManifestPlugin",
		"ScriptExtHtmlWebpackPlugin",
		"SWPrecacheWebpackPlugin",
	].forEach(p => {
		let { index } = helpers.getPluginsByName(config, p)[0];
		config.plugins.splice(index, 1);
	});

	// Prevent JS output file from being chunk-hashed
	config.output.filename = "bookings.js";

	// Prevent CSS output file from being chunk-hashed
	let { plugin } = helpers.getPluginsByName(config, "ExtractTextPlugin")[0];
	plugin.filename = "bookings.css";

	// 1998
	config.output.libraryTarget = "umd";

	// Output to our web assets dir
	config.output.path = path.resolve(__dirname, "../../src/web/assets/ui/dist");

};
