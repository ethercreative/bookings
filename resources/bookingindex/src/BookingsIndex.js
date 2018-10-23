/* global Garnish */

import Vue from 'vue';
import VueRouter from 'vue-router';
import store from './store';

Garnish.$doc.ready(() => {

	const Index = () => import('./pages/Index');
	const Event = () => import('./pages/Event');

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
		],
	});

	new Vue({
		el: '#main',
		store,
		template: '<main id="main" role="main"><router-view /></main>',
		router,
	});

});