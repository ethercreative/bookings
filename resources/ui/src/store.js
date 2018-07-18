import Socrates from "socrates";

const store = Socrates({
	boot: (state, action) => action,
});

export default store;