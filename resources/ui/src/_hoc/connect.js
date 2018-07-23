import { h, Component } from "preact";
import store from "../store";

export default function connect (mapStoreToProps = () => ({})) {
	return function (WrappedComponent) {
		return class extends Component {
			constructor (props) {
				super(props);

				this.state = mapStoreToProps(store());

				store.subscribe(nextStore => {
					this.setState(mapStoreToProps(nextStore));
				});
			}

			shouldComponentUpdate (nextProps, nextState) {
				return (
					JSON.stringify(this.props) !== JSON.stringify(nextProps)
					|| JSON.stringify(this.state) !== JSON.stringify(nextState)
				);
			}

			render (props, state) {
				return <WrappedComponent dispatch={(...args) => store.apply(null, args)} {...props} {...state} />
			}
		}
	}
}