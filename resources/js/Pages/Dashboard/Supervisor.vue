<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    employee: Object,
    teamMembers: Array,
    pendingLeaves: Number,
    recentLeaves: Array,
    policies: Object,
    myAttendance: Object,
    stats: Object,
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

const getStatusBadge = (status) => {
    const map = {
        'checked_in': { label: 'เข้างานแล้ว', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
        'checked_out': { label: 'ออกงานแล้ว', class: 'bg-sky-100 text-sky-700 border-sky-200' },
        'absent': { label: 'ยังไม่เข้างาน', class: 'bg-slate-100 text-slate-500 border-slate-200' },
    };
    return map[status] || { label: status, class: 'bg-slate-100 text-slate-500 border-slate-200' };
};

const getLeaveStatusBadge = (status) => {
    const map = {
        'pending': { label: 'รออนุมัติ', class: 'bg-amber-100 text-amber-700 border-amber-200', icon: '⏳' },
        'approved': { label: 'อนุมัติ', class: 'bg-emerald-100 text-emerald-700 border-emerald-200', icon: '✅' },
        'rejected': { label: 'ไม่อนุมัติ', class: 'bg-rose-100 text-rose-700 border-rose-200', icon: '❌' },
    };
    return map[status] || { label: status, class: 'bg-slate-100 text-slate-500 border-slate-200', icon: '📄' };
};

const getLeaveLabel = (key) => {
    const p = props.policies?.[key];
    return p ? `${p.icon} ${p.name}` : key;
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
};

const formatTime = (t) => {
    if (!t) return '-';
    return t;
};

const attendanceRate = computed(() => {
    if (!props.stats?.total_team) return 0;
    return Math.round((props.stats.checked_in_today / props.stats.total_team) * 100);
});
</script>

<template>
    <Head title="แดชบอร์ดหัวหน้างาน" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">
                        {{ greeting }}, {{ employee?.first_name }} 👋
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">{{ currentDate }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('leave.approvals')" 
                        class="relative inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        อนุมัติการลา
                        <span v-if="pendingLeaves > 0" class="absolute -top-2 -right-2 bg-rose-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                            {{ pendingLeaves }}
                        </span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
                    <!-- Total Team -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-indigo-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">ลูกทีมทั้งหมด</p>
                            <p class="text-3xl font-black text-indigo-600">{{ stats.total_team }}</p>
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
                            <p class="text-xs text-slate-400 mt-1">จาก {{ stats.total_team }} คน</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    </div>


                    <!-- Pending Approvals -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-3 -bottom-3 text-rose-100 opacity-50 group-hover:opacity-80 transition">
                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">รออนุมัติ</p>
                            <p class="text-3xl font-black" :class="pendingLeaves > 0 ? 'text-rose-600' : 'text-slate-400'">{{ pendingLeaves }}</p>
                            <p class="text-xs text-slate-400 mt-1">รายการ</p>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-400"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Team Attendance Table (2/3 width) -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                                    👥 สถานะทีมวันนี้
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">ภาพรวมการเข้างานของลูกทีม</p>
                            </div>
                            <!-- Attendance Rate Ring -->
                            <div class="flex items-center gap-3">
                                <div class="relative w-14 h-14">
                                    <svg class="w-14 h-14 -rotate-90" viewBox="0 0 56 56">
                                        <circle cx="28" cy="28" r="24" fill="none" stroke="#e2e8f0" stroke-width="4" />
                                        <circle cx="28" cy="28" r="24" fill="none" stroke="#10b981" stroke-width="4"
                                            stroke-linecap="round"
                                            :stroke-dasharray="`${attendanceRate * 1.508} 150.8`" />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-xs font-black text-emerald-600">{{ attendanceRate }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-3 font-bold">พนักงาน</th>
                                        <th class="px-6 py-3 font-bold">ตำแหน่ง</th>
                                        <th class="px-6 py-3 font-bold">กะ</th>
                                        <th class="px-6 py-3 font-bold text-center">สถานะ</th>
                                        <th class="px-6 py-3 font-bold text-center">เข้า</th>
                                        <th class="px-6 py-3 font-bold text-center">ออก</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="member in teamMembers" :key="member.id" class="hover:bg-indigo-50/30 transition group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0"
                                                    :class="member.today_status === 'absent' 
                                                        ? 'bg-slate-100 text-slate-400 border border-slate-200' 
                                                        : member.is_late 
                                                            ? 'bg-amber-100 text-amber-600 border border-amber-200' 
                                                            : 'bg-emerald-100 text-emerald-600 border border-emerald-200'">
                                                    {{ member.first_name?.charAt(0) }}{{ member.last_name?.charAt(0) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800">{{ member.first_name }} {{ member.last_name }}</p>
                                                    <p class="text-xs text-slate-400">{{ member.department }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-slate-600">{{ member.position || '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div v-if="member.shift_name">
                                                <p class="text-sm font-bold text-slate-700">{{ member.shift_name }}</p>
                                                <p class="text-xs text-slate-400 font-mono">{{ member.shift_time }}</p>
                                            </div>
                                            <span v-else class="text-slate-300">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full border"
                                                :class="getStatusBadge(member.today_status).class">
                                                <span v-if="member.is_late" class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ member.is_late ? 'มาสาย' : getStatusBadge(member.today_status).label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-mono" :class="member.check_in_time ? 'text-emerald-600 font-bold' : 'text-slate-300'">
                                                {{ formatTime(member.check_in_time) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-mono" :class="member.check_out_time ? 'text-sky-600 font-bold' : 'text-slate-300'">
                                                {{ formatTime(member.check_out_time) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!teamMembers || teamMembers.length === 0">
                                        <td colspan="6" class="py-12 text-center">
                                            <div class="text-4xl mb-2">📭</div>
                                            <p class="font-bold text-slate-400">ยังไม่มีลูกทีม</p>
                                            <p class="text-xs text-slate-400 mt-1">ลูกทีมจะปรากฏเมื่อมีการตั้งค่าผู้ใต้บังคับบัญชา</p>
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

                    <!-- Right Sidebar: Recent Leaves -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-base text-slate-800 flex items-center gap-2">
                                    📋 คำขอลาล่าสุด
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">รายการล่าสุดของลูกทีม</p>
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

                            <div v-if="!recentLeaves || recentLeaves.length === 0" class="px-6 py-12 text-center">
                                <div class="text-3xl mb-2">✨</div>
                                <p class="font-bold text-slate-400 text-sm">ไม่มีคำขอลา</p>
                                <p class="text-xs text-slate-400 mt-1">ยังไม่มีลูกทีมส่งคำขอลา</p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                            <Link :href="route('leave.approvals')"
                                class="flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                จัดการคำขอลาทั้งหมด
                                <span v-if="pendingLeaves > 0" class="bg-white/20 px-2 py-0.5 rounded-full text-xs">{{ pendingLeaves }}</span>
                            </Link>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
