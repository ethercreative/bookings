import Socrates from "socrates";
import RRule from "./_models/RRule";
import ExRule from "./_models/ExRule";

const store = Socrates({
	updateFrequencies (state, frequency) {
		return {
			settings: {
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

			duplicate (state, ruleToDupe) {
				return state.concat([new ExRule(ruleToDupe, true)]);
			},

			delete (state, id) {
				return state.filter(ex => ex.id !== id);
			},

			sort (state, sortedIds) {
				return state.slice().sort((a, b) => sortedIds.indexOf(a.id) - sortedIds.indexOf(b.id));
			},
		},
	},
});

export default store;