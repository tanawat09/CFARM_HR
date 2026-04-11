<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({ departments: Array });

const showModal = ref(false);
const editing = ref(null);
const confirmDelete = ref(null);
const search = ref('');
const statusFilter = ref('all');

const form = useForm({ name: '', code: '', is_active: true });

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.departments.filter(d => {
        const matchQ = !q || d.name.toLowerCase().includes(q) || (d.code || '').toLowerCase().includes(q);
        const matchS = statusFilter.value === 'all'
            || (statusFilter.value === 'active' && d.is_active)
            || (statusFilter.value === 'inactive' && !d.is_active);
        return matchQ && matchS;
    });
});

const totalCount = computed(() => props.departments.length);
const activeCount = computed(() => props.departments.filter(d => d.is_active).length);
const inactiveCount = computed(() => totalCount.value - activeCount.value);
const totalEmployees = computed(() => props.departments.reduce((s, d) => s + (d.employees_count || 0), 0));

const palettes = [
    { grad: 'from-indigo-500 to-purple-500', ring: 'ring-indigo-100', chip: 'bg-indigo-50 text-indigo-700', glow: 'shadow-indigo-200/50' },
    { grad: 'from-sky-500 to-cyan-500', ring: 'ring-sky-100', chip: 'bg-sky-50 text-sky-700', glow: 'shadow-sky-200/50' },
    { grad: 'from-emerald-500 to-teal-500', ring: 'ring-emerald-100', chip: 'bg-emerald-50 text-emerald-700', glow: 'shadow-emerald-200/50' },
    { grad: 'from-amber-500 to-orange-500', ring: 'ring-amber-100', chip: 'bg-amber-50 text-amber-700', glow: 'shadow-amber-200/50' },
    { grad: 'from-rose-500 to-pink-500', ring: 'ring-rose-100', chip: 'bg-rose-50 text-rose-700', glow: 'shadow-rose-200/50' },
    { grad: 'from-violet-500 to-fuchsia-500', ring: 'ring-violet-100', chip: 'bg-violet-50 text-violet-700', glow: 'shadow-violet-200/50' },
];
const paletteFor = (id) => palettes[id % palettes.length];
const initial = (name) => (name || '?').trim().charAt(0).toUpperCase();

const openCreate = () => { editing.value = null; form.reset(); form.is_active = true; form.clearErrors(); showModal.value = true; };
const openEdit = (d) => { editing.value = d; form.name = d.name; form.code = d.code || ''; form.is_active = d.is_active; form.clearErrors(); showModal.value = true; };
const submitForm = () => {
    if (editing.value) {
        form.put(route('departments.update', editing.value.id), { onSuccess: () => showModal.value = false });
    } else {
        form.post(route('departments.store'), { onSuccess: () => showModal.value = false });
    }
};
const del = (d) => router.delete(route('departments.destroy', d.id), { onSuccess: () => confirmDelete.value = null });
</script>

