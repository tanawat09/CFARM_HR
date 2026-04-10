<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    chartData: Array,
    recentLeaves: Array,
    todayAttendance: Array,
    departments: Array,
    policies: Object,
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'สวัสดีตอนเช้า';
    if (hour < 17) return 'สวัสดีตอนบ่าย';
    return 'สวัสดีตอนเย็น';
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('th-TH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

// Chart helpers
const getMaxChartValue = () => {
    if (!props.chartData) return 10;
    let max = 10;
    props.chartData.forEach(d => {
        const total = (d.on_time || 0) + (d.late || 0);
        if (total > max) max = total;
    });
    return max;
};
const maxVal = getMaxChartValue();

const getLeaveLabel = (key) => {
    const p = props.policies?.[key];
    return p ? `${p.icon} ${p.name}` : key;
};

const getLeaveStatusBadge = (status) => {
    const map = {
        'pending': { label: 'รออนุมัติ', class: 'bg-amber-100 text-amber-700 border-amber-200', icon: '⏳' },
        'approved': { label: 'อนุมัติ', class: 'bg-emerald-100 text-emerald-700 border-emerald-200', icon: '✅' },
        'rejected': { label: 'ไม่อนุมัติ', class: 'bg-rose-100 text-rose-700 border-rose-200', icon: '❌' },
    };
    return map[status] || { label: status, class: 'bg-slate-100 text-slate-500 border-slate-200', icon: '📄' };
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
};

const formatTime = (t) => {
    if (!t) return '-';
    // Already formatted as H:i from backend
    return t;
};

const attendanceRate = computed(() => {
    if (!props.stats?.total_employees) return 0;
    return Math.round((props.stats.checked_in_today / props.stats.total_employees) * 100);
});

const maxDeptCount = computed(() => {
    if (!props.departments?.length) return 1;
    return Math.max(...props.departments.map(d => d.count), 1);
});
</script>

<template>
    <Head title="แดชบอร์ดผู้ดูแลระบบ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">
                        {{ greeting }}, Admin 👋
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">{{ currentDate }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-sm font-medium text-slate-500 bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        ระบบกำลังทำงานปกติ
                    </div>
                    <Link :href="route('leave.approvals')" 
                        class="relative inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        อนุมัติการลา
                        <span v-if="stats.pending_leaves > 0" class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                            {{ stats.pending_leaves }}
                        </span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- KPI Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                    <!-- Total Employees -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-indigo-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">พนักงานทั้งหมด</p>
                            <p class="text-3xl font-black text-indigo-600">{{ stats.total_employees }}</p>
                            <p class="text-xs text-slate-400 mt-1">คน</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
                    </div>

                    <!-- Checked In Today -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-emerald-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">เข้างานวันนี้</p>
                            <p class="text-3xl font-black text-emerald-600">{{ stats.checked_in_today }}</p>
                            <p class="text-xs text-slate-400 mt-1">คน ({{ attendanceRate }}%)</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    </div>

                    <!-- Leaves this month -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-purple-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">ลาหยุด (เดือนนี้)</p>
                            <p class="text-3xl font-black text-purple-600">{{ stats.total_leaves_month }}</p>
                            <p class="text-xs text-slate-400 mt-1">รายการ</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-purple-400"></div>
                    </div>

                    <!-- Pending Approvals -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-rose-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">รออนุมัติ</p>
                            <p class="text-3xl font-black" :class="stats.pending_leaves > 0 ? 'text-rose-600' : 'text-slate-400'">{{ stats.pending_leaves }}</p>
                            <p class="text-xs text-slate-400 mt-1">รายการ</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-400"></div>
                    </div>
                </div>

                <!-- Section 2: Quick Actions -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-bold text-base text-slate-800 mb-4 flex items-center gap-2">⚡ เมนูจัดการด่วน</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link :href="route('employees.index')" class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/50 transition group">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">จัดการพนักงาน</span>
                        </Link>
                        <Link :href="route('worksites.index')" class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/50 transition group">
                            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">จัดการสาขา</span>
                        </Link>
                        <Link :href="route('reports.index')" class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-100 hover:border-purple-200 hover:bg-purple-50/50 transition group">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 group-hover:scale-110 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">รายงาน</span>
                        </Link>
                        <Link :href="route('settings.leave')" class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-slate-100 hover:border-amber-200 hover:bg-amber-50/50 transition group">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 group-hover:scale-110 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924-1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-slate-700">ตั้งค่าการลา</span>
                        </Link>
                    </div>
                </div>

                <!-- Section 3: Today's Attendance + Recent Leaves -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Today's Attendance -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">⏰ การเข้างานวันนี้</h3>
                                <p class="text-xs text-slate-400 mt-0.5">พนักงานที่เข้างานล่าสุด</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <!-- Attendance Rate Ring -->
                                <div class="relative w-12 h-12">
                                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 48 48">
                                        <circle cx="24" cy="24" r="20" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                        <circle cx="24" cy="24" r="20" fill="none" stroke="#10b981" stroke-width="4"
                                            stroke-linecap="round"
                                            :stroke-dasharray="`${attendanceRate * 1.257} 125.7`" />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-[10px] font-black text-emerald-600">{{ attendanceRate }}%</span>
                                    </div>
                                </div>
                                <Link :href="route('reports.index')" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition">
                                    ดูทั้งหมด →
                                </Link>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-3 font-bold">พนักงาน</th>
                                        <th class="px-6 py-3 font-bold">กะ</th>
                                        <th class="px-6 py-3 font-bold text-center">เข้า</th>
                                        <th class="px-6 py-3 font-bold text-center">ออก</th>
                                        <th class="px-6 py-3 font-bold text-center">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    <tr v-for="att in todayAttendance" :key="att.id" class="hover:bg-indigo-50/30 transition">
                                        <td class="px-6 py-3">
                                            <div>
                                                <p class="font-bold text-slate-800">{{ att.employee_name }}</p>
                                                <p class="text-xs text-slate-400">{{ att.department }}</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <div v-if="att.shift_name">
                                                <p class="text-sm font-bold text-slate-700">{{ att.shift_name }}</p>
                                                <p class="text-xs text-slate-400 font-mono">{{ att.shift_time }}</p>
                                            </div>
                                            <span v-else class="text-slate-300">-</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="font-mono font-bold" :class="att.check_in_time ? 'text-emerald-600' : 'text-slate-300'">{{ att.check_in_time || '-' }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="font-mono font-bold" :class="att.check_out_time ? 'text-sky-600' : 'text-slate-300'">{{ att.check_out_time || '-' }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span v-if="att.is_late" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 border border-amber-200">
                                                มาสาย
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                ปกติ
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!todayAttendance?.length">
                                        <td colspan="5" class="py-10 text-center">
                                            <div class="text-3xl mb-2">📭</div>
                                            <p class="font-bold text-slate-400 text-sm">ยังไม่มีการลงเวลาวันนี้</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                            <Link :href="route('reports.index')"
                                class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                ดูข้อมูลเข้างานรายวัน
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Leave Requests -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">📋 คำขอลาล่าสุด</h3>
                                <p class="text-xs text-slate-400 mt-0.5">รายการล่าสุดจากพนักงาน</p>
                            </div>
                            <Link :href="route('leave.approvals')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                ดูทั้งหมด →
                            </Link>
                        </div>

                        <div class="flex-1 divide-y divide-slate-100">
                            <div v-for="leave in recentLeaves" :key="leave.id" class="px-6 py-4 hover:bg-indigo-50/30 transition">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ leave.employee_name }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ getLeaveLabel(leave.leave_type) }}</p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
                                            <span class="font-bold text-indigo-600 ml-1">({{ leave.total_days }} วัน)</span>
                                        </p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full border"
                                        :class="getLeaveStatusBadge(leave.status).class">
                                        {{ getLeaveStatusBadge(leave.status).icon }} {{ getLeaveStatusBadge(leave.status).label }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="!recentLeaves?.length" class="px-6 py-10 text-center">
                                <div class="text-3xl mb-2">✨</div>
                                <p class="font-bold text-slate-400 text-sm">ไม่มีคำขอลา</p>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                            <Link :href="route('leave.approvals')"
                                class="flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                จัดการคำขอลาทั้งหมด
                                <span v-if="stats.pending_leaves > 0" class="bg-white/20 px-2 py-0.5 rounded-full text-xs">{{ stats.pending_leaves }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Chart + Dept Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- 7-Day Attendance Chart -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 pt-6 pb-6">
                            <div class="flex justify-between items-end mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800 mb-1">📊 แนวโน้มการเข้างาน 7 วันล่าสุด</h3>
                                    <div class="flex items-center gap-4 text-xs font-medium">
                                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div> มาปกติ</div>
                                        <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-rose-400"></div> มาสาย</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bar Chart -->
                            <div class="h-52 relative flex items-end justify-between gap-2 px-1">
                                <!-- Grid lines -->
                                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                                    <div class="w-full h-px bg-slate-100"></div>
                                    <div class="w-full h-px bg-slate-100"></div>
                                    <div class="w-full h-px bg-slate-100"></div>
                                    <div class="w-full h-px bg-slate-100"></div>
                                    <div class="w-full h-px bg-slate-200/50"></div>
                                </div>
                                
                                <div v-for="item in chartData" :key="item.date" class="relative flex flex-col justify-end items-center group flex-1" style="height: 100%;">
                                    <div class="w-full max-w-[40px] flex flex-col justify-end mx-auto" style="height: calc(100% - 28px); position: relative; z-index: 10;">
                                        <!-- Tooltip -->
                                        <div class="absolute -top-14 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1.5 px-3 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all whitespace-nowrap z-20 pointer-events-none">
                                            <div class="font-bold border-b border-white/20 mb-1 pb-1">{{ item.date }}</div>
                                            <div class="text-emerald-300">ปกติ: {{ item.on_time || 0 }}</div>
                                            <div class="text-rose-300">สาย: {{ item.late || 0 }}</div>
                                        </div>
                                        <div class="w-full rounded-t-md bar-late transition-all duration-700 ease-out hover:brightness-110" 
                                            :style="{ height: `${((item.late || 0) / maxVal) * 100}%` }">
                                        </div>
                                        <div class="w-full rounded-b-md bar-ontime transition-all duration-700 ease-out hover:brightness-110" 
                                            :style="{ height: `${((item.on_time || 0) / maxVal) * 100}%` }">
                                        </div>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400 mt-2 h-5">{{ item.label }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Breakdown -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100">
                            <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">🏢 สัดส่วนพนักงานตามแผนก</h3>
                            <p class="text-xs text-slate-400 mt-0.5">จำนวนพนักงานในแต่ละแผนก</p>
                        </div>
                        <div class="flex-1 p-6 space-y-4">
                            <div v-for="dept in departments" :key="dept.name" class="group">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-bold text-slate-700">{{ dept.name }}</span>
                                    <span class="text-sm font-black text-indigo-600">{{ dept.count }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-indigo-400 transition-all duration-700 ease-out group-hover:brightness-110"
                                        :style="{ width: `${(dept.count / maxDeptCount) * 100}%` }">
                                    </div>
                                </div>
                            </div>
                            <div v-if="!departments?.length" class="py-8 text-center">
                                <div class="text-3xl mb-2">🏢</div>
                                <p class="font-bold text-slate-400 text-sm">ยังไม่มีข้อมูลแผนก</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                            <Link :href="route('departments.index')"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                                จัดการแผนก →
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.bar-ontime {
    background: linear-gradient(180deg, #34d399 0%, #10b981 100%);
    animation: growBar 1s cubic-bezier(0.1, 0.8, 0.2, 1);
    transform-origin: bottom;
}
.bar-late {
    background: linear-gradient(180deg, #fb7185 0%, #f43f5e 100%);
    animation: growBar 1s cubic-bezier(0.1, 0.8, 0.2, 1);
    transform-origin: bottom;
}

@keyframes growBar {
    from { transform: scaleY(0); opacity: 0; }
    to { transform: scaleY(1); opacity: 1; }
}
</style>
