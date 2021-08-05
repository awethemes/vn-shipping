<template>
	<div class="trackingCode">
		<p><strong>{{ shippingData.courier_name }}</strong></p>
		<p><span>{{ shippingData.tracking_number }}</span></p>
	</div>
</template>

<script>
export default {
	name: 'ShippingInfo',
	props: ['shippingData']
};
</script>

<style scoped lang="scss">
.trackingCode {
	border: dashed 2px #737373;
	display: flex;
	padding: 1rem;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	box-sizing: border-box;

	p {
		margin: 0;
	}

	strong {
		color: #737373;
	}

	span {
		color: #ff9800;
		font-weight: 700;
		font-size: 1.6rem;
	}
}
</style>
