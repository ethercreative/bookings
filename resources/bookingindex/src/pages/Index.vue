<script>
import Vue from 'vue';
import Component from 'vue-class-component';
import Header from '../components/BookingsHeader';
import Tabs from '../components/Tabs';
import Search from '../components/Search';
import Deck from '../components/Deck';
import Card from '../components/Card';

@Component
export default class Index extends Vue {

	// Properties
	// =========================================================================

	// ...

	// Getters
	// =========================================================================

	get events () {
		return this.$store.state.events;
	}

	// Vue
	// =========================================================================

	mounted () {
		this.$store.dispatch('getEvents');
	}

	// Actions
	// =========================================================================

	search (query, done) {
		// TODO: Search
		setTimeout(done, 1000);
	}

	// Render
	// =========================================================================

	render () {
		return (
			<div class={this.$style.wrap}>
				<Header
					heading="Bookings"
					description="Welcome to bookings! You made a smart choice on how to manage your bookings with Craft. Click on the event below to view the attendance for each of the bookable slots or in total. Create new bookable products using the field type."
				>
					<Tabs />
				</Header>

				<Search
					performSearch={this.search}
					placeholder="Search Events"
				/>

				<Deck>
					{Object.values(this.events).map(event => (
						<Card
							key={event.id}
							event={event}
						/>
					))}
				</Deck>
			</div>
		);
	}

}
</script>

<style lang="less" module>
	@import "../variables";
</style>