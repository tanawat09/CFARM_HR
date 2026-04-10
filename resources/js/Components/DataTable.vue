<script setup>
import Pagination from '@/Components/Pagination.vue';

defineProps({
    columns: {
        type: Array,
        required: true,
        // Example: [{ key: 'id', label: 'ID' }, { key: 'name', label: 'Name' }, { key: 'actions', label: 'Actions' }]
    },
    data: {
        type: [Array, Object],
        required: true,
        // Can be an array of objects or a Laravel paginated object { data: [], links: [] }
    }
});
</script>

<template>
    <div class="overflow-x-auto bg-white rounded-xl border border-slate-200 shadow-sm">
        <table class="w-full text-sm text-left text-slate-600">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                <tr>
                    <th v-for="col in columns" :key="col.key" class="px-4 py-3" :class="col.class">
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in (data.data || data)" :key="item.id || index" class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                    <td v-for="col in columns" :key="col.key" class="px-4 py-3 font-medium" :class="col.cellClass">
                        <!-- Dynamic Slot Pattern: Enables custom template overrides per column -->
                        <slot :name="'cell-'+col.key" :item="item" :index="index">
                            {{ item[col.key] }}
                        </slot>
                    </td>
                </tr>
                <tr v-if="(data.data || data).length === 0">
                    <td :colspan="columns.length" class="px-4 py-8 text-center text-slate-500">
                        <slot name="empty">ไม่มีข้อมูล</slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Optional Pagination -->
    <div v-if="data.links" class="mt-6 flex justify-end">
        <Pagination :links="data.links" />
    </div>
</template>