<template>
    <Head title="จัดการแผนก" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-200/60">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">จัดการแผนก</h2>
                        <p class="text-xs text-slate-500 font-medium">กำหนดและปรับปรุงโครงสร้างแผนกขององค์กร</p>
                    </div>
                </div>
                <button @click="openCreate" class="group inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-sky-300/50 hover:shadow-sky-400/60 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                    เพิ่มแผนก
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Flash messages -->
                <transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-2">
                    <div v-if="$page.props.flash?.message" class="flex items-center gap-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-2xl text-sm font-semibold shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        {{ $page.props.flash.message }}
                    </div>
                </transition>
                <div v-if="$page.props.errors?.delete" class="flex items-center gap-3 bg-gradient-to-r from-rose-50 to-pink-50 border border-rose-200 text-rose-700 px-5 py-3.5 rounded-2xl text-sm font-semibold shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-rose-500 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    {{ $page.props.errors.delete }}
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-sky-100 to-indigo-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 flex items-center justify-center shadow-md shadow-sky-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">แผนกทั้งหมด</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalCount }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md shadow-emerald-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">เปิดใช้งาน</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ activeCount }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-rose-100 to-pink-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center shadow-md shadow-rose-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">ปิดใช้งาน</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ inactiveCount }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full opacity-60 group-hover:scale-110 transition-transform"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md shadow-amber-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">พนักงานรวม</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalEmployees }}</div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-[220px]">
                        <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input v-model="search" type="text" placeholder="ค้นหาแผนกหรือรหัส..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 text-sm font-medium bg-slate-50/60" />
                    </div>
                    <div class="inline-flex bg-slate-100 rounded-xl p-1 text-xs font-bold">
                        <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-white text-sky-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg transition">ทั้งหมด</button>
                        <button @click="statusFilter = 'active'" :class="statusFilter === 'active' ? 'bg-white text-emerald-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg transition">เปิด</button>
                        <button @click="statusFilter = 'inactive'" :class="statusFilter === 'inactive' ? 'bg-white text-rose-700 shadow' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 rounded-lg transition">ปิด</button>
                    </div>
                </div>

                <!-- Table View -->
                <div v-if="filtered.length > 0" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
                                    <th class="px-6 py-4">แผนก</th>
                                    <th class="px-6 py-4 text-center">รหัส</th>
                                    <th class="px-6 py-4 text-center">สถานะ</th>
                                    <th class="px-6 py-4 text-center">จำนวนพนักงาน</th>
                                    <th class="px-6 py-4 text-right">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="d in filtered" :key="d.id" class="hover:bg-slate-50/50 transition duration-150 group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-white font-black shrink-0 shadow-sm', paletteFor(d.id).grad, paletteFor(d.id).ring]">
                                                {{ initial(d.name) }}
                                            </div>
                                            <div class="font-extrabold text-slate-800 text-sm whitespace-nowrap">{{ d.name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="d.code" class="inline-flex items-center bg-slate-100 text-slate-600 text-xs font-bold px-2.5 py-1 rounded-md font-mono shadow-sm border border-slate-200/60">{{ d.code }}</span>
                                        <span v-else class="text-slate-300 text-xs">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="d.is_active" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-100">
                                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                            เปิดใช้งาน
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1.5 bg-slate-50 text-slate-500 text-xs font-bold px-3 py-1 rounded-full border border-slate-200">
                                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                            ปิดใช้งาน
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-600 font-bold text-sm">
                                        {{ d.employees_count }} คน
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 transition-opacity">
                                            <template v-if="confirmDelete === d.id">
                                                <button @click="del(d)" class="text-white bg-rose-600 hover:bg-rose-700 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm whitespace-nowrap transition-colors">ยืนยันลบ</button>
                                                <button @click="confirmDelete = null" class="text-slate-400 hover:text-slate-600 text-xs font-bold px-2 py-1.5 whitespace-nowrap transition-colors">ยกเลิก</button>
                                            </template>
                                            <template v-else>
                                                <button @click="openEdit(d)" title="แก้ไข" class="w-8 h-8 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-600 flex items-center justify-center transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </button>
                                                <button @click="confirmDelete = d.id" title="ลบ" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="bg-white rounded-2xl border border-dashed border-slate-200 py-16 text-center">
                    <div class="inline-flex w-20 h-20 rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="font-extrabold text-slate-700 text-lg">{{ search || statusFilter !== 'all' ? 'ไม่พบแผนกที่ค้นหา' : 'ยังไม่มีแผนก' }}</h3>
                    <p class="text-sm text-slate-400 font-medium mt-1">{{ search || statusFilter !== 'all' ? 'ลองเปลี่ยนคำค้นหาหรือตัวกรอง' : 'เริ่มต้นด้วยการเพิ่มแผนกแรกของคุณ' }}</p>
                    <button v-if="!search && statusFilter === 'all'" @click="openCreate" class="mt-5 inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-indigo-600 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-sky-200/60 hover:-translate-y-0.5 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        เพิ่มแผนกแรก
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <transition enter-active-class="transition duration-200" enter-from-class="opacity-0" leave-active-class="transition duration-150" leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
                    <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95 translate-y-4">
                        <div v-if="showModal" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                            <div class="relative bg-gradient-to-br from-sky-600 via-indigo-600 to-purple-600 p-6 text-white overflow-hidden">
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full"></div>
                                <div class="absolute -left-6 -bottom-8 w-28 h-28 bg-white/10 rounded-full"></div>
                                <div class="relative flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center ring-1 ring-white/30">
                                            <svg v-if="editing" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                        </div>
                                        <div>
                                            <h3 class="font-extrabold text-lg leading-tight">{{ editing ? 'แก้ไขแผนก' : 'เพิ่มแผนก' }}</h3>
                                            <p class="text-xs text-white/80 font-medium">{{ editing ? 'อัพเดทข้อมูลแผนก' : 'สร้างแผนกใหม่' }}</p>
                                        </div>
                                    </div>
                                    <button @click="showModal = false" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>

                            <form @submit.prevent="submitForm" class="p-6 space-y-5">
                                <div>
                                    <label class="block font-bold text-xs text-slate-500 uppercase tracking-wider mb-2">ชื่อแผนก <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                                        <input type="text" v-model="form.name" class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 text-sm font-semibold" placeholder="เช่น ทรัพยากรบุคคล" required>
                                    </div>
                                    <InputError :message="form.errors.name" class="mt-1.5" />
                                </div>

                                <div>
                                    <label class="block font-bold text-xs text-slate-500 uppercase tracking-wider mb-2">รหัสแผนก</label>
                                    <div class="relative">
                                        <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                        <input type="text" v-model="form.code" class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 text-sm font-mono font-bold" placeholder="HR, IT, ...">
                                    </div>
                                </div>

                                <label class="flex items-center justify-between gap-3 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl cursor-pointer hover:shadow-sm transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm">
                                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-800 text-sm">เปิดใช้งาน</div>
                                            <div class="text-[11px] text-slate-500 font-medium">แผนกนี้จะแสดงในรายการเลือก</div>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                                        <div class="w-12 h-7 bg-slate-300 rounded-full peer-checked:bg-gradient-to-r peer-checked:from-emerald-500 peer-checked:to-teal-500 transition"></div>
                                        <div class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                    </div>
                                </label>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="showModal = false" class="text-slate-600 hover:text-slate-800 font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-slate-100 transition">ยกเลิก</button>
                                    <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-lg shadow-sky-200/60 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        {{ form.processing ? 'กำลังบันทึก...' : (editing ? 'บันทึก' : 'เพิ่ม') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </transition>
                </div>
            </transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
