module.exports = {
	base: "/bookings/",
	title: "Bookings for Craft CMS",
	description: "An advanced booking plugin for Craft CMS and Craft Commerce.",

	theme: "craftdocs",
	themeConfig: {
		codeLanguages: {
			php: "PHP",
			twig: "Twig",
		},

		nav: [
			{ text: "Core", link: "/core/" },
			{ text: "Stand Alone", link: "/stand-alone/" },
			{ text: "Commerce", link: "/commerce/" },
		],

		sidebar: {
			"/core/": [
				{
					title: "Introduction",
					collapsable: false,
					children: [
						"concepts",
					],
				},
				{
					title: "Templating",
					collapsable: false,
					children: [
						"templating/bookings",
						"templating/availability",
					]
				},
			],
		},
	},

	markdown: {
		anchor: { level: [2, 3] },
		config (md) {
			let markup = require('vuepress-theme-craftdocs/markup');
			md.use(markup);
		},
	},
};