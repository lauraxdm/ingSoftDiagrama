<template>
  <Head :title="`Editor | ${props.diagram.name}`"></Head>

  <AuthenticatedLayout>
    <template #header>
      Diagrama de Secuencia: {{ props.diagram.name }}
    </template>

    <template #header-end>
      <div class="flex flex-grow justify-between gap-2">
        <Button variant="secondary" @click="exportDiagram('xmi')"
          >Exportar</Button
        >
        <div class="flex gap-2 ml-6">
          <Button variant="danger" @click="exportDiagram('java')">
            JAVA
          </Button>
          <Button
            class="bg-indigo-500 hover:bg-indigo-600"
            @click="exportDiagram('php')"
          >
            PHP
          </Button>
          <Button
            class="bg-gray-500 hover:bg-gray-600"
            @click="exportDiagram('cpp')"
          >
            C++
          </Button>
          <PrimaryButton class="ml-4" @click="onSave" v-if="isOwner"
            >Guardar
          </PrimaryButton>
        </div>
      </div>
    </template>

    <div class="app">
      <Editor :diagram="props.diagram" :project="props.project" ref="editor" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, usePage } from "@inertiajs/vue3";

import Editor from "@/Components/Editor.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Button from "@/Components/Button.vue";

const props = defineProps({
  diagram: { type: Object },
  project: { type: Object },
});

const emits = defineEmits(["export"]);

const editor = ref();

const isOwner = computed(
  () => props.project.user_id == usePage().props.auth.user.id
);

const exportDiagram = (as) => {
  if (as == "xmi") return editor.value.exportAsXMI();

  return editor.value.exportAsCode(as);
};

const onSave = () => {
  editor.value.saveDiagramContent(false);
};
</script>

<style scoped lang="scss">
.home {
  width: 1000px;
  margin: 0 auto;
}
</style>
