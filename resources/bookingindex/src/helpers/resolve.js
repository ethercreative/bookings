export default function resolve (path, obj, separator = '.') {
	const properties = Array.isArray(path) ? path : path.split(separator);
	return properties.reduce((prev, curr) => prev && prev[curr], obj);
}