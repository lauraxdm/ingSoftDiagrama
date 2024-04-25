<template>
  <button
    :type="type"
    class="rounded-md border border-transparent bg-primary px-4 py-2 text-center text-xs font-medium leading-4 text-white transition-colors duration-150 ease-in hover:bg-primary-shade focus:outline-none focus:ring-2 focus:bg-primary focus:ring-offset-2"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: "submit",
  },

  variant: {
    type: String,
    default: "primary",
  },
});
</script>
