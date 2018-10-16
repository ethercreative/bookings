<template>
	<div :class="$style.wrap">
		<bookings-header
			heading="Bookings"
			description="Welcome to bookings! You made a smart choice on how to manage your bookings with Craft. Click on the event below to view the attendance for each of the bookable slots or in total. Create new bookable products using the field type."
		>
			<tabs />
		</bookings-header>

		<search :perform-search="search" />

		<deck>
			<card
				v-for="i in 8"
				:key="i"
				:event="{id: i}"
			/>
		</deck>
	</div>
</template>

<script>
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

		methods: {
			search (query, done) {
				// TODO: Search
				setTimeout(done, 1000);
			}
		}
	};
</script>

<style lang="less" module>
	@import (css) url('https://fonts.googleapis.com/css?family=Rubik:400,700');

	.wrap {
		width: 100%;
		min-height: 100%;

		font-family: 'Rubik', sans-serif;

		background-color: #fff;
	}
</style>