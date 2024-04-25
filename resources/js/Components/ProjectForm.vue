<template>
    <div class="container max-w-sm bg-white p-4 rounded-sm">
        <p class="text-center font-bold">{{op === 1 ? 'Crear ' : 'Editar ' }} projecto</p>
        <form @submit.prevent="(op === 1 ? save() : update())">
            <div class="mb-4">
                <label for="name" class="block text-gray-800 text-sm mb-2">Nombre:</label>
                <input type="text" id="name" v-model="form.name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required />
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-800 text-sm  mb-2">Descripción:</label>
                <input type="text" id="description" v-model="form.description"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
            </div>

            <div class="mb-4">
                <label for="date_end" class="block text-gray-800 text-sm">Fecha de finalización:</label>
                <input type="date" id="date_end" v-model="form.date_end"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
            </div>

            <div class="flex justify-between">
                <button type="button" @click="cerrar"
                    class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded focus:outline-none focus:shadow-outline">
                    Cancelar
                </button>
                <button type="submit" :disabled="form.processing"
                    class="bg-cyan-500 hover:bg-cyan-600 text-white py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    OK
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    closeForm: { type: Function },
    project: { type: Object, default: () => ({}) },
    user_id: { type: Number },
    op: { type: Number }
});

const form = useForm({
    name: props.project.name, description: props.project.description, date_end: props.project.date_end, user_id: props.user_id
});

const cerrar = () => {
    form.reset();
    props.closeForm();
};

const save = () => {
    console.log(form)
    form.user_id = props.user_id
    form.post(route('projects.store'), {
        onSuccess: () => cerrar()
    });
}

const update = () => {
    form.put(route('projects.update', props.project.id), {
        onSuccess: () => cerrar()
    });
}
</script>