/* global Garnish */

import Vue from 'vue';
import VueRouter from 'vue-router';

Garnish.$doc.ready(() => {

	const Index = () => import('./pages/Index');

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
		],
	});

	new Vue({
		el: '#main',
		template: '<main id="main" role="main"><router-view /></main>',
		router,
	});

});