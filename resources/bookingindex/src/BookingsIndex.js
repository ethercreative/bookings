/* global Garnish */

import Vue from 'vue';
import VueRouter from 'vue-router';
import PortalVue from 'portal-vue';
import store from './store';

Garnish.$doc.ready(() => {

	Vue.use(PortalVue);

	const Index = () => import('./pages/Index');
	const Event = () => import('./pages/Event');
	const Booking = () => import('./pages/Booking');

	const router = new VueRouter({
		base: window.bookingsBaseUrl,
		mode: 'history',
		routes: [
			{
				path: '/',
				name: 'Index',
				component: Index,
			},
			{
				path: '/past',
				name: 'Past',
				component: Index,
			},
			{
				path: '/events/:eventId',
				name: 'Event',
				component: Event,
			},
			{
				path: '/bookings/:bookingId',
				name: 'Booking',
				component: Booking,
			},
		],
	});

	new Vue({
		el: '#main',
		store,
		template: '<main id="main" role="main"><router-view /></main>',
		router,
	});

});