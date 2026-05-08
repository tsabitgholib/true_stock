<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const stats = [
    { name: 'Total Items', value: '1,234', change: '+12%', changeType: 'increase' },
    { name: 'Total Warehouses', value: '5', change: '0', changeType: 'neutral' },
    { name: 'Active Movements (Today)', value: '45', change: '+5%', changeType: 'increase' },
    { name: 'Low Stock Alerts', value: '12', change: '-2', changeType: 'decrease' },
];

const recentMovements = [
    { id: 1, item: 'Raw Material A', type: 'STOCK_IN', qty: 100, user: 'Admin', time: '10 mins ago' },
    { id: 2, item: 'Finished Product B', type: 'STOCK_OUT', qty: 50, user: 'Warehouse Staff', time: '1 hour ago' },
    { id: 3, item: 'Packaging Material C', type: 'TRANSFER', qty: 200, user: 'Admin', time: '2 hours ago' },
];
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
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
