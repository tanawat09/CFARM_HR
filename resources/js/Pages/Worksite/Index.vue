<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

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
    router.get(route('worksites.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const palettes = [
    { grad: 'from-emerald-500 to-teal-500', glow: 'shadow-emerald-200/50', soft: 'bg-emerald-50 text-emerald-700', iconBg: 'bg-emerald-100' },
    { grad: 'from-sky-500 to-blue-500', glow: 'shadow-sky-200/50', soft: 'bg-sky-50 text-sky-700', iconBg: 'bg-sky-100' },
    { grad: 'from-indigo-500 to-purple-500', glow: 'shadow-indigo-200/50', soft: 'bg-indigo-50 text-indigo-700', iconBg: 'bg-indigo-100' },
    { grad: 'from-amber-500 to-orange-500', glow: 'shadow-amber-200/50', soft: 'bg-amber-50 text-amber-700', iconBg: 'bg-amber-100' },
    { grad: 'from-rose-500 to-pink-500', glow: 'shadow-rose-200/50', soft: 'bg-rose-50 text-rose-700', iconBg: 'bg-rose-100' },
    { grad: 'from-cyan-500 to-teal-500', glow: 'shadow-cyan-200/50', soft: 'bg-cyan-50 text-cyan-700', iconBg: 'bg-cyan-100' },
];
const paletteFor = (id) => palettes[id % palettes.length];

const totalWorksites = computed(() => props.worksites?.total || 0);
const totalEmployees = computed(() => (props.worksites?.data || []).reduce((s, w) => s + (w.employees_count || 0), 0));
</script>

<template>
    <Head title="จัดการสาขา" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200/60">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">จัดการสาขา</h2>
                        <p class="text-xs text-slate-500 font-medium">กำหนดสถานที่ปฏิบัติงานและ Geofence สำหรับการลงเวลา</p>
                    </div>
                </div>
                <Link :href="route('worksites.create')" class="group inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-emerald-300/50 hover:shadow-emerald-400/60 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    เพิ่มสาขาใหม่
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats + Toolbar -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full opacity-60"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md shadow-emerald-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">สาขาทั้งหมด</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalWorksites }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-60"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-md shadow-indigo-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">พนักงานทั้งหมด</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalEmployees }}</div>
                        </div>
                    </div>
                    <div class="md:col-span-2 bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center">
                        <div class="relative w-full">
                            <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input v-model="search" type="text" placeholder="ค้นหาชื่อสาขา, รหัส, ที่อยู่..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-medium bg-slate-50/60" />
                        </div>
                    </div>
                </div>

                <!-- Cards -->
                <div v-if="worksites.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <div v-for="ws in worksites.data" :key="ws.id"
                         :class="['group relative bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1', paletteFor(ws.id).glow]">
                        <div :class="['h-1.5 bg-gradient-to-r', paletteFor(ws.id).grad]"></div>
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-12 h-12 rounded-2xl bg-gradient-to-br flex items-center justify-center shadow-lg', paletteFor(ws.id).grad]">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-extrabold text-slate-800 text-lg leading-tight">{{ ws.name }}</h3>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ ws.code }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="flex items-start gap-2 mb-4 text-sm text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                <p class="line-clamp-2 leading-tight">{{ ws.address || 'ไม่มีข้อมูลที่อยู่' }}</p>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-2 gap-2.5 mb-4">
                                <div class="bg-slate-50 rounded-xl px-3 py-2.5 border border-slate-100">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase mb-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                                        พิกัด
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-700 font-bold">{{ Number(ws.latitude || 0).toFixed(4) }}, {{ Number(ws.longitude || 0).toFixed(4) }}</div>
                                </div>
                                <div :class="[paletteFor(ws.id).soft, 'rounded-xl px-3 py-2.5 border', paletteFor(ws.id).soft.replace('bg-', 'border-').replace('-50', '-100')]">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold uppercase mb-0.5 opacity-70">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        รัศมี
                                    </div>
                                    <div class="text-sm font-black">{{ ws.radius_meters }} ม.</div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="pt-4 border-t border-dashed border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', paletteFor(ws.id).iconBg]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase leading-none">พนักงาน</div>
                                        <div class="text-sm font-black text-slate-800 leading-tight">{{ ws.employees_count }} คน</div>
                                    </div>
                                </div>
                                <Link :href="route('worksites.edit', ws.id)" class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    แก้ไข
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-else class="bg-white rounded-2xl border border-dashed border-slate-200 py-16 text-center">
                    <div class="inline-flex w-20 h-20 rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-700 text-lg">{{ search ? 'ไม่พบสาขาที่ค้นหา' : 'ยังไม่มีสาขา' }}</h3>
                    <p class="text-sm text-slate-400 font-medium mt-1">{{ search ? 'ลองเปลี่ยนคำค้นหา' : 'เริ่มต้นด้วยการเพิ่มสาขาแรก' }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="worksites.data.length > 0" class="flex justify-end">
                    <Pagination :links="worksites.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
