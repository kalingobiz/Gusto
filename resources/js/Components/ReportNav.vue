<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const reportLinks = [
    { label: 'Sales',            routeName: 'admin.reports.sales' },
    { label: 'Hourly',           routeName: 'admin.reports.hourly-sales' },
    { label: 'BOM Variance',     routeName: 'admin.reports.bom-variance' },
    { label: 'Void Log',         routeName: 'admin.reports.voids' },
    { label: 'Audit Trail',      routeName: 'admin.reports.audit' },
    { label: 'Stock',            routeName: 'admin.reports.stock' },
    { label: 'Item Performance', routeName: 'admin.reports.item-performance' },
];

function isActive(routeName) {
    try {
        const path = new URL(route(routeName)).pathname;
        return page.url === path || page.url.startsWith(path + '?');
    } catch { return false; }
}
</script>

<template>
    <div class="flex gap-2 flex-wrap">
        <Link
            v-for="l in reportLinks"
            :key="l.routeName"
            :href="route(l.routeName)"
            :class="['report-nav-link', isActive(l.routeName) ? 'active' : '']"
        >{{ l.label }}</Link>
    </div>
</template>
