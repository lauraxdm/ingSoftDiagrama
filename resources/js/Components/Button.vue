<template>
  <button
    :type="type"
    class="inline-flex items-center justify-center px-4 py-2 border rounded-md font-medium leading-4 text-xs tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
    :class="variant"
  >
    <slot />
  </button>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  type: {
    type: String,
    default: "button",
  },

  variant: {
    type: String,
    default: "primary",
  },
});

const variants = {
  primary: ["text-white"],
  secondary: ["bg-secondary", "hover:bg-secondary-shade", "text-white"],
  danger: ["text-white", "bg-red-500", "hover:bg-red-600", "focus:bg-red-700"],
};

const variant = computed(() => variants[props.variant ?? "primary"]);
</script>
