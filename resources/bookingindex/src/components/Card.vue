<template>
	<router-link :to="'/events/' + event.id" :class="$style.card">
		<span
			:class="$style.image"
			:style="{background:image}"
		></span>
		<span :class="$style.content">
			<span :class="$style.name">{{event.title}}</span>

			<dates />

			<x-progress :width="0 / 10" />
		</span>
		<span :class="$style.content"></span>
	</router-link>
</template>

<script>
	import Progress from './Progress';
	import Dates from './Dates';

	export default {
		name: 'Card',

		props: {
			event: Object,
		},

		components: {
			'x-progress': Progress,
			Dates,
		},

		computed: {
			image () {
				const search = this.event.title.toLowerCase().split(' ').slice(0, 2).join(',');
				const url = `https://source.unsplash.com/400x130/?${search}&sig=${this.event.id}`;
				return `url(${url}) center / cover`;
			},
		},
	};
</script>

<style lang="less" module>
	.card {
		text-decoration: none !important;

		background: #fff;
		border: 1px solid #D9DDE2;
		border-radius: 3px;
		box-shadow: 0 16px 24px 0 rgba(48, 49, 51, 0.10);
		overflow: hidden;

		transition: transform 0.3s ease, box-shadow 0.3s ease;

		&:hover {
			transform: translateY(-10px);
			box-shadow: 0 30px 24px 0 rgba(48, 49, 51, 0.10);
		}
	}

	.image {
		display: block;
		width: 100%;
		height: 120px;
	}

	.content {
		display: block;
		padding: 20px;

		&:last-child {
			border-top: 1px solid #D9DDE2;
		}
	}

	.name {
		display: block;

		margin-bottom: 7px;
		color: #3F4549;
		font-size: 22px;
		letter-spacing: 0;
		line-height: 28px;
	}
</style>