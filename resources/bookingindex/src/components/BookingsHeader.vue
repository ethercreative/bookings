<template>
	<header :class="$style.header">
		<div :class="$style.heading">
			<h1>{{heading}}</h1>
			<p>{{description}}</p>
		</div>

		<slot />
	</header>
</template>

<script>
	export default {
		name: "BookingsHeader",

		props: {
			heading: String,
			description: String,
		}
	};
</script>

<style lang="less" module>
	.header {
		display: grid;
		grid-template-columns: repeat(12, 1fr);
		grid-gap: 30px;
		padding: 40px 30px;

		background-color: #F1F5F8;
		border-bottom: 1px solid #D9DDE2;
	}

	.heading {
		grid-column: span 6;

		h1 {
			font-size: 44px;
		}

		p {
			color: #75889E;
			font-size: 14px;
			letter-spacing: 0;
			line-height: 26px;
		}
	}
</style>