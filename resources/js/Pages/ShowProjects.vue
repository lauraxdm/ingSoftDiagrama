<template>
  <Head :title="`Proyecto | ${project.name}`" />

  <AuthenticatedLayout>
    <template #header> Proyecto: {{ project.name }} </template>
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
        Diagramas
      </button>
      <button
        class="inline-block px-4 py-2 border-b-2"
        :class="[
          currentTab === 2
            ? 'border-b-2 border-tertiary text-tertiary font-semibold'
            : 'text-gray-700 hover:text-black',
        ]"
        @click="changeTab(2)"
      >
        Colaboradores
      </button>
    </nav>

    <div v-if="currentTab == 1">
      <div
        class="row justify-end"
        v-if="props.project.user_id == $page.props.auth.user.id"
      >
        <PrimaryButton @click="showCreateForm">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-plus" />
            <span>Crear diagrama</span>
          </div>
        </PrimaryButton>
      </div>
      <table
        class="w-full text-sm text-left text-gray-500 mt-5 shadow-sm border"
      >
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3">Nombre</th>
            <th scope="col" class="px-6 py-3">Descripción</th>
            <th scope="col" class="px-6 py-3">
              <span class="sr-only">Edit</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            class="bg-white border-b"
            v-for="item in diagrams"
            :key="item.id"
            v-if="diagrams.length"
          >
            <th
              scope="row"
              class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"
            >
              <Link :href="route('diagrams.show', item.id)">
                {{ item.name }}
              </Link>
            </th>
            <td class="px-6 py-4">
              {{ item.description }}
            </td>
            <td class="px-6 py-4 text-right">
              <button
                class="font-medium text-green-600 dark:text-green-500 hover:underline"
                @click="showEditForm(item)"
              >
                Editar
              </button>
              <button
                class="font-medium text-red-600 hover:underline mx-2"
                @click="deleteDiagram(item.id, item.name)"
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
              No hay diagramas
            </th>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="currentTab == 2">
      <div
        class="row justify-end"
        v-if="props.project.user_id == $page.props.auth.user.id"
      >
        <PrimaryButton @click="showAddColabForm">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-plus" />
            <span>Añadir colaborador</span>
          </div>
        </PrimaryButton>
      </div>
      <table
        class="w-full text-sm text-left text-gray-500 mt-5 shadow-sm border"
      >
        <thead class="text-xs uppercase bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3">Nombre</th>
            <th scope="col" class="px-6 py-3">Descripción</th>
            <th scope="col" class="px-6 py-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            class="bg-white border-b text-gray-900"
            v-for="user in collaborators"
            :key="user.id"
            v-if="collaborators.length"
          >
            <td class="px-6 py-4">
              {{ user.name }}
              <span class="text-gray-600">{{
                +user.id == props.project.user_id ? "(Dueño)" : ""
              }}</span>
            </td>
            <td class="px-6 py-4">
              {{ user.email }}
            </td>
            <td class="px-6 py-4">
              <button
                class="font-medium text-red-600 hover:underline mx-2"
                v-if="
                  props.project.user_id == $page.props.auth.user.id &&
                  user.id != props.project.user_id
                "
                @click="dismissUser(user.id, user.name)"
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
              No hay colaboradores
            </th>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>
  <div v-if="isCreateFormShow" class="form-cover">
    <DiagramForm :close="closeForm" :project="props.project" :op="1" />
  </div>
  <div v-if="isEditFormShow" class="form-cover">
    <DiagramForm
      :close="closeForm"
      :project="props.project"
      :op="2"
      :diagram="diagramSelected"
    />
  </div>
  <div v-if="isInvModalShow" class="form-cover">
    <InvitationModal :close="closeForm" :project_id="props.project.id" />
  </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import DiagramForm from "@/Components/DiagramForm.vue";
import InvitationModal from "@/Components/InvitationModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Link from "@/Components/Link.vue";

const props = defineProps({
  project: { type: Object },
  diagrams: { type: Object },
});

const form = useForm({});
const collaborators = ref([]);
let isCreateFormShow = ref(false);
let isEditFormShow = ref(false);
let isInvModalShow = ref(false);
let diagramSelected = ref(null);
let currentTab = ref(1);

function showCreateForm() {
  isCreateFormShow.value = true;
}

function showEditForm(diagram) {
  diagramSelected.value = diagram;
  isEditFormShow.value = true;
}

function closeForm() {
  isCreateFormShow.value = false;
  isEditFormShow.value = false;
  isInvModalShow.value = false;
  diagramSelected.value = null;
}

function changeTab(tab) {
  currentTab.value = tab;
}

function showAddColabForm() {
  isInvModalShow.value = true;
}

const getCollaborators = async () => {
  await axios
    .get(route("api.projects.collaborators", props.project.id))
    .then((response) => {
      collaborators.value = response.data;
    })
    .catch((error) => console.log(error));
};

const deleteDiagram = (id, name) => {
  Swal.fire({
    title: "¿Estás seguro de eliminar el diagrama " + name + "?",
    text: "Esta accion es irreversible",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Si, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      form.delete(route("diagrams.destroy", id));
    }
  });
};

const dismissUser = (id, name) => {
  Swal.fire({
    title: "¿Estás seguro de desvincular a " + name + "?",
    text: "Ya no estará en el proyecto",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Si, desvincular",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      axios
        .delete(
          route("api.projects.dismiss", { project: props.project.id, user: id })
        )
        .then((response) => {
          console.log(response.data);
          getCollaborators();
        })
        .catch((error) => {
          console.error(error);
        });
    }
  });
};

onMounted(() => {
  getCollaborators();
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
