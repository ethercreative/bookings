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
			{ text: "Core", link: "/core/" },
			{ text: "Commerce", link: "/commerce/" },
			{ text: "Changelog", link: "https://github.com/ethercreative/bookings/blob/master/CHANGELOG.md" },
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
						"templating/functions",
						"templating/global-variables",
						"templating/bookings",
						"templating/availability",
					]
				},
				{
					title: "Example Templates",
					collapsable: false,
					children: [
						"example-templates/reserve-slot",
						"example-templates/confirm-booking",
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