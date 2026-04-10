<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

// Custom debounce function to replace lodash
const debounce = (fn, delay = 300) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    worksites: Object,
    branches: Array,
    filters: Object
});

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
    router.get(route('worksites.index'), {
        search: value
    }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
    <Head title="จัดการสาขา" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">🏢 จัดการสาขา (Branches)</h2>
                <Link :href="route('worksites.create')" class="inline-flex items-center gap-2 bg-emerald-600 border border-transparent rounded-xl px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 active:bg-emerald-800 transition shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เพิ่มสาขาใหม่
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Data Display -->
                <div class="glass sm:rounded-3xl p-8 relative overflow-hidden mb-8 border border-white/60 shadow-xl">
                    <!-- Background blobs -->
                    <div class="absolute -top-20 -left-20 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 pointer-events-none"></div>
                    <div class="absolute top-40 right-10 w-48 h-48 bg-teal-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 pointer-events-none"></div>

                    <!-- Search Bar -->
                    <div class="flex justify-between items-center mb-8 relative z-10 w-full md:w-1/3">
                        <div class="w-full relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input v-model="search" type="text" placeholder="ค้นหาชื่อสาขา, รหัส..." class="pl-10 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-white/70 backdrop-blur transition hover:bg-white" />
                        </div>
                    </div>

                    <!-- Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                        <div v-for="ws in worksites.data" :key="ws.id" class="bg-white/80 backdrop-blur border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group hover:-translate-y-1">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-emerald-50 border border-emerald-100 p-3 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <span class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1 rounded-full border border-slate-200">
                                    {{ ws.code }}
                                </span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-slate-800 mb-1 leading-tight">{{ ws.name }}</h3>
                            <p class="text-sm text-slate-500 mb-4 h-10 line-clamp-2">{{ ws.address || 'ไม่มีข้อมูลที่อยู่' }}</p>
                            
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mt-2">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">พิกัด GPS</span>
                                    <span class="text-xs font-mono bg-white px-2 py-0.5 rounded border">{{ Number(ws.latitude || 0).toFixed(4) }}, {{ Number(ws.longitude || 0).toFixed(4) }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">รัศมีการลงเวลา</span>
                                    <span class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ ws.radius_meters }} เมตร</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">พนักงานในสาขา</span>
                                    <span class="text-xs font-bold text-slate-700">{{ ws.employees_count }} คน</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                                <div class="text-xs font-medium text-slate-500">
                                    สังกัด: {{ ws.branch?.name || 'อิสระ' }}
                                </div>
                                <Link :href="route('worksites.edit', ws.id)" class="text-indigo-600 font-bold text-sm hover:text-indigo-800 transition">
                                    แก้ไข &rarr;
                                </Link>
                            </div>
                        </div>

                        <div v-if="worksites.data.length === 0" class="col-span-full py-16 text-center border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50">
                            <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-700">ไม่พบข้อมูลสาขา</h3>
                            <p class="text-slate-500 mt-1">ลองเปลี่ยนคำค้นหาหรือเพิ่มสาขาใหม่</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 flex justify-end relative z-10 w-full overflow-hidden">
                        <Pagination :links="worksites.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
