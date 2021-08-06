<template>
  <div class="vns-loading">
    <div class="hourglass"></div>
  </div>
</template>

<script>
export default {
  name: 'Loading'
};
</script>

<style scoped lang="scss">
.vns-loading {
  display: flex;
  justify-content: center;
  padding: 1rem;
}

.vns-loading .hourglass {
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;

  &:after {
    content: " ";
    display: block;
    border-radius: 50%;
    width: 0;
    height: 0;
    margin: 8px;
    box-sizing: border-box;
    border: 32px solid #bcd4cc;
    border-color: #bcd4cc transparent #bcd4cc transparent;
    animation: hourglass 1.2s infinite;
  }
}

@keyframes hourglass {
  0% {
    transform: rotate(0);
    animation-timing-function: cubic-bezier(0.55, 0.055, 0.675, 0.19);
  }
  50% {
    transform: rotate(900deg);
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
  }
  100% {
    transform: rotate(1800deg);
  }
}
</style>
