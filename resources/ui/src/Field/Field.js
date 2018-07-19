import { h, Component } from "preact";
import connect from "../_hoc/connect";

class Field extends Component {

	render (props) {
		return `hello ${props.color}!`;
	}

}

export default connect(({ color }) => ({
	color
}))(Field);