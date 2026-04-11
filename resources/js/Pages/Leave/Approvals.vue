<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    leaves: Object,
    pendingCount: Number,
    policies: Object,
    filters: Object,
});

const currentTab = ref(props.filters?.tab || 'pending');

const tabs = [
    { key: 'pending', label: 'รออนุมัติ', icon: '⏳' },
    { key: 'approved', label: 'อนุมัติแล้ว', icon: '✅' },
    { key: 'rejected', label: 'ไม่อนุมัติ', icon: '❌' },
];

const switchTab = (tab) => {
    currentTab.value = tab;
    router.get(route('leave.approvals'), { tab }, { preserveState: true, replace: true });
};

const formatDate = (d) => {
    if (!d) return '-';
    return new Date(d.split(' ')[0]).toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getLeaveLabel = (key) => {
    const k = typeof key === 'object' ? key.value : key;
    const p = props.policies?.[k];
    return p ? `${p.icon} ${p.name}` : k;
};

// --- Approve / Reject ---
const showRejectModal = ref(false);
const rejectTarget = ref(null);
const rejectForm = useForm({ note: '' });

const approveLeave = (leaveId) => {
    if (!confirm('ยืนยันการอนุมัติใบลานี้?')) return;
    router.post(route('leave.approve', leaveId), { note: '' }, { preserveScroll: true });
};

const openRejectModal = (leave) => {
    rejectTarget.value = leave;
    rejectForm.note = '';
    showRejectModal.value = true;
};

const submitReject = () => {
    rejectForm.post(route('leave.reject', rejectTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectTarget.value = null;
        },
    });
};
</script>

