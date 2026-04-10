<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    leaveTypes: Array,  // [{key, name, icon}]
    stats: Object       // { items: {key: {quota, used, remaining, ...}}, is_probation, hire_date }
});

const form = useForm({
    leave_type: props.leaveTypes?.[0]?.key || '',
    start_date: '',
    end_date: '',
    reason: '',
    attachment: null,
});

const fileInputRef = ref(null);

// Current selected policy stats
const currentStats = computed(() => {
    return props.stats?.items?.[form.leave_type] || null;
});

const totalDays = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    if (end < start) return 0;
    return Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
});

const reasonRequired = computed(() => currentStats.value?.requires_reason ?? true);

const attachmentRequired = computed(() => {
    const threshold = currentStats.value?.requires_attachment_after_days;
    return threshold && totalDays.value >= threshold;
});

const attachmentThreshold = computed(() => currentStats.value?.requires_attachment_after_days || null);

const isProbation = computed(() => props.stats?.is_probation || false);
const isBlocked = computed(() => {
    if (!currentStats.value) return false;
    return isProbation.value && !currentStats.value.probation_allowed;
});

// Clear attachment when switching away from types that need it
watch(() => form.leave_type, () => {
    form.attachment = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
});

const onFileChange = (e) => {
    form.attachment = e.target.files[0];
};

const submit = () => {
    form.post(route('leave.store'), { forceFormData: true });
};
</script>

<template>
    <Head title="เขียนใบลาใหม่" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('leave.index')" class="text-slate-400 hover:text-indigo-600 transition bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">📝 เขียนใบลาใหม่</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <!-- Quota Overview Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div v-for="(info, key) in stats.items" :key="key" class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
                        <div class="text-2xl mb-1">{{ info.icon }}</div>
                        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ info.name }}</div>
                        <div class="text-xl font-extrabold mt-1" :class="info.remaining > 0 ? 'text-indigo-600' : 'text-rose-600'">{{ info.used }}/{{ info.quota }}</div>
                        <div class="text-xs text-slate-400">วัน</div>
                        <div v-if="isProbation && !info.probation_allowed" class="mt-1 text-xs font-bold text-rose-500">🔒 ไม่มีสิทธิ์</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 relative">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 pointer-events-none"></div>

                    <form @submit.prevent="submit" class="p-8 relative z-10 space-y-6">
                        
                        <!-- Leave Type Selection -->
                        <div>
                            <label class="block font-bold text-sm text-slate-700 mb-3">ประเภทการลา <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label v-for="type in leaveTypes" :key="type.key" class="relative cursor-pointer" :class="{'opacity-40 pointer-events-none': isProbation && stats.items[type.key] && !stats.items[type.key].probation_allowed}">
                                    <input type="radio" :value="type.key" v-model="form.leave_type" class="peer sr-only" name="leave_type">
                                    <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl text-center hover:border-indigo-300 hover:bg-indigo-50/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md transition-all">
                                        <div class="text-2xl mb-1">{{ type.icon }}</div>
                                        <div class="font-bold text-sm text-slate-700">{{ type.name }}</div>
                                    </div>
                                    <svg class="w-5 h-5 absolute top-2 right-2 text-indigo-500 hidden peer-checked:block pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </label>
                            </div>
                            <InputError :message="form.errors.leave_type" class="mt-2" />
                        </div>

                        <!-- Blocked Warning -->
                        <div v-if="isBlocked" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start gap-3">
                            <svg class="w-6 h-6 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <p class="font-bold text-rose-700">ไม่สามารถลาประเภทนี้ได้</p>
                                <p class="text-sm text-rose-600">คุณอยู่ในช่วงทดลองงาน (Probation) ยังไม่ได้รับสิทธิ์ประเภทนี้</p>
                            </div>
                        </div>

                        <!-- Remaining Quota Info -->
                        <div v-if="!isBlocked && currentStats && currentStats.quota > 0" class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="text-3xl">{{ currentStats.icon }}</div>
                                <div>
                                    <p class="font-bold text-indigo-900">{{ currentStats.name }}</p>
                                    <p class="text-sm text-indigo-700">ใช้ไปแล้ว {{ currentStats.used }} วัน จากทั้งหมด {{ currentStats.quota }} วัน</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-extrabold" :class="currentStats.remaining > 0 ? 'text-emerald-600' : 'text-rose-600'">{{ currentStats.remaining }}</div>
                                <div class="text-xs text-slate-500 font-bold">วันที่เหลือ</div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">วันที่เริ่มต้น <span class="text-rose-500">*</span></label>
                                <input type="date" v-model="form.start_date" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required>
                                <InputError :message="form.errors.start_date" class="mt-2" />
                            </div>
                            <div>
                                <label class="block font-bold text-sm text-slate-700 mb-2">ถึงวันที่ <span class="text-rose-500">*</span></label>
                                <input type="date" v-model="form.end_date" :min="form.start_date" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition" required>
                                <InputError :message="form.errors.end_date" class="mt-2" />
                            </div>
                        </div>

                        <!-- Total days preview -->
                        <div v-if="totalDays > 0" class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-600">📅 จำนวนวันที่ขอลา</span>
                            <span class="text-lg font-extrabold text-indigo-600">{{ totalDays }} วัน</span>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block font-bold text-sm text-slate-700 mb-2">
                                รายละเอียด / เหตุผลที่ลา
                                <span v-if="reasonRequired" class="text-rose-500">*</span>
                                <span v-else class="text-slate-400 font-normal">(ไม่บังคับ)</span>
                            </label>
                            <textarea v-model="form.reason" rows="3" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition resize-none" :placeholder="reasonRequired ? 'กรุณาระบุเหตุผลการขอลา...' : 'ระบุเหตุผล (ไม่บังคับ)'" :required="reasonRequired"></textarea>
                            <InputError :message="form.errors.reason" class="mt-2" />
                        </div>

                        <!-- Attachment Upload (dynamic based on policy) -->
                        <div v-if="attachmentThreshold" class="space-y-2">
                            <label class="block font-bold text-sm text-slate-700 mb-2">
                                📎 แนบเอกสาร
                                <span v-if="attachmentRequired" class="text-rose-500">* (ลา {{ attachmentThreshold }}+ วัน)</span>
                                <span v-else class="text-slate-400 font-normal">(ไม่บังคับ)</span>
                            </label>

                            <div v-if="attachmentRequired" class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2 mb-2">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <p class="text-sm text-amber-700 font-semibold">{{ currentStats?.name }} ตั้งแต่ {{ attachmentThreshold }} วันขึ้นไป จำเป็นต้องแนบเอกสารประกอบ</p>
                            </div>

                            <input ref="fileInputRef" type="file" @change="onFileChange" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl" />
                            <p class="text-xs text-slate-400">รองรับไฟล์ JPG, PNG, PDF ขนาดไม่เกิน 5MB</p>
                            <InputError :message="form.errors.attachment" class="mt-2" />
                        </div>

                        <!-- Submit -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-4">
                            <Link :href="route('leave.index')" class="text-slate-500 font-bold hover:text-slate-700 transition px-4 py-2">ยกเลิก</Link>
                            <button type="submit" :disabled="form.processing || isBlocked" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl px-8 py-3 text-sm font-bold text-white shadow-lg hover:shadow-indigo-500/30 transition transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                {{ form.processing ? 'กำลังส่งคำขอ...' : 'ส่งใบขอลา' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
