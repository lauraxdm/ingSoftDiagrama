<template>
  <div class="dropdown relative">
    <div class="trigger h-full" @click="onToggle">
      <slot name="trigger" />
    </div>
    <transition
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-show="open"
        class="absolute z-10 right-0 mt-2 rounded-md border border-gray-100 bg-white p-2 text-gray-600 shadow-md space-y-2"
        :class="props.contentClass"
        aria-label="Dropdown Submenu"
      >
        <slot name="content" />
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
  contentClass: {
    type: Array,
    default: () => ["w-56"],
  },
});

const open = ref(false);

const closeOnEscape = (e) => {
  if (open.value && e.keyCode === 27) {
    open.value = false;
  }
};

const onToggle = () => {
  open.value = !open.value;
};

onMounted(() => {
  document.addEventListener("keydown", closeOnEscape);
});

onUnmounted(() => {
  document.removeEventListener("keydown", closeOnEscape);
});
</script>