<template>
    <Head title="อนุมัติการลา" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">📋 อนุมัติการลา</h2>
                <div v-if="pendingCount > 0" class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-2 rounded-xl text-sm font-bold">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ pendingCount }} รายการรออนุมัติ
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Flash -->
                <div v-if="$page.props.flash?.message" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm font-semibold">
                    ✅ {{ $page.props.flash.message }}
                </div>

                <!-- Tabs -->
                <div class="flex gap-2 mb-6">
                    <button v-for="tab in tabs" :key="tab.key"
                        @click="switchTab(tab.key)"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold transition border shadow-sm"
                        :class="currentTab === tab.key
                            ? 'bg-emerald-600 text-white border-emerald-600 shadow-md'
                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                        {{ tab.icon }} {{ tab.label }}
                        <span v-if="tab.key === 'pending' && pendingCount > 0" class="ml-1.5 bg-white/30 text-white px-2 py-0.5 rounded-full text-xs">{{ pendingCount }}</span>
                    </button>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-emerald-50/50 text-emerald-900 border-b border-emerald-100/50">
                                    <th class="p-4 font-bold rounded-tl-2xl">พนักงาน</th>
                                    <th class="p-4 font-bold">ประเภทการลา</th>
                                    <th class="p-4 font-bold">วันที่ลา</th>
                                    <th class="p-4 font-bold text-center">ปริมาณที่ลา</th>
                                    <th class="p-4 font-bold">เหตุผล</th>
                                    <th v-if="currentTab !== 'pending'" class="p-4 font-bold">ผู้อนุมัติ</th>
                                    <th v-if="currentTab === 'pending'" class="p-4 font-bold text-center rounded-tr-2xl">จัดการ</th>
                                    <th v-else class="p-4 font-bold text-center rounded-tr-2xl">หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <tr v-for="leave in leaves.data" :key="leave.id" class="hover:bg-emerald-50/30 transition">
                                    <!-- Employee Info -->
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-emerald-100 border-2 border-emerald-200 flex items-center justify-center text-sm font-bold text-emerald-600 shrink-0">
                                                {{ leave.employee?.first_name?.charAt(0) }}{{ leave.employee?.last_name?.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">{{ leave.employee?.first_name }} {{ leave.employee?.last_name }}</p>
                                                <p class="text-xs text-slate-500">{{ leave.employee?.position?.name }} · {{ leave.employee?.department?.name }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Leave Type -->
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-700">{{ getLeaveLabel(leave.leave_type) }}</span>
                                            <span v-if="leave.leave_format === 'hourly'" class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">รายชั่วโมง</span>
                                        </div>
                                    </td>

                                    <!-- Dates -->
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1 text-slate-700 font-medium">
                                                <span>{{ formatDate(leave.start_date) }}</span>
                                                <span v-if="leave.leave_format === 'hourly'" class="text-xs font-bold text-emerald-600">({{ leave.start_time?.substring(0,5) }} - {{ leave.end_time?.substring(0,5) }} น.)</span>
                                            </div>
                                            <div v-if="leave.leave_format === 'daily'" class="text-xs text-slate-400">ถึง {{ formatDate(leave.end_date) }}</div>
                                        </div>
                                    </td>

                                    <!-- Total Days -->
                                    <td class="p-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="inline-flex items-center justify-center min-w-[32px] px-2 h-8 bg-emerald-50 text-emerald-700 font-extrabold rounded-lg border border-emerald-100 text-sm">
                                                {{ leave.total_days }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase mt-1">วัน</span>
                                        </div>
                                    </td>

                                    <!-- Reason -->
                                    <td class="p-4">
                                        <p class="text-slate-600 max-w-[200px] truncate" :title="leave.reason">{{ leave.reason || '-' }}</p>
                                    </td>

                                    <!-- Approver (for non-pending) -->
                                    <td v-if="currentTab !== 'pending'" class="p-4">
                                        <span v-if="leave.approver" class="text-sm text-slate-600">{{ leave.approver.first_name }} {{ leave.approver.last_name }}</span>
                                        <span v-else class="text-slate-400">-</span>
                                    </td>

                                    <!-- Actions (pending) -->
                                    <td v-if="currentTab === 'pending'" class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="approveLeave(leave.id)"
                                                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm transition transform hover:-translate-y-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                อนุมัติ
                                            </button>
                                            <button @click="openRejectModal(leave)"
                                                class="inline-flex items-center gap-1.5 bg-white hover:bg-rose-50 text-rose-600 text-xs font-bold px-4 py-2 rounded-xl border border-rose-200 shadow-sm transition transform hover:-translate-y-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                ไม่อนุมัติ
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Note (non-pending) -->
                                    <td v-else class="p-4 text-center">
                                        <span class="text-xs text-slate-500">{{ leave.approval_note || '-' }}</span>
                                    </td>
                                </tr>

                                <tr v-if="leaves.data.length === 0">
                                    <td :colspan="currentTab === 'pending' ? 6 : 7" class="p-12 text-center text-slate-400">
                                        <div class="text-4xl mb-2">📭</div>
                                        <p class="font-bold text-slate-500">ไม่มีรายการ{{ currentTab === 'pending' ? 'ที่รออนุมัติ' : '' }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="leaves.links && leaves.data.length > 0" class="px-6 py-4 border-t border-slate-100 flex justify-end">
                        <Pagination :links="leaves.links" />
                    </div>
                </div>

            </div>
        </div>

        <!-- Reject Modal -->
        <Teleport to="body">
            <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showRejectModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-2 flex items-center gap-2">
                        <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        ปฏิเสธใบลา
                    </h3>
                    <p class="text-sm text-slate-500 mb-4" v-if="rejectTarget">
                        ใบลาของ <span class="font-bold text-slate-700">{{ rejectTarget.employee?.first_name }} {{ rejectTarget.employee?.last_name }}</span>
                    </p>
                    <div class="mb-6">
                        <label class="block font-bold text-sm text-slate-700 mb-2">เหตุผลที่ไม่อนุมัติ <span class="text-rose-500">*</span></label>
                        <textarea v-model="rejectForm.note" rows="3"
                            class="w-full rounded-xl border-slate-200 shadow-sm focus:border-rose-500 focus:ring-rose-500 resize-none"
                            placeholder="กรุณาระบุเหตุผลในการไม่อนุมัติ..."></textarea>
                        <p v-if="rejectForm.errors.note" class="mt-1 text-sm text-rose-600">{{ rejectForm.errors.note }}</p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="showRejectModal = false" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700 transition">ยกเลิก</button>
                        <button @click="submitReject" :disabled="rejectForm.processing"
                            class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl shadow-md transition disabled:opacity-50">
                            <svg v-if="rejectForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            ยืนยันไม่อนุมัติ
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
