<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    stats: Object,
    chartData: Array,
    attendances: Object,
    filters: Object
});

const selectedMonth = ref(props.filters.month);

watch(selectedMonth, (newVal) => {
    if (newVal !== props.filters.month) {
        router.get(route('reports.index'), { month: newVal }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }
});

const exportExcel = () => {
    window.location.href = route('reports.export', { month: selectedMonth.value });
};

const getMaxChartValue = () => {
    if (!props.chartData || props.chartData.length === 0) return 10;
    let max = 10;
    props.chartData.forEach(d => {
        const total = d.fake_on_time + d.fake_late;
        if (total > max) max = total;
    });
    return max;
};

const maxVal = getMaxChartValue();

const formatThaiDate = (dateStr) => {
    if (!dateStr) return '-';
    // dateStr format is usually YYYY-MM-DD
    const d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getStatusBadge = (status) => {
    const map = {
        'checked_in': { label: 'เข้างานแล้ว', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
        'checked_out': { label: 'ออกงานแล้ว', class: 'bg-sky-100 text-sky-700 border-sky-200' },
        'absent': { label: 'ขาดงาน', class: 'bg-rose-100 text-rose-700 border-rose-200' },
        'leave': { label: 'ลาหยุด', class: 'bg-purple-100 text-purple-700 border-purple-200' },
        'holiday': { label: 'วันหยุด', class: 'bg-slate-100 text-slate-700 border-slate-200' },
    };
    return map[status] || { label: status, class: 'bg-slate-100 text-slate-500 border-slate-200' };
};
</script>

<template>
    <Head title="รายงานสรุป" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col gap-3">
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">📊 รายงานและสถิติการทำงาน</h2>
                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl w-max shadow-inner">
                        <Link :href="route('reports.leaves')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-bold rounded-xl transition-all text-slate-500 hover:text-slate-700 hover:bg-slate-200/50">
                            <span class="text-lg">🏖️</span> สถิติการลางาน
                        </Link>
                        <Link :href="route('reports.index')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-black rounded-xl transition-all bg-white text-indigo-700 shadow-md ring-1 ring-slate-900/5">
                            <span class="text-lg">🕒</span> เวลาและการเข้างาน
                        </Link>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <input type="month" v-model="selectedMonth" class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition cursor-pointer" />
                    </div>
                    <button @click="exportExcel" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 border border-transparent rounded-xl px-5 py-2.5 text-sm font-bold text-white hover:from-emerald-600 hover:to-teal-600 transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                        ส่งออกเป็น CSV
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- KPI Widgets grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 relative z-10">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-4 -bottom-4 text-indigo-50 w-24 h-24 transform group-hover:scale-110 transition">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">พนักงานรวมทั้งหมด</h4>
                        <div class="text-3xl font-black text-indigo-600 relative z-10">{{ stats.total_employees }}<span class="text-sm text-slate-400 font-medium ml-1">คน</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
                    </div>
                    
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-4 -bottom-4 text-emerald-50 w-24 h-24 transform group-hover:scale-110 transition">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">อัตราส่วนเข้างานเฉลี่ย</h4>
                        <div class="text-3xl font-black text-emerald-600 relative z-10">{{ stats.avg_attendance_rate }}<span class="text-lg ml-1">%</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                         <div class="absolute -right-4 -bottom-4 text-amber-50 w-24 h-24 transform group-hover:scale-110 transition">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">สถิติมาสายในเดือน</h4>
                        <div class="text-3xl font-black text-amber-500 relative z-10">{{ stats.total_lates }}<span class="text-sm text-slate-400 font-medium ml-1">ครั้ง</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-400"></div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                         <div class="absolute -right-4 -bottom-4 text-purple-50 w-24 h-24 transform group-hover:scale-110 transition">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                        </div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10">การลาหยุดในเดือน</h4>
                        <div class="text-3xl font-black text-purple-600 relative z-10">{{ stats.total_leaves }}<span class="text-sm text-slate-400 font-medium ml-1">รายการ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-purple-400"></div>
                    </div>
                </div>

                <!-- Monthly Activity Chart Section -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 relative z-10">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 mb-1">ภาพรวมการเข้างานรายเดือน</h3>
                            <p class="text-sm text-slate-500">เปรียบเทียบสถิติพนักงานที่มาตรงเวลาและมาสายในแต่ละวัน</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-bold text-slate-600 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded bg-emerald-400 shadow-sm"></div> ตรงเวลา</div>
                            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded bg-amber-400 shadow-sm"></div> มาสาย</div>
                        </div>
                    </div>

                    <!-- Custom CSS Bar Chart for Month -->
                    <div class="h-64 relative flex items-end overflow-hidden pb-6">
                        <!-- Y-Axis Grid Lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pb-6 pointer-events-none">
                            <div class="w-full h-px bg-slate-100 mt-2"></div>
                            <div class="w-full h-px bg-slate-100"></div>
                            <div class="w-full h-px bg-slate-100"></div>
                            <div class="w-full h-px bg-slate-100"></div>
                            <div class="w-full h-px bg-slate-200"></div>
                        </div>
                        
                        <!-- Bars -->
                        <div class="flex-1 flex justify-between items-end gap-1 h-full relative z-10 overflow-x-auto custom-scrollbar pr-2 pb-2">
                            <div v-for="item in chartData" :key="item.date" class="relative flex flex-col justify-end items-center group w-full min-w-[20px] max-w-[40px] shrink-0" style="height: 100%;">
                                <!-- Bar Container -->
                                <div class="w-full flex flex-col justify-end px-0.5" style="height: calc(100% - 24px); position: relative; z-index: 10;">
                                    <!-- Tooltip (Hover) -->
                                    <div class="absolute -top-14 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-3 rounded-lg shadow-xl opacity-0 group-hover:opacity-100 group-hover:-translate-y-1 transition-all whitespace-nowrap z-20 pointer-events-none">
                                        <div class="font-bold border-b border-white/20 mb-1 pb-0.5">{{ formatThaiDate(item.date) }}</div>
                                        <div class="text-emerald-300">ตรงเวลา: {{ item.fake_on_time }}</div>
                                        <div class="text-amber-300">มาสาย: {{ item.fake_late }}</div>
                                    </div>
                                    
                                    <!-- Late Bar Part -->
                                    <div class="w-full bg-gradient-to-t from-amber-400 to-amber-300 rounded-t-sm transition-all duration-700 ease-out hover:brightness-110 shadow-[inset_0_1px_rgba(255,255,255,0.4)]" 
                                        :style="{ height: `${(item.fake_late / maxVal) * 100}%` }">
                                    </div>
                                    <!-- On Time Bar Part -->
                                    <div class="w-full bg-gradient-to-t from-emerald-500 to-emerald-400 rounded-b-sm border-t border-emerald-300 transition-all duration-700 ease-out hover:brightness-110" 
                                        :style="{ height: `${(item.fake_on_time / maxVal) * 100}%` }">
                                    </div>
                                </div>
                                <!-- X-Axis Label -->
                                <div class="text-[10px] sm:text-xs font-bold text-slate-400 mt-2 h-6 text-center whitespace-nowrap overflow-hidden text-ellipsis w-full">{{ item.label }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable Section -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm relative z-10 flex flex-col">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            ประวัติการลงเวลาทำงาน
                        </h3>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">วันที่</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">พนักงาน</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">พื้นที่</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">กะการทำงาน</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">เข้า</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">ออก</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">สาย (นาที)</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="att in attendances.data" :key="att.id" class="hover:bg-indigo-50/30 transition group">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-700">{{ formatThaiDate(att.date) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ att.employee_name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ att.department }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-600 flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>{{ att.worksite }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-slate-500">
                                        {{ att.shift_name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-mono font-bold" :class="att.check_in_at ? 'text-emerald-600' : 'text-slate-300'">{{ att.check_in_at || '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-mono font-bold" :class="att.check_out_at ? 'text-sky-600' : 'text-slate-300'">{{ att.check_out_at || '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold" :class="att.is_late ? 'text-amber-500' : 'text-slate-300'">
                                        {{ att.is_late ? att.late_minutes : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full border"
                                            :class="getStatusBadge(att.status).class">
                                            {{ getStatusBadge(att.status).label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="attendances.data.length === 0">
                                    <td colspan="8" class="py-16 text-center border-b-0">
                                        <div class="text-4xl mb-3 text-slate-200">📭</div>
                                        <p class="font-bold text-slate-500 text-base">ไม่พบข้อมูลการลงเวลา</p>
                                        <p class="text-sm text-slate-400 mt-1">ไม่มีบันทึกข้อมูลในเดือนที่ท่านเลือก</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination footer -->
                    <div v-if="attendances.data.length > 0" class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex justify-between items-center sm:flex-row flex-col gap-4">
                        <div class="text-sm text-slate-500">
                            แสดงข้อมูล <span class="font-bold">{{ attendances.from }}</span> ถึง <span class="font-bold">{{ attendances.to }}</span> จากทั้งหมด <span class="font-bold">{{ attendances.total }}</span> รายการ
                        </div>
                        <Pagination :links="attendances.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

/* Base animations for bars to grow */
.bg-emerald-400, .bg-emerald-500, .bg-amber-400, .from-emerald-500, .from-amber-400 {
    animation: growBar 0.8s cubic-bezier(0.1, 0.8, 0.2, 1);
    transform-origin: bottom;
}

@keyframes growBar {
    from {
        transform: scaleY(0);
        opacity: 0.5;
    }
    to {
        transform: scaleY(1);
        opacity: 1;
    }
}
</style>
