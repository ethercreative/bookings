<template>
	<div :class="$style.wrap">
		<bookings-header
			heading="Bookings"
			description="Welcome to bookings! You made a smart choice on how to manage your bookings with Craft. Click on the event below to view the attendance for each of the bookable slots or in total. Create new bookable products using the field type."
		>
			<tabs />
		</bookings-header>

		<search
			:perform-search="search"
			placeholder="Search Events"
		/>

		<deck>
			<card
				v-for="event in events"
				:key="event.id"
				:event="event"
			/>
		</deck>
	</div>
</template>

<script>
	import { mapState } from 'vuex';
	import BookingsHeader from '../components/BookingsHeader';
	import Tabs from '../components/Tabs';
	import Search from '../components/Search';
	import Deck from '../components/Deck';
	import Card from '../components/Card';

	export default {
		name: 'Index',

		components: {
			BookingsHeader,
			Tabs,
			Search,
			Deck,
			Card,
		},

		computed: {
			...mapState([
				'events',
			]),
		},

		mounted () {
			this.$store.dispatch('getEvents');
		},

		methods: {
			search (query, done) {
				// TODO: Search
				setTimeout(done, 1000);
			},
		},
	};
</script>

<style lang="less" module>
	@import "../variables";
</style>