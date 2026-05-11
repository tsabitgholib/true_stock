<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    stocks: Array,
    warehouses: Array,
    items: Array,
    filters: Object
});

const filterForm = ref({
    warehouse_id: props.filters.warehouse_id || '',
    item_id: props.filters.item_id || '',
});

watch(filterForm, (newVal) => {
    router.get(route('inventory.index'), newVal, {
        preserveState: true,
        replace: true
    });
}, { deep: true });

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};

const getStatusClass = (stock) => {
    if (stock.available_quantity <= 0) return 'text-red-600 font-bold';
    if (stock.available_quantity <= stock.item.reorder_level) return 'text-yellow-600 font-bold';
    return 'text-green-600';
};

</script>

<template>
    <Head title="Stock Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock Inventory</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <!-- Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Warehouse</label>
                            <select v-model="filterForm.warehouse_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Warehouses</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Item</label>
                            <select v-model="filterForm.item_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Items</option>
                                <option v-for="item in items" :key="item.id" :value="item.id">[{{ item.item_code }}] {{ item.item_name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Stock Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse / Loc / Rack</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Physical Stock</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="stock in stocks" :key="stock.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ stock.item.item_name }}</div>
                                        <div class="text-xs text-gray-500">{{ stock.item.item_code }} | {{ stock.item.category?.name }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ stock.warehouse.name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ stock.location?.name || '-' }} / {{ stock.rack?.name || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ stock.batch?.batch_number || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500" v-if="stock.batch?.expiry_date">
                                            Exp: {{ stock.batch.expiry_date }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                        {{ formatNumber(stock.quantity) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-orange-600">
                                        {{ formatNumber(stock.reserved_quantity) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm" :class="getStatusClass(stock)">
                                        {{ formatNumber(stock.available_quantity) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{ stock.item.unit?.code }}
                                    </td>
                                </tr>
                                <tr v-if="stocks.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        No stock records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
