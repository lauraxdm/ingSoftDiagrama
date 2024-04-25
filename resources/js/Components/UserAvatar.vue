<template>
  <img
    class="w-8 h-8 rounded-full"
    :src="getUserImage(props.user)"
    :alt="`Avatar de ${props.user.name}`"
    :title="props.user.name"
  />
</template>

<script setup>
const props = defineProps(
  {
    user: {
      type: Object,
    },
  },
  {
    user: {
      name: null,
    },
  }
);

const getUserImage = () => {
  const avatarApi = "https://api.dicebear.com/8.x/initials/svg";

  const avatar = new URLSearchParams({
    seed: props.user.name,
    size: 32,
  });

  return `${avatarApi}?${avatar.toString()}`;
};
</script>
