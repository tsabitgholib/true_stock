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

const props = defineProps({
    items: Array,
    categories: Array,
    units: Array,
});

const showingModal = ref(false);
const editMode = ref(false);
const currentId = ref(null);

const form = useForm({
    item_code: '',
    item_name: '',
    description: '',
    item_category_id: '',
    unit_id: '',
    item_type: 'RAW',
    weight: 0,
    dimension: '',
    barcode: '',
    reorder_level: 0,
    safety_stock: 0,
    max_stock: 0,
});

const openModal = (item = null) => {
    if (item) {
        currentId.value = item.id;
        form.item_code = item.item_code;
        form.item_name = item.item_name;
        form.description = item.description;
        form.item_category_id = item.item_category_id;
        form.unit_id = item.unit_id;
        form.item_type = item.item_type;
        form.weight = item.weight;
        form.dimension = item.dimension;
        form.barcode = item.barcode;
        form.reorder_level = item.reorder_level;
        form.safety_stock = item.safety_stock;
        form.max_stock = item.max_stock;
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
        form.put(route('items.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('items.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this item?')) {
        form.delete(route('items.destroy', id));
    }
};
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Item Master</h2>
                <PrimaryButton @click="openModal()">Add Item</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in items" :key="item.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.item_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.item_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.item_category_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.item_type }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openModal(item)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                    <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="showingModal" @close="closeModal" maxWidth="4xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editMode ? 'Edit Item' : 'Add Item' }}
                </h2>

                <form @submit.prevent="submit" class="mt-6 grid grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="item_code" value="Item Code" />
                        <TextInput id="item_code" v-model="form.item_code" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.item_code" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="item_name" value="Item Name" />
                        <TextInput id="item_name" v-model="form.item_name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.item_name" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="description" value="Description" />
                        <textarea id="description" v-model="form.description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="item_category_id" value="Category" />
                        <select id="item_category_id" v-model="form.item_category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Select Category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                        <InputError :message="form.errors.item_category_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="unit_id" value="Unit" />
                        <select id="unit_id" v-model="form.unit_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Select Unit</option>
                            <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                        </select>
                        <InputError :message="form.errors.unit_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="item_type" value="Type" />
                        <select id="item_type" v-model="form.item_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="RAW">Raw Material</option>
                            <option value="WIP">Work In Progress</option>
                            <option value="FINISHED">Finished Goods</option>
                        </select>
                        <InputError :message="form.errors.item_type" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="reorder_level" value="Reorder Level" />
                        <TextInput id="reorder_level" v-model="form.reorder_level" type="number" step="0.01" class="mt-1 block w-full" />
                        <InputError :message="form.errors.reorder_level" class="mt-2" />
                    </div>

                    <div class="col-span-2 flex items-center justify-end mt-4">
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
