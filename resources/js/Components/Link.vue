<template>
  <Link :href="href" class="hover:text-tertiary" v-bind="$attrs">
    <slot />
  </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  variant: {
    type: String,
    default: "secondary",
  },
});
</script>
