<template>
    <div class="container max-w-sm bg-white p-4 rounded-sm">
        <p class="text-center font-bold">{{ op === 1 ? 'Crear ' : 'Editar ' }} diagrama de secuencia</p>
        <form @submit.prevent="(op == 1 ? save() : update())">
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
    close: { type: Function },
    project: { type: Object, default: () => ({}) },
    diagram: { type: Object, default: () => ({}) },
    op: { type: Number }
});

const form = useForm({
    name: props.diagram.name, description: props.diagram.description, project_id: props.project.id
});

const cerrar = () => {
    form.reset();
    props.close();
};

const save = () => {
    form.project_id = props.project.id
    form.post(route('diagrams.store'), {
        onSuccess: () => cerrar()
    });
}

const update = () => {
    form.put(route('diagrams.update', props.diagram.id), {
        onSuccess: () => cerrar()
    });
}
</script>