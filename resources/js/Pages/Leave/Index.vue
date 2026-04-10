<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    leaves: Object,
    stats: Object,      // { items: { key: { name, icon, quota, used, remaining, ... } }, is_probation }
    leaveTypes: Array    // [{ key, name, icon }]
});

const tableColumns = [
    { key: 'leave_type', label: 'ประเภทการลา', class: 'rounded-tl-2xl' },
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

// Find leave label from leaveTypes or stats
const getLeaveLabel = (key) => {
    const info = props.stats?.items?.[key];
    if (info) return info.icon + ' ' + info.name;
    const t = props.leaveTypes?.find(t => t.key === key);
    if (t) return t.icon + ' ' + t.name;
    return key;
};

// Color palette for progress bars
const barColors = ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-pink-500','bg-teal-500','bg-violet-500'];
const textColors = ['text-indigo-600','text-emerald-600','text-amber-600','text-pink-600','text-teal-600','text-violet-600'];
const borderColors = ['border-indigo-500','border-emerald-500','border-amber-500','border-pink-500','border-teal-500','border-violet-500'];
</script>

<template>
    <Head title="ระบบการลา" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">🏖️ ระบบการลา</h2>
                <Link :href="route('leave.create')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    เขียนใบลา
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Flash -->
                <div v-if="$page.props.flash?.message" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold">
                    ✅ {{ $page.props.flash.message }}
                </div>

                <!-- Dynamic Quota Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8" v-if="stats?.items">
                    <div v-for="(info, key, idx) in stats.items" :key="key"
                         class="bg-white rounded-2xl p-5 border shadow-sm relative overflow-hidden"
                         :class="borderColors[idx % borderColors.length]"
                         style="border-top-width: 0; border-left-width: 0; border-right-width: 0; border-bottom-width: 4px;">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl">{{ info.icon }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                  :class="info.remaining > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                เหลือ {{ info.remaining }} วัน
                            </span>
                        </div>
                        <h3 class="font-bold text-sm text-slate-700 mb-2">{{ info.name }}</h3>
                        <div class="flex items-end gap-1.5">
                            <span class="text-3xl font-black" :class="textColors[idx % textColors.length]">{{ info.used }}</span>
                            <span class="text-sm font-bold text-slate-400 mb-1">/ {{ info.quota }} วัน</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-100 h-1.5 mt-3 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 :class="barColors[idx % barColors.length]"
                                 :style="{ width: info.quota > 0 ? `${Math.min((info.used / info.quota) * 100, 100)}%` : '0%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Probation Notice -->
                <div v-if="stats?.is_probation" class="mb-6 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold">
                    🔒 คุณอยู่ในช่วงทดลองงาน — บางประเภทการลาอาจยังไม่เปิดให้ใช้สิทธิ์
                </div>

                <!-- History Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                            📜 ประวัติการลา
                        </h3>
                        <span class="text-xs text-slate-400 font-semibold">ทั้งหมด {{ leaves.total || 0 }} รายการ</span>
                    </div>

                    <DataTable :columns="tableColumns" :data="leaves">
                        <template #cell-leave_type="{ item }">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-800">{{ getLeaveLabel(item.leave_type?.value || item.leave_type) }}</span>
                                <span v-if="item.leave_format === 'hourly'" class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">รายชั่วโมง</span>
                            </div>
                            <div v-if="item.reason" class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ item.reason }}</div>
                        </template>

                        <template #cell-start_date="{ item }">
                            <div>{{ formatDate(item.start_date) }}</div>
                            <div v-if="item.leave_format === 'hourly' && item.start_time" class="text-xs font-bold text-indigo-600">
                                🕒 {{ item.start_time.substring(0, 5) }} น.
                            </div>
                        </template>

                        <template #cell-end_date="{ item }">
                            <div v-if="item.leave_format === 'daily'">{{ formatDate(item.end_date) }}</div>
                            <div v-else-if="item.leave_format === 'hourly' && item.end_time" class="text-xs font-bold text-indigo-600">
                                🕒 {{ item.end_time.substring(0, 5) }} น.
                            </div>
                            <div v-else>-</div>
                        </template>

                        <template #cell-status="{ item }">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusColor(item.status)">
                                {{ getStatusText(item.status) }}
                            </span>
                        </template>
                    </DataTable>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
