<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kpis: Object,
    valuationByWarehouse: Array
});

const stats = computed(() => [
    { name: 'Total Items', value: props.kpis.total_items, change: 'Master Data', changeType: 'neutral' },
    { name: 'Inventory Valuation', value: new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(props.kpis.total_valuation), change: 'Current Assets', changeType: 'increase' },
    { name: 'Low Stock Alerts', value: props.kpis.low_stock_count, change: 'Requires Action', changeType: props.kpis.low_stock_count > 0 ? 'decrease' : 'neutral' },
    { name: 'Total Warehouses', value: props.valuationByWarehouse.length, change: 'Active Sites', changeType: 'neutral' },
]);

const recentMovements = computed(() => props.kpis.recent_movements.map(m => ({
    id: m.id,
    item: m.item.item_name,
    type: m.movement_type,
    qty: m.quantity,
    user: m.user_id, // Could be user name if joined
    time: new Date(m.created_at).toLocaleString()
})));
</script>

<template>
    <Head title="Enterprise Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Enterprise WMS Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div v-for="item in stats" :key="item.name" class="bg-white overflow-hidden shadow rounded-lg px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">{{ item.name }}</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ item.value }}</dd>
                        <dd class="mt-2 flex items-center text-sm" :class="item.changeType === 'increase' ? 'text-green-600' : (item.changeType === 'decrease' ? 'text-red-600' : 'text-gray-500')">
                            {{ item.change }} since last month
                        </dd>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Movements -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Stock Movements</h3>
                        <div class="flow-root">
                            <ul role="list" class="-my-5 divide-y divide-gray-200">
                                <li v-for="movement in recentMovements" :key="movement.id" class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ movement.item }}</p>
                                            <p class="text-sm text-gray-500 truncate">{{ movement.type }} by {{ movement.user }}</p>
                                        </div>
                                        <div>
                                            <span :class="movement.qty > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ movement.qty > 0 ? '+' : '' }}{{ movement.qty }}
                                            </span>
                                            <p class="text-xs text-gray-400 mt-1 text-right">{{ movement.time }}</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Operations</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <button class="flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                New GRN
                            </button>
                            <button class="flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 shadow-sm">
                                New GIN
                            </button>
                            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                Stock Transfer
                            </button>
                            <button class="flex items-center justify-center px-4 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm">
                                Stock Opname
                            </button>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Valuation by Warehouse</h3>
                            <div class="space-y-3">
                                <div v-for="wh in valuationByWarehouse" :key="wh.name" class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">{{ wh.name }}</span>
                                    <span class="text-sm font-bold text-gray-900">
                                        {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(wh.valuation) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
