<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tables: Array,
});

const tables = ref(props.tables);

const statusColors = {
    available: 'bg-[var(--bg-card)] border-[var(--border)] text-[var(--text-strong)] hover:border-emerald-500/50',
    occupied:  'bg-amber-500/5 border-amber-500/20 text-[var(--text-strong)] shadow-[0_0_15px_rgba(245,158,11,0.05)]',
    reserved:  'bg-blue-500/5 border-blue-500/20 text-[var(--text-strong)]',
    cleaning:  'bg-[var(--bg-surface)] border-[var(--border)] text-[var(--text-muted)] opacity-60',
};

const statusLabel = {
    available: 'text-emerald-500',
    occupied:  'text-amber-500',
    reserved:  'text-blue-500',
    cleaning:  'text-[var(--text-muted)]',
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
        <div class="space-y-8 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black italic tracking-tight">Active Floor Map</h1>
                    <p class="text-[var(--text-muted)] text-sm">Real-time table status and order management grid.</p>
                </div>
                
                <!-- Status Legend -->
                <div class="flex flex-wrap items-center gap-4 bg-[var(--bg-card)] px-5 py-3 rounded-2xl border border-[var(--border)] shadow-sm">
                    <div v-for="(color, s) in statusLabel" :key="s" class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full ring-4 ring-opacity-20" :class="[statusDot[s], s === 'occupied' ? 'animate-pulse ring-amber-500' : 'ring-transparent']"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{{ s }}</span>
                    </div>
                </div>
            </div>

            <!-- Tables Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-5">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    class="relative group glass-card border-2 p-5 transition-all duration-300 hover:-translate-y-1"
                    :class="statusColors[table.status]"
                >
                    <!-- Table ID & Capacity -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-tighter block mb-1 opacity-50">SECTION A</span>
                            <span class="text-2xl font-black italic">{{ table.number }}</span>
                        </div>
                        <div class="text-right">
                            <span class="w-3 h-3 rounded-full block ml-auto mb-1 shadow-sm" :class="[statusDot[table.status], table.status === 'occupied' ? 'animate-pulse' : '']"></span>
                            <span class="text-[10px] font-black opacity-50">{{ table.capacity }}p</span>
                        </div>
                    </div>

                    <!-- Occupied State Info -->
                    <div v-if="table.status === 'occupied'" class="mb-4 space-y-1.5">
                        <div v-if="getActiveOrder(table)" class="bg-[var(--bg-main)]/50 p-2 rounded-xl border border-[var(--border)]">
                            <div class="text-[10px] font-black text-[var(--brand)] uppercase tracking-widest mb-0.5">ORDER #{{ getActiveOrder(table).id }}</div>
                            <div class="text-[10px] font-bold text-[var(--text-strong)] truncate">
                                {{ getActiveOrder(table).items?.length || 0 }} Items Selected
                            </div>
                        </div>
                    </div>

                    <!-- Available State Action -->
                    <div v-if="table.status === 'available'" class="pt-2">
                        <button 
                            @click="openOrder(table)" 
                            class="w-full text-[10px] font-black uppercase tracking-widest bg-[var(--brand)] text-white py-3 rounded-xl shadow-lg shadow-[var(--brand-glow)] hover:bg-[var(--brand-hover)] active:scale-95 transition-all"
                        >
                            + NEW ORDER
                        </button>
                    </div>

                    <!-- Occupied State Actions -->
                    <div v-else-if="table.status === 'occupied'" class="flex gap-2">
                        <Link 
                            v-if="getActiveOrder(table)" 
                            :href="route('orders.show', getActiveOrder(table).id)" 
                            class="flex-1 text-center text-[10px] font-black uppercase tracking-widest bg-[var(--bg-surface)] text-[var(--text-strong)] py-2.5 rounded-xl border border-[var(--border)] hover:bg-[var(--bg-card)] transition-all"
                        >
                            VIEW
                        </Link>
                        <button 
                            @click="openOrder(table)" 
                            class="flex-1 text-[10px] font-black uppercase tracking-widest bg-[var(--brand)] text-white py-2.5 rounded-xl hover:bg-[var(--brand-hover)] shadow-md transition-all"
                        >
                            + ADD
                        </button>
                    </div>

                    <!-- Cleaning State Action -->
                    <div v-else-if="table.status === 'cleaning'" class="pt-2">
                        <button 
                            @click="markClean(table)" 
                            class="w-full text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 py-3 rounded-xl hover:bg-emerald-500 hover:text-white transition-all"
                        >
                            CLEARED
                        </button>
                    </div>
                    
                    <!-- Decorative pulse for occupied tables -->
                    <div v-if="table.status === 'occupied'" class="absolute -inset-0.5 bg-amber-500/10 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
