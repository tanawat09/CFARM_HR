<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    employee: Object,
    today_attendance: Object,
    check_in_mode: String,
    worksites: Array
});

const formatTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="หน้าหลัก" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">หน้าหลัก พนักงาน</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
                <!-- Profile & Status Column -->
                <div class="w-full md:w-1/3">
                    <div class="glass sm:rounded-2xl p-8 flex flex-col items-center relative overflow-hidden group">
                        <!-- Background Gradient Blob -->
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-teal-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                        
                        <div class="w-32 h-32 rounded-full border-4 border-white mb-4 overflow-hidden shadow-xl z-10 transition transform group-hover:scale-105">
                            <svg v-if="!employee?.profile_photo" class="w-full h-full text-gray-300 p-6 bg-slate-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            <img v-else :src="'/storage/' + employee.profile_photo" class="w-full h-full object-cover">
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800 z-10">{{ employee?.first_name }} {{ employee?.last_name }}</h3>
                        <div class="inline-block mt-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold z-10 shadow-sm border border-indigo-200">
                             {{ employee?.position?.name }}
                        </div>
                        <p class="text-slate-500 mb-1 mt-3 z-10 font-medium">{{ employee?.department?.name }}</p>
                        
                        <div class="mt-6 w-full z-10 space-y-3">
                            <div class="bg-white/80 backdrop-blur rounded-xl p-4 flex justify-between shadow-sm border border-slate-100">
                                <span class="text-slate-500 text-sm font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                    รหัสพนักงาน
                                </span>
                                <span class="font-mono text-sm font-bold text-slate-800">{{ employee?.employee_code }}</span>
                            </div>
                            <div class="bg-white/80 backdrop-blur rounded-xl p-4 shadow-sm border border-slate-100">
                                <span class="text-slate-500 text-sm font-medium block mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    กะการทำงาน
                                </span>
                                <div class="font-bold text-slate-800">{{ employee?.shift?.name }}</div>
                                <div class="text-xs text-slate-500 font-mono mt-1 bg-slate-100 p-2 rounded inline-block">
                                    {{ formatTime('2024-01-01 ' + employee?.shift?.start_time) }} - {{ formatTime('2024-01-01 ' + employee?.shift?.end_time) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Column -->
                <div class="w-full md:w-2/3 flex flex-col gap-6">
                    <!-- Check In Box -->
                    <div class="glass sm:rounded-2xl p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-indigo-100 rounded-xl text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-slate-800">สถานะลงเวลาวันนี้</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="relative bg-white rounded-2xl p-6 text-center border-2 transition hover:shadow-lg" :class="today_attendance?.check_in_at ? 'border-teal-400/50 shadow-teal-100' : 'border-slate-100'">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-3 py-1 bg-white border rounded-full text-xs font-bold shadow-sm" :class="today_attendance?.check_in_at ? 'text-teal-600 border-teal-200' : 'text-slate-400 border-slate-200'">ขาเข้า</div>
                                <p class="text-sm text-slate-500 mb-2 mt-2">เวลาเข้างาน</p>
                                <p class="text-4xl font-bold font-mono" :class="today_attendance?.check_in_at ? 'text-teal-600' : 'text-slate-300'">
                                    {{ today_attendance?.check_in_at ? formatTime(today_attendance.check_in_at) : '--:--' }}
                                </p>
                            </div>
                            <div class="relative bg-white rounded-2xl p-6 text-center border-2 transition hover:shadow-lg" :class="today_attendance?.check_out_at ? 'border-orange-400/50 shadow-orange-100' : 'border-slate-100'">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-3 py-1 bg-white border rounded-full text-xs font-bold shadow-sm" :class="today_attendance?.check_out_at ? 'text-orange-600 border-orange-200' : 'text-slate-400 border-slate-200'">ขาออก</div>
                                <p class="text-sm text-slate-500 mb-2 mt-2">เวลาออกงาน</p>
                                <p class="text-4xl font-bold font-mono" :class="today_attendance?.check_out_at ? 'text-orange-600' : 'text-slate-300'">
                                    {{ today_attendance?.check_out_at ? formatTime(today_attendance.check_out_at) : '--:--' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <Link v-if="!today_attendance || (today_attendance && !today_attendance.check_out_at)" 
                                :href="route('attendance.index')" 
                                class="block w-full text-center py-5 rounded-2xl font-bold text-xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl"
                                :class="!today_attendance ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white' : 'bg-gradient-to-r from-rose-500 to-red-600 text-white'">
                                <span class="flex items-center justify-center gap-2">
                                    <svg v-if="!today_attendance" class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    {{ !today_attendance ? 'เข้างาน (Check-In)' : 'เลิกงาน (Check-Out)' }}
                                </span>
                            </Link>
                            
                            <div v-if="today_attendance?.check_out_at" class="bg-teal-50 border border-teal-100 text-teal-700 text-center py-4 rounded-xl flex items-center justify-center gap-2 font-medium shadow-sm">
                                <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                วันนี้คุณลงเวลาครบแล้ว พักผ่อนให้เต็มที่!
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mt-2">
                                <Link :href="route('attendance.history')" class="block w-full text-center py-3 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-xl text-sm font-medium transition">
                                    ดูประวัติลงเวลา
                                </Link>
                                <Link :href="route('leave.index')" class="block w-full text-center py-3 bg-orange-50 text-orange-600 hover:bg-orange-100 rounded-xl text-sm font-medium transition">
                                    🏖️ ระบบขอลาหยุด
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Worksite Box -->
                    <div class="glass sm:rounded-2xl p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-emerald-100 rounded-xl text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">สถานที่ปฏิบัติงาน</h3>
                        </div>
                        
                        <div v-if="worksites && worksites.length > 0">
                            <ul class="space-y-4">
                                <li v-for="ws in worksites" :key="ws.id" class="p-4 bg-white border border-slate-100 rounded-xl shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1">
                                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ ws.name }}</p>
                                            <p class="text-sm text-slate-500">{{ ws.address }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                        รัศมี {{ ws.radius_meters }} ม.
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="text-center py-10 bg-slate-50/50 rounded-xl border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-3">
                                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="font-bold text-slate-600">ไม่มีสถานที่ประจำ</p>
                            <p class="text-sm text-slate-400 mt-1">คุณได้รับสิทธิ์ลงเวลาแบบอิสระ ไม่จำกัดพื้นที่</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
