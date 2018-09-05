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
			if (!(value instanceof Date))
				value = new Date(value.date.replace(" ", "T") + "Z");
			value.setSeconds(0);
			value.setMilliseconds(0);
		}

		self[key] = value;
	});
}