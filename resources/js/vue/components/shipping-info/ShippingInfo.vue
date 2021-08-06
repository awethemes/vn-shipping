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
