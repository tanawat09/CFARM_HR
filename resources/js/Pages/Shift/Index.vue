<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { computed } from 'vue';

const props = defineProps({ shifts: Object });

const palettes = [
    { grad: 'from-indigo-500 to-purple-500', glow: 'shadow-indigo-200/50', soft: 'bg-indigo-50 text-indigo-700' },
    { grad: 'from-sky-500 to-cyan-500', glow: 'shadow-sky-200/50', soft: 'bg-sky-50 text-sky-700' },
    { grad: 'from-emerald-500 to-teal-500', glow: 'shadow-emerald-200/50', soft: 'bg-emerald-50 text-emerald-700' },
    { grad: 'from-amber-500 to-orange-500', glow: 'shadow-amber-200/50', soft: 'bg-amber-50 text-amber-700' },
    { grad: 'from-rose-500 to-pink-500', glow: 'shadow-rose-200/50', soft: 'bg-rose-50 text-rose-700' },
    { grad: 'from-violet-500 to-fuchsia-500', glow: 'shadow-violet-200/50', soft: 'bg-violet-50 text-violet-700' },
];
const paletteFor = (id) => palettes[id % palettes.length];

const formatTime = (t) => (t || '').toString().substring(0, 5);

const dayNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
const workingDaysArray = (wd) => {
    if (!wd) return [];
    try {
        const parsed = typeof wd === 'string' ? JSON.parse(wd) : wd;
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const totalShifts = computed(() => props.shifts?.total || props.shifts?.data?.length || 0);
const activeShifts = computed(() => (props.shifts?.data || []).filter(s => s.is_active).length);
</script>

<template>
    <Head title="จัดการกะทำงาน" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-200/60">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">จัดการกะทำงาน</h2>
                        <p class="text-xs text-slate-500 font-medium">กำหนดช่วงเวลาเข้า-ออกงานและเงื่อนไขการทำงาน</p>
                    </div>
                </div>
                <Link :href="route('shifts.create')" class="group inline-flex items-center gap-2 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-violet-300/50 hover:shadow-violet-400/60 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    เพิ่มกะทำงาน
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-violet-100 to-fuchsia-100 rounded-full opacity-60"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center shadow-md shadow-violet-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">กะทั้งหมด</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ totalShifts }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full opacity-60"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md shadow-emerald-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">เปิดใช้งาน</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ activeShifts }}</div>
                        </div>
                    </div>
                    <div class="relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm overflow-hidden">
                        <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-sky-100 to-blue-100 rounded-full opacity-60"></div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center shadow-md shadow-sky-200/60 mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">หน้าปัจจุบัน</div>
                            <div class="text-3xl font-black text-slate-800 mt-1">{{ shifts?.data?.length || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Shift Cards Grid -->
                <div v-if="shifts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    <div v-for="shift in shifts.data" :key="shift.id"
                         :class="['group relative bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1', paletteFor(shift.id).glow]">
                        <div :class="['h-1.5 bg-gradient-to-r', paletteFor(shift.id).grad]"></div>
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between gap-3 mb-5">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-12 h-12 rounded-2xl bg-gradient-to-br flex items-center justify-center shadow-lg', paletteFor(shift.id).grad]">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-slate-800 text-lg leading-tight">{{ shift.name }}</h3>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ shift.code }}</p>
                                    </div>
                                </div>
                                <span v-if="shift.is_active" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full border border-emerald-100">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>เปิด
                                </span>
                                <span v-else class="inline-flex items-center gap-1 bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>ปิด
                                </span>
                            </div>

                            <!-- Time Range -->
                            <div class="relative bg-gradient-to-br from-slate-50 to-slate-100/50 border border-slate-100 rounded-2xl p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="text-center">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">เข้างาน</div>
                                        <div class="flex items-center gap-1.5 justify-center">
                                            <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .2.08.4.22.53l3.25 3.25a.75.75 0 101.06-1.06L10.75 9.69V5z" clip-rule="evenodd" /></svg>
                                            <span class="font-black text-2xl text-emerald-600 font-mono">{{ formatTime(shift.start_time) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 mx-4 relative">
                                        <div class="h-1 bg-gradient-to-r from-emerald-400 via-sky-400 to-rose-400 rounded-full"></div>
                                        <svg class="w-4 h-4 text-slate-400 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-full" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">ออกงาน</div>
                                        <div class="flex items-center gap-1.5 justify-center">
                                            <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .2.08.4.22.53l3.25 3.25a.75.75 0 101.06-1.06L10.75 9.69V5z" clip-rule="evenodd" /></svg>
                                            <span class="font-black text-2xl text-rose-600 font-mono">{{ formatTime(shift.end_time) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chips: rules -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-amber-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" /></svg>
                                    สาย > {{ shift.late_after_minutes }} นาที
                                </span>
                                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-[11px] font-bold px-2.5 py-1 rounded-lg border border-purple-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                                    พัก {{ shift.break_duration_minutes }} นาที
                                </span>
                            </div>

                            <!-- Working Days -->
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">วันทำงาน</div>
                                <div class="flex gap-1">
                                    <div v-for="(d, i) in dayNames" :key="i"
                                         :class="[workingDaysArray(shift.working_days).includes(i) ? paletteFor(shift.id).soft + ' font-black' : 'bg-slate-50 text-slate-300 font-bold', 'flex-1 text-center text-[11px] py-1.5 rounded-lg']">
                                        {{ d }}
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-5 pt-4 border-t border-dashed border-slate-100 flex justify-end">
                                <Link :href="route('shifts.edit', shift.id)" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    แก้ไข
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="bg-white rounded-2xl border border-dashed border-slate-200 py-16 text-center">
                    <div class="inline-flex w-20 h-20 rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-700 text-lg">ยังไม่มีกะทำงาน</h3>
                    <p class="text-sm text-slate-400 font-medium mt-1">สร้างกะทำงานแรกเพื่อใช้กำหนดตารางเวลาของพนักงาน</p>
                    <Link :href="route('shifts.create')" class="mt-5 inline-flex items-center gap-2 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-violet-200/60 hover:-translate-y-0.5 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        เพิ่มกะแรก
                    </Link>
                </div>

                <div v-if="shifts.data.length > 0" class="flex justify-end">
                    <Pagination :links="shifts.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
