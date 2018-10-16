<template>
	<a href="#" :class="$style.card">
		<span
			:class="$style.image"
			:style="{background:image}"
		></span>
		<span :class="$style.content">
			<span :class="$style.name">Card Name</span>

			<dates />

			<x-progress :width="event.id / 10" />
		</span>
		<span :class="$style.content"></span>
	</a>
</template>

<script>
	import seededRandom from '../helpers/seededRandom';
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
				const rng = seededRandom(this.event.id);
				let start = (rng() * 0xFFFFFF << 0).toString(16),
					end   = (rng() * 0xFFFFFF << 0).toString(16);

				if (start.length < 6) start = '0' + start;
				if (end.length < 6) end = '0' + end;

				return `linear-gradient(to right, #${start} 0%, #${end} 100%)`;
			}
		}
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