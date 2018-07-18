import { h, Component } from "preact";
import connect from "../_hoc/connect";

class Field extends Component {

	constructor (props) {
		super(props);

		// TODO: Set the default state using props from widget
		props.dispatch("boot", {
			ready: true,
			something: {
				here: {
					a: "you"
				}
			}
		});
	}

	componentDidMount () {
		setTimeout(() => {
			console.log("DISPATCH");
			this.props.dispatch(
				"change:something.here",
				{ a: "world" }
			);
		}, 1000)
	}

	render (props) {
		if (!props.ready)
			return null;

		return `hello ${props.something.here.a}!`;
	}

}

export default connect(({ ready, something }) => ({
	ready,
	something,
}))(Field);