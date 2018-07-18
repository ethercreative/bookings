import "preact-cli/lib/lib/webpack/polyfills";
import habitat from "preact-habitat";
import Field from "./Field/Field";

// TODO: can we set the store here, using the widget props?

habitat(Field).render({
	selector: "craft-bookings",
	clean: true,
});
