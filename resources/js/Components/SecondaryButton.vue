<template>
  <button
    :type="type"
    class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium leading-4 text-xs text-gray-700 tracking-widest shadow-sm hover:bg-secondary hover:text-white focus:outline-none focus:ring-2 focus:bg-secondary-shade focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: "button",
  },
});
</script>
