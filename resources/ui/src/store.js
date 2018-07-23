import Socrates from "socrates";
import RRule from "./_models/RRule";
import ExRule from "./_models/ExRule";

const store = Socrates({
	updateFrequencies (state, frequency) {
		return {
			...state,
			settings: {
				...state.settings,
				baseRule: new RRule({
					...state.settings.baseRule,
					frequency,
				}),
				exceptions: state.settings.exceptions.map(ex => new ExRule({
					...ex,
					frequency,
				})),
			}
		};
	},

	settings: {
		exceptions: {
			new (state) {
				return state.concat([new ExRule({
					frequency: store().settings.baseRule.frequency,
				})]);
			},

			update (state, { id, field, value }) {
				return state.map(ex => {
					if (ex.id !== id)
						return ex;

					return new ExRule({
						...ex,
						[field]: value,
					});
				});
			},

			duplicate (state, id) {
				const ruleToDupe = state.filter(ex => ex.id === id);

				if (ruleToDupe.length === 0)
					return;

				return state.concat([new ExRule({
					...ruleToDupe[0],
				}, true)]);
			},

			delete (state, id) {
				return state.filter(ex => ex.id !== id);
			},
		},
	},
});
export default store;