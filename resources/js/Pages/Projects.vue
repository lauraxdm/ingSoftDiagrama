<template>
  <Head title="Mis proyectos" />

  <AuthenticatedLayout>
    <template #header> Proyectos </template>
    <nav class="border-b text-sm flex justify-start -mt-4 mb-3">
      <button
        class="inline-block px-4 py-2"
        :class="[
          currentTab === 1
            ? 'border-b-2 border-tertiary text-tertiary font-semibold'
            : 'text-gray-700 hover:text-black',
        ]"
        @click="changeTab(1)"
      >
        Mis proyectos
      </button>
      <button
        class="inline-block px-4 py-2"
        :class="[
          currentTab === 2
            ? 'border-b-2 border-tertiary text-tertiary font-semibold'
            : 'text-gray-700 hover:text-black',
        ]"
        @click="changeTab(2)"
      >
        Colaboraciones
      </button>
    </nav>

    <div v-if="currentTab == 1">
      <div class="row justify-end">
        <PrimaryButton @click="showCreateForm">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-plus" />
            <span>Nuevo proyecto</span>
          </div>
        </PrimaryButton>
      </div>
      <table
        class="w-full text-sm text-left text-gray-500 mt-5 shadow-sm border"
      >
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3">Nombre del Proyecto</th>
            <th scope="col" class="px-6 py-3">Descripción</th>
            <th scope="col" class="px-6 py-3">
              <div class="flex items-center">Finalización</div>
            </th>
            <th scope="col" class="px-6 py-3">
              <span class="sr-only">Editar</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            class="bg-white border-b"
            v-for="project in myProjects"
            :key="project.id"
            v-if="myProjects.length"
          >
            <th
              scope="row"
              class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
            >
              <Link :href="route('projects.show', project.id)">
                {{ project.name }}
              </Link>
            </th>
            <td class="px-6 py-4">
              {{ project.description }}
            </td>
            <td class="px-6 py-4">
              {{ project.date_end }}
            </td>
            <td class="px-6 py-4 text-right">
              <button
                class="font-medium text-green-600 dark:text-green-500 hover:underline"
                @click="showEditForm(project)"
              >
                Editar
              </button>
              <button
                class="font-medium text-red-600 hover:underline mx-2"
                @click="deleteProject(project.id, project.name)"
              >
                Eliminar
              </button>
            </td>
          </tr>
          <tr class="bg-white border-b" v-else>
            <th
              scope="row"
              colspan="3"
              class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center"
            >
              No hay proyectos
            </th>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="currentTab == 2">
      <table
        class="w-full text-sm text-left text-gray-500 mt-5 shadow-sm border"
      >
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3">Nombre del Proyecto</th>
            <th scope="col" class="px-6 py-3">Descripción</th>
            <th scope="col" class="px-6 py-3">
              <div class="flex items-center">Finalización</div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            class="bg-white border-b"
            v-for="project in collaborations"
            :key="project.id"
            v-if="collaborations.length"
          >
            <th
              scope="row"
              class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
            >
              <Link :href="route('projects.show', project.id)">
                {{ project.name }}
              </Link>
            </th>
            <td class="px-6 py-4">
              {{ project.description }}
            </td>
            <td class="px-6 py-4">
              {{ project.date_end }}
            </td>
          </tr>
          <tr class="bg-white border-b" v-else>
            <th
              scope="row"
              colspan="3"
              class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center"
            >
              No hay proyectos colaborativos
            </th>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>

  <div v-if="isCreateFormShow" class="form-cover">
    <ProjectForm :closeForm="closeForm" :user_id="props.user_id" :op="1" />
  </div>
  <div v-if="isEditFormShow" class="form-cover">
    <ProjectForm
      :closeForm="closeForm"
      :user_id="props.user_id"
      :op="2"
      :project="projectSelected"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import Swal from "sweetalert2";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ProjectForm from "@/Components/ProjectForm.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Link from "@/Components/Link.vue";

const props = defineProps({
  myProjects: { type: Object },
  user_id: Number,
});

const collaborations = ref([]);

const form = useForm({});
let isCreateFormShow = ref(false);
let isEditFormShow = ref(false);
let projectSelected = ref(null);
let currentTab = ref(1);

function showCreateForm() {
  isCreateFormShow.value = true;
}

function showEditForm(project) {
  projectSelected.value = project;
  isEditFormShow.value = true;
}

function closeForm() {
  isCreateFormShow.value = false;
  isEditFormShow.value = false;
  projectSelected.value = null;
}

function changeTab(tab) {
  currentTab.value = tab;
}

const showAlert = (message) => {
  Swal.fire({
    title: message.text,
    icon: message.type,
    showConfirmButton: false,
    timer: 1500,
  });
};

const getCollaborations = async () => {
  await axios
    .get(route("api.users.collaborations", props.user_id))
    .then((response) => {
      collaborations.value = response.data;
    })
    .catch((error) => console.log(error));
};

const deleteProject = (id, name) => {
  Swal.fire({
    title: "¿Estás seguro de eliminar el proyecto " + name + "?",
    text: "Se eliminará el proyecto y los diagramas asociados al mismo",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Si, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      form.delete(route("projects.destroy", id));
    }
  });
};

onMounted(() => {
  getCollaborations();
});
</script>

<style>
.form-cover {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 999;
  background: rgba(0, 0, 0, 0.5);
}
</style>
