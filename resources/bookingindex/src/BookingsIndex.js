/* global Craft */
Craft.booting(Vue => {
	import('./BookingsIndex.vue').then(({ default: BookingsIndex }) => {
		console.log(BookingsIndex);
		Vue.component('bookings-index', BookingsIndex);
	});
	// Vue.component('bookings-index', require('./BookingsIndex.vue'));
});