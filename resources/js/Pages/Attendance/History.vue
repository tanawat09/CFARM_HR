<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    attendances: Object
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};
const formatTime = (timeString) => {
    if (!timeString) return '-';
    return new Date(timeString).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
};
const getStatusBadge = (status) => {
    const map = {
        'checked_in': { text: 'กำลังทำงาน', class: 'bg-emerald-100 text-emerald-800' },
        'checked_out': { text: 'เลิกงาน', class: 'bg-teal-100 text-teal-800' },
        'missed_checkout': { text: 'ลืมออกงาน', class: 'bg-rose-100 text-rose-800' },
    };
    return map[status] || { text: status, class: 'bg-slate-100 text-slate-800' };
};
</script>

<template>
    <Head title="ประวัติลงเวลา" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">ប្រวัติการลงເວລາ</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">วันที่</th>
                                        <th class="px-4 py-3">เวลาเข้า</th>
                                        <th class="px-4 py-3">สถานที่เข้า</th>
                                        <th class="px-4 py-3">เวลาออก</th>
                                        <th class="px-4 py-3">สถานที่ออก</th>
                                        <th class="px-4 py-3">สถานะ</th>
                                        <th class="px-4 py-3">ชั่วโมงทำงาน</th>
                                        <th class="px-4 py-3 text-center">หลักฐาน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="att in attendances.data" :key="att.id" class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium">{{ formatDate(att.date) }}</td>
                                        <td class="px-4 py-3">
                                            <span :class="att.is_late ? 'text-red-600 font-bold' : ''">
                                                {{ formatTime(att.check_in_at) }}
                                                <span v-if="att.is_late" class="text-xs ml-1">(สาย {{ att.late_minutes }} นาที)</span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 max-w-[150px] truncate" :title="att.check_in_worksite_id ? (att.checkInWorksite?.name || 'ไซต์งานประจำ') : (att.check_in_outside_reason || 'นอกพื้นที่')">
                                            {{ att.check_in_worksite_id ? '✔️ ในพื้นที่' : '⚠️ นอกพื้นที่' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="att.is_early_leave ? 'text-orange-600 font-bold' : ''">
                                                {{ formatTime(att.check_out_at) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ att.check_out_at ? (att.check_out_worksite_id ? '✔️ ในพื้นที่' : '⚠️ นอกพื้นที่') : '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusBadge(att.status.value || att.status).class]">
                                                {{ getStatusBadge(att.status.value || att.status).text }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ Math.floor(att.working_minutes / 60) }}h {{ att.working_minutes % 60 }}m</td>
                                        <td class="px-4 py-3 text-center flex justify-center gap-2">
                                            <a v-if="att.check_in_photo" :href="'/storage/' + att.check_in_photo" target="_blank" class="text-blue-600 hover:text-blue-800" title="รูปเข้างาน">
                                                <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </a>
                                            <a v-if="att.check_out_photo" :href="'/storage/' + att.check_out_photo" target="_blank" class="text-green-600 hover:text-green-800" title="รูปออกงาน">
                                                <svg class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">ไม่พบประวัติการลงเวลา</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
