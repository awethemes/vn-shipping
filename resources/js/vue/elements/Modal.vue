<template>
	<teleport to="body">
		<div class="modal__screen-overlay">

			<div class="modal__frame" role="dialog" tabindex="-1">
				<div class="modal__content" role="document">
					<div class="modal__header">
						<div class="modal__header-heading-container" v-if="title">
							<h1 class="modal__header-heading">{{ title }}</h1>
						</div>

						<button
							type="button"
							class="components-button has-icon"
							aria-label="Close dialog"
							@click.prevent="$emit('modal-close');">
							<svg
								width="24"
								height="24"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 24 24"
								role="img"
								aria-hidden="true"
								focusable="false">
								<path
									d="M12 13.06l3.712 3.713 1.061-1.06L13.061 12l3.712-3.712-1.06-1.06L12 10.938 8.288 7.227l-1.061 1.06L10.939 12l-3.712 3.712 1.06 1.061L12 13.061z"></path>
							</svg>
						</button>
					</div>

					<slot />
				</div>
			</div>

		</div>
	</teleport>
</template>

<script>
export default {
	name: 'Modal',
	props: ['title']
};
</script>

<style scoped>
.modal__screen-overlay {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	background-color: rgba(0, 0, 0, 0.35);
	z-index: 100000;
}

@media (prefers-reduced-motion: reduce) {
	.modal__screen-overlay {
		animation-duration: 1ms;
		animation-delay: 0s;
	}
}

.modal__frame {
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	box-sizing: border-box;
	margin: 0;
	background: #fff;
	box-shadow: 0 10px 10px rgba(0, 0, 0, 0.25);
	border-radius: 2px;
	overflow: auto;
}

@media (min-width: 600px) {
	.modal__frame {
		top: 50%;
		right: auto;
		bottom: auto;
		left: 50%;
		min-width: 360px;
		max-width: calc(100% - 16px - 16px);
		max-height: 90%;
		transform: translate(-50%, -50%);
		animation: modal__appear-animation 0.1s ease-out;
		animation-fill-mode: forwards;
	}
}

.modal__header {
	box-sizing: border-box;
	border-bottom: 1px solid #ddd;
	padding: 0 32px;
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	background: #fff;
	align-items: center;
	height: 60px;
	z-index: 10;
	position: relative;
	position: sticky;
	top: 0;
	margin: 0 -32px 24px;
}

@supports (-ms-ime-align: auto) {
	.modal__header {
		position: fixed;
		width: 100%;
	}
}

.modal__header .modal__header-heading {
	font-size: 1rem;
	font-weight: 600;
}

.modal__header h1 {
	line-height: 1;
	margin: 0;
}

.modal__header .components-button {
	position: relative;
	left: 8px;
}

.modal__header-heading-container {
	align-items: center;
	flex-grow: 1;
	display: flex;
	flex-direction: row;
	justify-content: left;
}

.modal__content {
	box-sizing: border-box;
	height: 100%;
	padding: 0 32px 24px;
}

@supports (-ms-ime-align: auto) {
	.modal__content {
		padding-top: 60px;
	}
}
</style>
