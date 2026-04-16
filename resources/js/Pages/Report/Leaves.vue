<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    stats: Object,
    chartData: Array,
    leaves: Object,
    filters: Object
});

const startMonth = ref(props.filters.start_month);
const endMonth = ref(props.filters.end_month);

watch([startMonth, endMonth], ([newStart, newEnd]) => {
    if (newStart !== props.filters.start_month || newEnd !== props.filters.end_month) {
        router.get(route('reports.leaves'), { start_month: newStart, end_month: newEnd }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }
});

const exportExcel = () => {
    window.location.href = route('reports.leaves.export', { 
        start_month: startMonth.value,
        end_month: endMonth.value
    });
};

const formatThaiDate = (dateStr) => {
    if (!dateStr) return '-';
    // dateStr format is usually YYYY-MM-DD
    const d = new Date(dateStr);
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getLeaveTypeBadge = (type) => {
    const map = {
        'ลาป่วย': { class: 'bg-rose-100 text-rose-700' },
        'ลากิจ': { class: 'bg-amber-100 text-amber-700' },
        'ลาพักร้อน': { class: 'bg-emerald-100 text-emerald-700' },
        'ลาคลอด': { class: 'bg-fuchsia-100 text-fuchsia-700' },
        'ลาบวช': { class: 'bg-orange-100 text-orange-700' },
    };
    return map[type] || { class: 'bg-emerald-100 text-emerald-700' };
};

const getStatusBadge = (status) => {
    const map = {
        'approved': { label: 'อนุมัติ', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
        'pending': { label: 'รออนุมัติ', class: 'bg-amber-100 text-amber-700 border-amber-200' },
        'rejected': { label: 'ไม่อนุมัติ', class: 'bg-rose-100 text-rose-700 border-rose-200' },
    };
    return map[status] || { label: status, class: 'bg-slate-100 text-slate-500 border-slate-200' };
};

const totalChartLeaves = computed(() => {
    return props.chartData.reduce((acc, curr) => acc + curr.count, 0) || 1;
});
</script>

<template>
    <Head title="รายงานสถิติการลางาน" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col gap-3">
                    <h2 class="font-black text-3xl text-slate-800 leading-tight tracking-tight flex items-center gap-3">
                        <span class="p-3 bg-emerald-600 rounded-2xl shadow-lg shadow-emerald-200">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 3H5c-1.1 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                        </span>
                        สถิติการลางาน
                    </h2>
                    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl w-max shadow-inner">
                        <Link :href="route('reports.leaves')" class="flex items-center gap-2 px-5 py-2.5 text-sm font-black rounded-xl transition-all bg-white text-emerald-700 shadow-md ring-1 ring-slate-900/5">
                            <span class="text-lg">🏖️</span> สถิติการลางาน
                        </Link>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-2 py-1.5 shadow-sm p-0 m-0">
                        <span class="text-xs font-black text-slate-400 uppercase ml-2 select-none">ตั้งแต่</span>
                        <input type="month" v-model="startMonth" class="border-0 bg-transparent text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer py-1 pl-2 pr-0" />
                        <span class="text-slate-300">|</span>
                        <span class="text-xs font-black text-slate-400 uppercase select-none">ถึง</span>
                        <input type="month" v-model="endMonth" class="border-0 bg-transparent text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer py-1 pl-2 pr-4" />
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
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-6 relative z-10">
                    <!-- ลาพักร้อน -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-4 -bottom-4 text-emerald-50 w-20 h-20 transform group-hover:scale-110 transition">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z"/></svg>
                        </div>
                        <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10 flex items-center gap-1.5"><span class="text-emerald-500">🏖️</span> ลาพักร้อน</h4>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-600 relative z-10">{{ stats.annual }}<span class="text-[10px] sm:text-xs text-slate-400 font-medium ml-1">คำขอ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-400"></div>
                    </div>
                    
                    <!-- ลากิจ -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                        <div class="absolute -right-4 -bottom-4 text-amber-50 w-20 h-20 transform group-hover:scale-110 transition">
                             <svg fill="currentColor" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                         </div>
                        <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10 flex items-center gap-1.5"><span class="text-amber-500">💼</span> ลากิจ</h4>
                        <div class="text-2xl sm:text-3xl font-black text-amber-500 relative z-10">{{ stats.personal }}<span class="text-[10px] sm:text-xs text-slate-400 font-medium ml-1">คำขอ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-400"></div>
                    </div>

                    <!-- ลาป่วย -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                         <div class="absolute -right-4 -bottom-4 text-rose-50 w-20 h-20 transform group-hover:scale-110 transition">
                             <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                         </div>
                        <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10 flex items-center gap-1.5"><span class="text-rose-500">🤒</span> ลาป่วย</h4>
                        <div class="text-2xl sm:text-3xl font-black text-rose-500 relative z-10">{{ stats.sick }}<span class="text-[10px] sm:text-xs text-slate-400 font-medium ml-1">คำขอ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-rose-400"></div>
                    </div>
                    
                    <!-- ลาคลอด -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                         <div class="absolute -right-4 -bottom-4 text-fuchsia-50 w-20 h-20 transform group-hover:scale-110 transition">
                             <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                         </div>
                        <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10 flex items-center gap-1.5"><span class="text-fuchsia-500">👶</span> ลาคลอด</h4>
                        <div class="text-2xl sm:text-3xl font-black text-fuchsia-500 relative z-10">{{ stats.maternity }}<span class="text-[10px] sm:text-xs text-slate-400 font-medium ml-1">คำขอ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-fuchsia-500 to-fuchsia-400"></div>
                    </div>
                    
                    <!-- ลาบวช -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 overflow-hidden relative group hover:-translate-y-1 transition duration-300">
                         <div class="absolute -right-4 -bottom-4 text-orange-50 w-20 h-20 transform group-hover:scale-110 transition">
                             <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                         </div>
                        <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 relative z-10 flex items-center gap-1.5"><span class="text-orange-500">🙏</span> ลาบวช</h4>
                        <div class="text-2xl sm:text-3xl font-black text-orange-500 relative z-10">{{ stats.other }}<span class="text-[10px] sm:text-xs text-slate-400 font-medium ml-1">คำขอ</span></div>
                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-orange-400"></div>
                    </div>
                </div>

                <!-- Chart Distribution Section -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8 relative z-10">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        สัดส่วนประเภทการลา (เฉพาะที่อนุมัติแล้ว)
                    </h3>
                    
                    <div v-if="chartData.length > 0" class="flex flex-col gap-4">
                        <div v-for="item in chartData" :key="item.type" class="flex items-center gap-4">
                            <div class="w-24 shrink-0 text-sm font-bold text-slate-600">{{ item.type }}</div>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden relative">
                                <div class="absolute top-0 left-0 bottom-0 bg-emerald-500 transition-all duration-1000 ease-out" 
                                     :style="{ width: `${(item.count / totalChartLeaves) * 100}%` }">
                                </div>
                            </div>
                            <div class="w-12 shrink-0 text-right font-bold text-emerald-600">{{ item.count }} <span class="text-xs text-slate-400">ครั้ง</span></div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-slate-500 bg-slate-50 rounded-xl">
                        ไม่มีข้อมูลการลาที่ได้รับการอนุมัติในรอบเดือนนี้
                    </div>
                </div>

                <!-- DataTable Section -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm relative z-10 flex flex-col">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            ประวัติยื่นขอลาหยุด
                        </h3>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider">
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">พนักงาน</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">พื้นที่/แผนก</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">ประเภท</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">วันที่เริ่ม - สิ้นสุด</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">รวม (วัน)</th>
                                    <th class="px-6 py-4 font-bold text-center border-b border-slate-100">สถานะ</th>
                                    <th class="px-6 py-4 font-bold border-b border-slate-100">ผู้อนุมัติ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <tr v-for="leave in leaves.data" :key="leave.id" class="hover:bg-emerald-50/30 transition group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-800">{{ leave.employee_name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-500">{{ leave.department }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full"
                                            :class="getLeaveTypeBadge(leave.leave_type).class">
                                            {{ leave.leave_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-700">
                                        {{ formatThaiDate(leave.start_date) }} - {{ formatThaiDate(leave.end_date) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-emerald-600">
                                        {{ leave.total_days }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-emerald-600">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full border"
                                            :class="getStatusBadge(leave.status).class">
                                            {{ getStatusBadge(leave.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{ leave.approver_name }}
                                    </td>
                                </tr>
                                <tr v-if="leaves.data.length === 0">
                                    <td colspan="7" class="py-16 text-center border-b-0">
                                        <div class="text-4xl mb-3 text-slate-200">🏖️</div>
                                        <p class="font-bold text-slate-500 text-base">ไม่มีการยื่นลางาน</p>
                                        <p class="text-sm text-slate-400 mt-1">ไม่มีบันทึกข้อมูลในเดือนที่ท่านเลือก</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination footer -->
                    <div v-if="leaves.data.length > 0" class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex justify-between items-center sm:flex-row flex-col gap-4">
                        <div class="text-sm text-slate-500">
                            แสดงข้อมูล <span class="font-bold">{{ leaves.from }}</span> ถึง <span class="font-bold">{{ leaves.to }}</span> จากทั้งหมด <span class="font-bold">{{ leaves.total }}</span> รายการ
                        </div>
                        <Pagination :links="leaves.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
