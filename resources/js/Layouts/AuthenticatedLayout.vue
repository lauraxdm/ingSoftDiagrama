<template>
  <div>
    <div class="flex h-screen bg-gray-50">
      <Navigation />
      <NavigationMobile />

      <div class="flex flex-col flex-1 w-full">
        <TopMenu />

        <main class="h-full overflow-y-auto">
          <div class="mx-6 mt-4 px-6 grid">
            <div class="my-6 flex justify-between flex-wrap gap-4">
              <h2 class="text-2xl font-semibold text-gray-700">
                <slot name="header" />
              </h2>
              <slot name="header-end"></slot>
            </div>

            <slot />
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navigation from "./Navigation.vue";
import TopMenu from "./TopMenu.vue";
import NavigationMobile from "./NavigationMobile.vue";
</script>
