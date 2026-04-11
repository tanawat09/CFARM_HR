<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    leaves: Object,
    stats: Object,
    leaveTypes: Array
});

const tableColumns = [
    { key: 'leave_type', label: 'ประเภทการลา' },
    { key: 'start_date', label: 'วันที่เริ่มต้น' },
    { key: 'end_date', label: 'วันที่สิ้นสุด' },
    { key: 'total_days', label: 'ปริมาณที่ลา', class: 'text-center', cellClass: 'text-center font-bold text-slate-700' },
    { key: 'status', label: 'สถานะ' }
];

const getStatusColor = (s) => {
    const v = typeof s === 'object' ? s.value : s;
    if (v === 'approved') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (v === 'rejected') return 'bg-rose-50 text-rose-700 border-rose-200';
    return 'bg-amber-50 text-amber-700 border-amber-200';
};
const getStatusDot = (s) => {
    const v = typeof s === 'object' ? s.value : s;
    if (v === 'approved') return 'bg-emerald-500';
    if (v === 'rejected') return 'bg-rose-500';
    return 'bg-amber-500';
};
const getStatusText = (s) => {
    const v = typeof s === 'object' ? s.value : s;
    if (v === 'approved') return 'อนุมัติแล้ว';
    if (v === 'rejected') return 'ไม่อนุมัติ';
    return 'รอตรวจสอบ';
};
const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d.split(' ')[0]).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getLeaveLabel = (key) => {
    const info = props.stats?.items?.[key];
    if (info) return info.icon + ' ' + info.name;
    const t = props.leaveTypes?.find(t => t.key === key);
    if (t) return t.icon + ' ' + t.name;
    return key;
};

const palettes = [
    { grad: 'from-emerald-500 to-teal-500', glow: 'shadow-emerald-200/50', text: 'text-emerald-600', bar: 'bg-emerald-500', soft: 'bg-emerald-50' },
    { grad: 'from-teal-500 to-cyan-500', glow: 'shadow-teal-200/50', text: 'text-teal-600', bar: 'bg-teal-500', soft: 'bg-teal-50' },
    { grad: 'from-amber-500 to-orange-500', glow: 'shadow-amber-200/50', text: 'text-amber-600', bar: 'bg-amber-500', soft: 'bg-amber-50' },
    { grad: 'from-rose-500 to-pink-500', glow: 'shadow-rose-200/50', text: 'text-rose-600', bar: 'bg-rose-500', soft: 'bg-rose-50' },
    { grad: 'from-sky-500 to-indigo-500', glow: 'shadow-sky-200/50', text: 'text-sky-600', bar: 'bg-sky-500', soft: 'bg-sky-50' },
    { grad: 'from-lime-500 to-emerald-500', glow: 'shadow-lime-200/50', text: 'text-lime-600', bar: 'bg-lime-500', soft: 'bg-lime-50' },
];
const paletteFor = (idx) => palettes[idx % palettes.length];
</script>

<template>
    <Head title="ระบบการลา" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200/60">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">ระบบการลา</h2>
                        <p class="text-xs text-slate-500 font-medium">ยื่นใบลา ตรวจสอบสิทธิ์ และประวัติการลา</p>
                    </div>
                </div>
                <Link :href="route('leave.create')" class="group inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl px-5 py-2.5 text-sm font-bold shadow-lg shadow-emerald-300/50 hover:shadow-emerald-400/60 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    เขียนใบลา
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Flash -->
                <transition enter-active-class="transition duration-300" enter-from-class="opacity-0 -translate-y-2">
                    <div v-if="$page.props.flash?.message" class="flex items-center gap-3 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 text-emerald-700 px-5 py-3.5 rounded-2xl text-sm font-semibold shadow-sm">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        {{ $page.props.flash.message }}
                    </div>
                </transition>

                <!-- Quota Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4" v-if="stats?.items">
                    <div v-for="(info, key, idx) in stats.items" :key="key"
                         :class="['relative bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group hover:-translate-y-1', paletteFor(idx).glow]">
                        <div :class="['absolute -right-8 -top-8 w-28 h-28 rounded-full opacity-40 group-hover:scale-110 transition-transform', paletteFor(idx).soft]"></div>
                        <div class="relative">
                            <div class="flex items-start justify-between mb-3">
                                <div :class="['w-11 h-11 rounded-2xl bg-gradient-to-br flex items-center justify-center text-white text-xl shadow-lg', paletteFor(idx).grad]">
                                    {{ info.icon }}
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      :class="info.remaining > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                    เหลือ {{ info.remaining }}
                                </span>
                            </div>
                            <h3 class="font-bold text-xs text-slate-500 uppercase tracking-wider">{{ info.name }}</h3>
                            <div class="flex items-baseline gap-1.5 mt-1">
                                <span :class="['text-3xl font-black', paletteFor(idx).text]">{{ info.used }}</span>
                                <span class="text-sm font-bold text-slate-400">/ {{ info.quota }} วัน</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 mt-3 rounded-full overflow-hidden">
                                <div :class="['h-full rounded-full transition-all duration-500', paletteFor(idx).bar]"
                                     :style="{ width: info.quota > 0 ? `${Math.min((info.used / info.quota) * 100, 100)}%` : '0%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Probation Notice -->
                <div v-if="stats?.is_probation" class="flex items-center gap-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-800 px-5 py-3.5 rounded-2xl text-sm font-semibold shadow-sm">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    คุณอยู่ในช่วงทดลองงาน — บางประเภทการลาอาจยังไม่เปิดให้ใช้สิทธิ์
                </div>

                <!-- History -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-800 flex items-center justify-center shadow">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="font-extrabold text-base text-slate-800">ประวัติการลา</h3>
                        </div>
                        <span class="text-xs text-slate-500 font-bold bg-slate-100 px-3 py-1 rounded-full">ทั้งหมด {{ leaves.total || 0 }} รายการ</span>
                    </div>

                    <DataTable :columns="tableColumns" :data="leaves">
                        <template #cell-leave_type="{ item }">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ getLeaveLabel(item.leave_type?.value || item.leave_type) }}</span>
                                <span v-if="item.leave_format === 'hourly'" class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">รายชั่วโมง</span>
                            </div>
                            <div v-if="item.reason" class="text-xs text-slate-400 mt-0.5 truncate max-w-[220px]">{{ item.reason }}</div>
                        </template>

                        <template #cell-start_date="{ item }">
                            <div class="font-semibold text-slate-700">{{ formatDate(item.start_date) }}</div>
                            <div v-if="item.leave_format === 'hourly' && item.start_time" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ item.start_time.substring(0, 5) }} น.
                            </div>
                        </template>

                        <template #cell-end_date="{ item }">
                            <div v-if="item.leave_format === 'daily'" class="font-semibold text-slate-700">{{ formatDate(item.end_date) }}</div>
                            <div v-else-if="item.leave_format === 'hourly' && item.end_time" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ item.end_time.substring(0, 5) }} น.
                            </div>
                            <div v-else>-</div>
                        </template>

                        <template #cell-status="{ item }">
                            <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border', getStatusColor(item.status)]">
                                <span :class="['w-1.5 h-1.5 rounded-full', getStatusDot(item.status)]"></span>
                                {{ getStatusText(item.status) }}
                            </span>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
