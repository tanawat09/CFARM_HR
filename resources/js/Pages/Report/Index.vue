<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    stats: Object,
    chartData: Array,
    attendances: Object,
    departments: Array,
    filters: Object
});

const selectedMonth = ref(props.filters.month);
const selectedDepartment = ref(props.filters.department_id || '');
const searchQuery = ref(props.filters.search || '');

const updateFilters = debounce(() => {
    router.get(route('reports.index'), { 
        month: selectedMonth.value,
        department_id: selectedDepartment.value,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([selectedMonth, selectedDepartment, searchQuery], () => {
    updateFilters();
});

const exportExcel = () => {
    window.location.href = route('reports.export', { 
        month: selectedMonth.value,
        department_id: selectedDepartment.value,
        search: searchQuery.value
    });
};

const getMaxChartValue = () => {
    if (!props.chartData || props.chartData.length === 0) return 10;
    let max = 10;
    props.chartData.forEach(d => {
        const total = (d.on_time || 0) + (d.late || 0);
        if (total > max) max = total;
    });
    return max;
};

const maxVal = getMaxChartValue();

const formatThaiDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getStatusBadge = (status) => {
    const map = {
        'checked_in': { label: 'เข้างานแล้ว', class: 'bg-emerald-100/80 text-emerald-700 border-emerald-200/50 backdrop-blur-sm' },
        'checked_out': { label: 'ออกงานแล้ว', class: 'bg-sky-100/80 text-sky-700 border-sky-200/50 backdrop-blur-sm' },
        'absent': { label: 'ขาดงาน', class: 'bg-rose-100/80 text-rose-700 border-rose-200/50 backdrop-blur-sm' },
        'leave': { label: 'ลาหยุด', class: 'bg-purple-100/80 text-purple-700 border-purple-200/50 backdrop-blur-sm' },
        'holiday': { label: 'วันหยุด', class: 'bg-slate-100/80 text-slate-700 border-slate-200/50 backdrop-blur-sm' },
    };
    return map[status] || { label: status, class: 'bg-slate-100/80 text-slate-500 border-slate-200/50 backdrop-blur-sm' };
};
</script>

<template>
    <Head title="รายงานสรุปการเข้างาน" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex flex-col gap-3">
                    <h2 class="font-black text-3xl text-slate-800 leading-tight tracking-tight flex items-center gap-3">
                        <span class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </span>
                        สถิติการเข้างานรายเดือน
                    </h2>
                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl w-max shadow-inner">
                        <Link :href="route('reports.index')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-black rounded-xl transition-all bg-white text-indigo-700 shadow-md ring-1 ring-slate-900/5">
                            <span class="text-lg">🕒</span> เวลาและการเข้างาน
                        </Link>
                        <Link :href="route('reports.leaves')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold rounded-xl transition-all text-slate-500 hover:text-slate-700 hover:bg-slate-200/50">
                            <span class="text-lg">🏖️</span> สถิติการลางาน
                        </Link>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <!-- Filters Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full md:w-auto">
                        <div class="relative group">
                            <input type="month" v-model="selectedMonth" class="w-full md:w-44 pl-4 pr-10 py-3 bg-white/80 border-slate-200 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all backdrop-blur-md" />
                        </div>
                        <div class="relative">
                            <select v-model="selectedDepartment" class="w-full md:w-48 pl-4 pr-10 py-3 bg-white/80 border-slate-200 rounded-2xl text-sm font-bold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all backdrop-blur-md appearance-none">
                                <option value="">🏢 ทุกแผนก</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" v-model="searchQuery" placeholder="ชื่อพนักงาน..." class="w-full md:w-56 pl-10 pr-4 py-3 bg-white/80 border-slate-200 rounded-2xl text-sm font-medium text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all backdrop-blur-md" />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="px-4 sm:px-6 lg:px-8 space-y-10">
                
                <!-- KPI Widgets grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Employees -->
                    <div class="glass-card p-6 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Target Group</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 mb-1">พนักงานในกลุ่มเป้าหมาย</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-slate-800">{{ stats.total_employees }}</span>
                            <span class="text-sm font-bold text-slate-400">คน</span>
                        </div>
                    </div>
                    
                    <!-- Attendance Rate -->
                    <div class="glass-card p-6 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Stability</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 mb-1">อัตราการเข้างานเฉลี่ย</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-emerald-600">{{ stats.avg_attendance_rate }}<span class="text-2xl">%</span></span>
                        </div>
                    </div>

                    <!-- Lates -->
                    <div class="glass-card p-6 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-amber-50 rounded-xl text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-500">Warning</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 mb-1">จำนวนการมาสายรวม</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-amber-500">{{ stats.total_lates }}</span>
                            <span class="text-sm font-bold text-slate-400">ครั้ง</span>
                        </div>
                    </div>

                    <!-- Leaves -->
                    <div class="glass-card p-6 group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-purple-50 rounded-xl text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-purple-500">Personal</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-500 mb-1">คำขออนุมัติลาทั้งหมด</h4>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-black text-purple-600">{{ stats.total_leaves }}</span>
                            <span class="text-sm font-bold text-slate-400">รายการ</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Monthly Activity Chart Section -->
                    <div class="lg:col-span-2 glass-card p-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
                            <div>
                                <h3 class="text-xl font-black text-slate-800 mb-1 uppercase tracking-tight">📊 สถิติการเข้างานรายวัน</h3>
                                <p class="text-sm text-slate-400 font-medium italic">ข้อมูลจริงจากฐานข้อมูลระบบลงเวลา</p>
                            </div>
                            <div class="flex items-center gap-6 text-[11px] font-black tracking-widest uppercase">
                                <div class="flex items-center gap-2"><div class="w-3.5 h-3.5 rounded-md bg-emerald-500 shadow-lg shadow-emerald-200"></div> มาตรงเวลา</div>
                                <div class="flex items-center gap-2"><div class="w-3.5 h-3.5 rounded-md bg-amber-400 shadow-lg shadow-amber-200"></div> มาสาย</div>
                            </div>
                        </div>

                        <!-- Custom CSS Bar Chart for Month -->
                        <div class="h-72 relative flex items-end overflow-hidden pb-8 px-2">
                            <!-- Y-Axis Grid Lines -->
                            <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-between pb-8 pointer-events-none">
                                <div v-for="i in 4" :key="i" class="w-full h-px bg-slate-100/60"></div>
                                <div class="w-full h-px bg-slate-200"></div>
                            </div>
                            
                            <!-- Bars Container -->
                            <div class="flex-1 flex justify-between items-end gap-1 h-full relative z-10 overflow-x-auto custom-scrollbar pr-2 pb-2">
                                <div v-for="item in chartData" :key="item.date" class="relative flex flex-col justify-end items-center group w-full min-w-[14px] max-w-[32px] shrink-0" style="height: 100%;">
                                    
                                    <!-- Tooltip (Hover) -->
                                    <div class="absolute -top-16 left-1/2 transform -translate-x-1/2 bg-slate-900/95 text-white text-[10px] py-2 px-3 rounded-xl shadow-2xl opacity-0 group-hover:opacity-100 group-hover:-translate-y-2 transition-all backdrop-blur-md z-30 pointer-events-none border border-white/10">
                                        <div class="font-black text-indigo-300 border-b border-white/10 mb-1.5 pb-1 flex justify-between gap-4 italic">{{ formatThaiDate(item.date).split(' ')[0] }} {{ formatThaiDate(item.date).split(' ')[1] }}</div>
                                        <div class="flex justify-between gap-4 font-bold"><span class="text-emerald-400">ปกติ:</span> {{ item.on_time }}</div>
                                        <div class="flex justify-between gap-4 font-bold"><span class="text-amber-400">สาย:</span> {{ item.late }}</div>
                                    </div>
                                    
                                    <!-- Bars -->
                                    <div class="w-full flex flex-col justify-end px-0.5 h-full relative">
                                        <!-- Late Bar -->
                                        <div class="w-full bg-gradient-to-t from-amber-500 to-amber-300 rounded-t-sm transition-all duration-700 ease-out hover:brightness-110 shadow-sm" 
                                            :style="{ height: `${((item.late || 0) / maxVal) * 100}%` }">
                                        </div>
                                        <!-- On Time Bar -->
                                        <div class="w-full bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-b-sm transition-all duration-700 ease-out hover:brightness-110 border-t border-white/20" 
                                            :style="{ height: `${((item.on_time || 0) / maxVal) * 100}%` }">
                                        </div>
                                    </div>
                                    
                                    <!-- X-Axis Label -->
                                    <div class="text-[9px] font-black text-slate-400 mt-2 h-4 text-center">{{ item.label }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Navigation -->
                    <div class="glass-card flex flex-col">
                        <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-indigo-50/30 rounded-t-3xl">
                            <h3 class="font-black text-lg text-slate-800 uppercase tracking-tight">🚀 เครื่องมือจัดการ</h3>
                            <span class="text-2xl">⚡</span>
                        </div>
                        <div class="p-8 flex-1 flex flex-col gap-4">
                            <button @click="exportExcel" class="w-full inline-flex items-center justify-center gap-3 bg-gradient-to-r from-emerald-600 to-teal-500 rounded-2xl px-6 py-4 text-sm font-black text-white hover:from-emerald-700 hover:to-teal-600 transition shadow-xl shadow-emerald-200/50 transform hover:-translate-y-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                                ดาวน์โหลดรายงาน (CSV)
                            </button>

                            <Link :href="route('reports.leaves')" class="w-full inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-indigo-200 transition group transform hover:-translate-y-1">
                                <span class="text-xl group-hover:scale-125 transition">🏖️</span>
                                ดูสถิติการลางาน
                            </Link>

                            <div class="mt-auto pt-6 border-t border-slate-100">
                                <div class="bg-indigo-600 rounded-2xl p-5 text-white shadow-xl shadow-indigo-200 relative overflow-hidden group">
                                    <div class="absolute -right-4 -bottom-4 text-white/10 w-24 h-24 group-hover:scale-110 transition duration-500">
                                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1">System Status</p>
                                    <h5 class="text-sm font-black mb-3 italic">ซิงค์ข้อมูลล่าสุดอัตโนมัติ</h5>
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                        <span class="text-xs font-bold text-indigo-100">Connected to Database</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable Section -->
                <div class="glass-card overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-50/10">
                        <h3 class="text-lg font-black text-slate-800 flex items-center gap-3 uppercase tracking-tight italic">
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold font-sans not-italic">ATT</span>
                            ประวัติการลงเวลาทำงานละเอียด
                        </h3>
                        <div class="text-xs font-bold text-slate-400 bg-white px-4 py-2 rounded-xl border border-slate-100 shadow-sm italic">
                            พบข้อมูลทั้งหมด {{ attendances.total }} รายการ ในเงื่อนไขปัจจุบัน
                        </div>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr class="bg-indigo-50/30 text-slate-500 text-[11px] uppercase tracking-[0.15em] font-black">
                                    <th class="px-8 py-5 border-b border-slate-100">วัน/เดือน/ปี</th>
                                    <th class="px-8 py-5 border-b border-slate-100">ข้อมูลพนักงาน</th>
                                    <th class="px-8 py-5 border-b border-slate-100">พื้นที่ปฏิบัติงาน</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">เข้างาน</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">ออกงาน</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">มาสาย (ม.)</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="att in attendances.data" :key="att.id" class="hover:bg-indigo-50/20 transition-all group cursor-default">
                                    <td class="px-8 py-5">
                                        <div class="font-black text-slate-700 italic">{{ formatThaiDate(att.date) }}</div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="font-black text-slate-800 text-base leading-none mb-1">{{ att.employee_name }}</div>
                                        <div class="text-xs font-bold text-slate-400 tracking-tight">{{ att.department }} · {{ att.shift_name || 'ไม่ระบุกะ' }}</div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="px-3 py-1 bg-slate-100 rounded-lg text-slate-600 text-[11px] font-black w-max flex items-center gap-2 group-hover:bg-white border border-transparent group-hover:border-slate-100 transition-all">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                            {{ att.worksite }}
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="font-mono text-base font-black px-3 py-1 rounded-lg" :class="att.check_in_at ? 'text-emerald-600 bg-emerald-50 ring-1 ring-emerald-100' : 'text-slate-300 bg-slate-50 ring-1 ring-slate-100'">{{ att.check_in_at || '--:--' }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="font-mono text-base font-black px-3 py-1 rounded-lg" :class="att.check_out_at ? 'text-sky-600 bg-sky-50 ring-1 ring-sky-100' : 'text-slate-300 bg-slate-50 ring-1 ring-slate-100'">{{ att.check_out_at || '--:--' }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex flex-col items-center">
                                            <span v-if="att.is_late" class="text-base font-black text-amber-500 animate-pulse">{{ att.late_minutes }}</span>
                                            <span v-else class="text-slate-200">0</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black px-3 py-1.5 rounded-xl border border-transparent shadow-sm tracking-widest uppercase transition-all group-hover:scale-105"
                                            :class="getStatusBadge(att.status).class">
                                            <span class="w-2 h-2 rounded-full bg-current"></span>
                                            {{ getStatusBadge(att.status).label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="attendances.data.length === 0">
                                    <td colspan="7" class="py-24 text-center border-b-0 italic">
                                        <div class="text-6xl mb-6 grayscale opacity-30">📂</div>
                                        <p class="font-black text-slate-500 text-xl tracking-tight">ไม่พบข้อมูลการลงเวลาในเงื่อนไขที่ระบุ</p>
                                        <p class="text-sm text-slate-400 mt-2 font-medium">ลองเปลี่ยนเดือน หรือปรับเปลี่ยนฟิลเตอร์การค้นหาใหม่ดูนะครับ</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination footer -->
                    <div v-if="attendances.data.length > 0" class="px-8 py-8 border-t border-slate-100 bg-slate-50/30 flex justify-between items-center sm:flex-row flex-col gap-6">
                        <div class="text-sm font-bold text-slate-400 italic">
                            แสดงข้อมูล <span class="text-indigo-600 not-italic">{{ attendances.from }}-{{ attendances.to }}</span> จากทั้งหมด <span class="text-indigo-600 not-italic">{{ attendances.total }}</span> รายการ
                        </div>
                        <Pagination :links="attendances.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    border-radius: 2rem;
    box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.glass-card:hover {
    box-shadow: 0 20px 50px -15px rgba(99, 102, 241, 0.1);
    border-color: rgba(99, 102, 241, 0.2);
}

.custom-scrollbar::-webkit-scrollbar {
    height: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(226, 232, 240, 0.3);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}

/* Animations */
.glass-card, table tr {
    animation: slideInUp 0.6s cubic-bezier(0.1, 0.8, 0.2, 1) backwards;
}

@keyframes slideInUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.bar-animation {
    animation: growHeight 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes growHeight {
    from { transform: scaleY(0); }
    to { transform: scaleY(1); }
}
</style>

