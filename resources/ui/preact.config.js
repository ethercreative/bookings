import path from "path";
import flowStripTypes from "babel-plugin-transform-flow-strip-types";

export default (config, env, helpers) => {
	config.module.loaders[4].include = [
		path.resolve(__dirname, 'src'),
	];

	config.module.loaders[5].exclude = [
		path.resolve(__dirname, 'src'),
	];

	const babelLoader = config.module.loaders.filter(
		loader => loader.loader === 'babel-loader'
	)[0];
	babelLoader.options.plugins.push(flowStripTypes);

	delete config.entry.polyfills;
	config.output.filename = "[name].js";

	let { plugin } = helpers.getPluginsByName(config, "ExtractTextPlugin")[0];
	plugin.options.disable = true;

	if (env.production) {
		config.output.libraryTarget = "umd";
	}
};
