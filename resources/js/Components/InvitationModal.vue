<template>
    <div class="container max-w-sm bg-white p-4 rounded-sm">
        <div class="flex items-start justify-between">
            <h3 class="font-semibold">
                Invitar a colaborar
            </h3>
            <button type="button" @click="close"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <table class="w-full text-left text-sm text-gray-500 mt-5 shadow-sm border">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Usuario
                    </th>

                    <th scope="col" class="px-6 py-3">
                        Opcion
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-white border-b" v-for="item in users" :key="item.id" v-if="users.length">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                        {{ item.name }}
                    </th>
                    <td class="px-6 py-4">
                        <button type="button" @click="invite(item.id)"
                            class="bg-cyan-600 hover:bg-cyan-700 text-white py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                            Invitar
                        </button>
                    </td>

                </tr>
                <tr class="bg-white border-b" v-else>
                    <th scope="row" colspan="2" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-center">
                        No hay usuarios disponibles
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    close: { type: Function },
    project_id: { type: Number, },
});

let users = ref([]);

const form = useForm({
    project_id: props.project_id, user_id: null
});

const fetchUsers = () => {
    axios.get('/api/users', { params: { project_id: props.project_id } })
        .then(response => users.value = response.data)
        .catch(error => console.log(error))
}

const invite = (user_id) => {
    axios.post(route('api.invitations.store'), {
        project_id: props.project_id,
        user_id: user_id
    })
        .then((response) => {
            console.log(response.data)
            props.close()
        })
        .catch(error => console.log(error))
}

onMounted(() => {
    fetchUsers();
})
</script>