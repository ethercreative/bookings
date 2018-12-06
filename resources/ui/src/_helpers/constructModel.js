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
			if (!value)
				value = new Date();

			if (!(value instanceof Date))
				value = new Date(value.date.replace(" ", "T") + "Z");

			value.setUTCSeconds(0);
			value.setUTCMilliseconds(0);
		}

		self[key] = value;
	});
}