import ExRule from "../_models/ExRule";
import RRule from "../_models/RRule";

export default function modelToRRule (model:RRule|ExRule) {
	const data = Object.keys(model).reduce((obj, key) => {
		if (key !== "id")
			obj[key] = model[key];

		if (typeof obj[key] === "object" && obj[key].hasOwnProperty("date")) {
			obj[key].date.setSeconds(0);
			obj[key].date.setMilliseconds(0);

			obj[key] = obj[key].date.toISOString();
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