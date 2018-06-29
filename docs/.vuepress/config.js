module.exports = {
	base: "/bookings/",
	title: "Bookings for Craft CMS",
	description: "An advanced booking plugin for Craft CMS and Craft Commerce.",

	themeConfig: {
		nav: [
			{ text: "Core", link: "/core/" },
			{ text: "Stand Alone", link: "/stand-alone/" },
			{ text: "Commerce", link: "/commerce/" },
		],

		sidebar: {
			"/core/": [
				"elements",
			],
		},
	}
};