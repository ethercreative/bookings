import ExRule from "../_models/ExRule";
import RRule from "../_models/RRule";

export default function modelToRRule (model:RRule|ExRule) {
	const data = Object.keys(model).reduce((obj, key) => {
		if (key !== "id")
			obj[key] = model[key];

		if (obj[key] instanceof Date) {
			obj[key].setSeconds(0);
			obj[key].setMilliseconds(0);

			obj[key] = obj[key].toISOString();
		}

		return obj;
	}, {});

	switch (model.repeats) {
		case "until":
			delete data.count;
			break;
		case "count":
			delete data.until;
			break;
		default:
			delete data.count;
			delete data.until;
	}

	return data;
}