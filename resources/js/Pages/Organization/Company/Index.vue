<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    companies: Array,
});

const showingModal = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    name: '',
    code: '',
    address: '',
});

const openModal = (company = null) => {
    if (company) {
        currentId.value = company.id;
        form.name = company.name;
        form.code = company.code;
        form.address = company.address;
        editMode.value = true;
    } else {
        editMode.value = false;
        currentId.value = null;
        form.reset();
    }
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    form.reset();
};

const submit = () => {
    if (editMode.value) {
        form.put(route('companies.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('companies.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCompany = (id) => {
    if (confirm('Are you sure you want to delete this company?')) {
        form.delete(route('companies.destroy', id));
    }
};
</script>

<template>
    <Head title="Companies" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Companies</h2>
                <PrimaryButton @click="openModal()">Add Company</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="company in companies" :key="company.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ company.code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ company.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ company.address }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openModal(company)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                    <button @click="deleteCompany(company.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="showingModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editMode ? 'Edit Company' : 'Add Company' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="code" value="Code" />
                        <TextInput id="code" v-model="form.code" type="text" class="mt-1 block w-full" required autofocus />
                        <InputError :message="form.errors.code" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="address" value="Address" />
                        <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.address" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
