module.exports = {
	base: "/bookings/",
	title: "Bookings for Craft CMS",
	description: "An advanced booking plugin for Craft CMS and Craft Commerce.",

	theme: "craftdocs",
	themeConfig: {
		repo: 'ethercreative/bookings',
		repoLabel: 'Source',
		docsRepo: 'ethercreative/bookings',
		docsDir: 'docs',
		docsBranch: 'master',
		editLinks: true,
		editLinkText: 'Suggest an improvement',

		codeLanguages: {
			php: "PHP",
			twig: "Twig",
		},

		nav: [
			{ text: "Stand-Alone", link: "/standalone/" },
			{ text: "Commerce", link: "/commerce/" },
			{ text: "Changelog", link: "https://github.com/ethercreative/bookings/blob/master/CHANGELOG.md" },
		],

		sidebar: {
			"/commerce/": [
				{
					title: "Templating",
					collapsable: false,
					children: [
						"templating/how-to-book",
						"templating/availability",
						"templating/sorting",
						"templating/ticket-custom-fields",
					],
				},
				{
					title: "API",
					collapsable: false,
					children: [
						"api/availability",
					],
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