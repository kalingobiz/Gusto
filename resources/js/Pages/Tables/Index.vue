<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tables: Array,
});

const tables = ref(props.tables);

const statusColors = {
    available: 'bg-emerald-100 border-emerald-300 text-emerald-800',
    occupied:  'bg-amber-100 border-amber-300 text-amber-800',
    reserved:  'bg-blue-100 border-blue-300 text-blue-800',
    cleaning:  'bg-gray-100 border-gray-300 text-gray-600',
};

const statusDot = {
    available: 'bg-emerald-500',
    occupied:  'bg-amber-500',
    reserved:  'bg-blue-500',
    cleaning:  'bg-gray-400',
};

function getActiveOrder(table) {
    return table.active_session?.orders?.find(o => !['paid', 'voided'].includes(o.status));
}

// Real-time table status updates
let echoChannel;
onMounted(() => {
    if (window.Echo) {
        echoChannel = window.Echo.channel('tables')
            .listen('.table.status-changed', ({ table_id, status }) => {
                const t = tables.value.find(t => t.id === table_id);
                if (t) t.status = status;
            });
    }
});
onUnmounted(() => echoChannel?.stopListening('.table.status-changed'));

function openOrder(table) {
    router.get(route('orders.create'), { table_id: table.id });
}

function markClean(table) {
    router.patch(route('tables.update', table.id), { status: 'available' }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Floor View</h1>
                <div class="flex gap-2 text-xs">
                    <span v-for="(color, s) in statusColors" :key="s" class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full" :class="statusDot[s]"></span>
                        {{ s }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    class="border-2 rounded-xl p-4 cursor-pointer transition-all hover:shadow-md"
                    :class="statusColors[table.status]"
                >
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-lg font-bold">T{{ table.number }}</span>
                        <span class="w-2.5 h-2.5 rounded-full mt-1" :class="statusDot[table.status]"></span>
                    </div>
                    <div class="text-xs opacity-70 mb-3">{{ table.capacity }} seats</div>

                    <template v-if="table.status === 'available'">
                        <button @click="openOrder(table)" class="w-full text-xs bg-emerald-600 text-white py-1.5 rounded-lg hover:bg-emerald-700">
                            + New Order
                        </button>
                    </template>

                    <template v-else-if="table.status === 'occupied'">
                        <div v-if="getActiveOrder(table)" class="text-xs mb-2 space-y-0.5">
                            <div class="font-medium">Order #{{ getActiveOrder(table).id }}</div>
                            <div>{{ getActiveOrder(table).items?.length }} items</div>
                        </div>
                        <div class="flex gap-1">
                            <Link v-if="getActiveOrder(table)" :href="route('orders.show', getActiveOrder(table).id)" class="flex-1 text-center text-xs bg-amber-600 text-white py-1.5 rounded-lg hover:bg-amber-700">
                                View
                            </Link>
                            <button @click="openOrder(table)" class="flex-1 text-xs bg-white/60 text-amber-800 py-1.5 rounded-lg hover:bg-white/80 border border-amber-300">
                                + Add
                            </button>
                        </div>
                    </template>

                    <template v-else-if="table.status === 'cleaning'">
                        <button @click="markClean(table)" class="w-full text-xs bg-gray-500 text-white py-1.5 rounded-lg hover:bg-gray-600">
                            Mark Clean
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
