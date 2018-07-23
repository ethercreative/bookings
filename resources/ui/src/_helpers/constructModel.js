import uuid from "./uuid";

export default function constructModel (self, def = {}, overwriteId = false) {
	self.id = uuid();

	def && Object.keys(def).map(key => {
		if (!self.hasOwnProperty(key))
			return;

		if (key === "id" && overwriteId)
			return;

		let value = def[key];

		if (~["start", "until"].indexOf(key)) {
			if (!(value.date instanceof Date))
				value.date = new Date(value.date);
			value.date.setSeconds(0);
			value.date.setMilliseconds(0);
		}

		self[key] = value;
	});
}