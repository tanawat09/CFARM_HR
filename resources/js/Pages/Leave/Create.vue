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
    leave_format: 'daily',
    start_date: '',
    end_date: '',
    start_time: '08:00',
    end_time: '10:00',
    reason: '',
    attachment: null,
});

const fileInputRef = ref(null);

// Current selected policy stats
const currentStats = computed(() => {
    return props.stats?.items?.[form.leave_type] || null;
});

const totalDays = computed(() => {
    if (form.leave_format === 'hourly') {
        if (!form.start_date || !form.start_time || !form.end_time) return 0;
        const [sh, sm] = form.start_time.split(':').map(Number);
        const [eh, em] = form.end_time.split(':').map(Number);
        const diffMinutes = (eh * 60 + em) - (sh * 60 + sm);
        if (diffMinutes <= 0) return 0;
        return Number((diffMinutes / 60 / 8).toFixed(3));
    } else {
        if (!form.start_date || !form.end_date) return 0;
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        if (end < start) return 0;
        return Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
    }
});

const totalHours = computed(() => {
    if (form.leave_format !== 'hourly') return 0;
    if (!form.start_date || !form.start_time || !form.end_time) return 0;
    const [sh, sm] = form.start_time.split(':').map(Number);
    const [eh, em] = form.end_time.split(':').map(Number);
    const diffMinutes = (eh * 60 + em) - (sh * 60 + sm);
    if (diffMinutes <= 0) return 0;
    const h = Math.floor(diffMinutes / 60);
    const m = diffMinutes % 60;
    return `${h} ชม. ${m > 0 ? m + ' นาที' : ''}`;
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
        <div class="relative min-h-[calc(100vh-4rem)] bg-[#F8FAFC] overflow-hidden font-sans flex flex-col justify-center">
            <!-- Dynamic Background Effects -->
            <div class="absolute top-0 left-0 w-full h-80 bg-gradient-to-b from-emerald-500/10 to-transparent pointer-events-none"></div>
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-green-400/20 rounded-full mix-blend-multiply filter blur-[80px] opacity-70 animate-pulse pointer-events-none" style="animation-duration: 8s;"></div>
            <div class="absolute top-40 -left-20 w-72 h-72 bg-teal-400/20 rounded-full mix-blend-multiply filter blur-[80px] opacity-70 animate-pulse pointer-events-none" style="animation-duration: 10s; animation-delay: 2s;"></div>

            <!-- Added max-w-7xl to constrain the width perfectly -->
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 relative z-10">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 xl:gap-10">
                    
                    <!-- Left Sidebar (Header & Quotas) -->
                    <div class="lg:col-span-4 xl:col-span-4 flex flex-col gap-6">
                        <!-- Modern Header -->
                        <div class="flex items-start gap-4">
                            <Link :href="route('leave.index')" class="text-slate-400 hover:text-emerald-600 transition bg-white/80 backdrop-blur-xl p-3 rounded-2xl shadow-sm border border-white hover:shadow-md hover:-translate-y-1 group shrink-0">
                                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            </Link>
                            <div>
                                <h2 class="font-black text-2xl sm:text-3xl text-slate-800 tracking-tight leading-tight">ทำรายการ <br/><span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">เขียนใบลา</span></h2>
                                <p class="text-slate-500 font-medium text-xs sm:text-sm mt-1.5 flex items-center gap-2">
                                    <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                                    ระบุข้อมูลเพื่อยื่นเรื่องขออนุมัติ
                                </p>
                            </div>
                        </div>

                        <!-- Dashboard Quota Cards -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    โควตาการลางานของคุณ
                                </h3>
                            </div>
                            <!-- Flex layout for quotas to adapt to any count -->
                            <div class="flex flex-col gap-3 max-h-[calc(100vh-200px)] overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(info, key) in stats.items" :key="key" 
                                    class="bg-white/80 backdrop-blur-xl rounded-2xl p-4 border border-white shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] relative overflow-hidden group hover:shadow-[0_8px_30px_rgba(16,185,129,0.1)] transition-all hover:-translate-y-0.5 duration-300">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-emerald-50 to-transparent rounded-full -mr-8 -mt-8 opacity-50 group-hover:scale-150 group-hover:bg-emerald-100/50 transition-all duration-700"></div>
                                    <div class="flex flex-col relative z-10">
                                        <div class="flex items-center justify-between mb-2.5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-xl shadow-inner group-hover:bg-white group-hover:shadow transition-all group-hover:scale-110 duration-300">
                                                    {{ info.icon }}
                                                </div>
                                                <div class="font-bold text-sm text-slate-700">{{ info.name }}</div>
                                            </div>
                                            <div class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100 shadow-sm leading-none flex items-center">
                                                คงเหลือ {{ info.remaining }}
                                            </div>
                                        </div>
                                        
                                        <!-- Animated Progress Bar -->
                                        <div class="flex items-center gap-3">
                                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner flex-1">
                                                <div class="h-full rounded-full transition-all duration-1500 ease-out relative" 
                                                    :class="info.remaining > 0 ? 'bg-gradient-to-r from-emerald-400 to-teal-400' : 'bg-gradient-to-r from-rose-400 to-red-500'" 
                                                    :style="`width: ${Math.min((info.used / (info.quota || 1)) * 100, 100)}%`">
                                                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                                </div>
                                            </div>
                                            <div class="text-[10px] font-black text-slate-500 whitespace-nowrap">
                                                <span class="text-slate-800">{{ info.used }}</span> / {{ info.quota }}
                                            </div>
                                        </div>
                                        
                                        <div v-if="isProbation && !info.probation_allowed" class="absolute inset-0 bg-white/70 backdrop-blur-[4px] flex justify-center items-center rounded-2xl z-20 m-[-16px]">
                                            <span class="bg-white text-rose-600 px-3 py-1.5 rounded-full text-xs font-black shadow-lg border border-rose-100 flex items-center gap-1.5 transform -rotate-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                ล็อคช่วงทดลองงาน
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (The Action Form) -->
                    <div class="lg:col-span-8 xl:col-span-8">
                        <div class="bg-white/80 backdrop-blur-2xl rounded-3xl shadow-[0_8px_40px_-12px_rgba(0,0,0,0.1)] border border-white relative h-full flex flex-col overflow-hidden">
                            <!-- Subtle card top highlight -->
                            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500"></div>

                            <form @submit.prevent="submit" class="p-6 md:p-8 relative z-10 flex flex-col h-full gap-6">
                                
                                <div class="flex-1 space-y-6">
                                    
                                    <!-- Leave Type Selection -->
                                    <div>
                                        <label class="block font-black text-base text-slate-800 mb-3 flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs shadow-sm shadow-emerald-500/30">1</div>
                                            เลือกประเภทการลา <span class="text-rose-500 ml-1 text-sm">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 cursor-pointer">
                                            <label v-for="type in leaveTypes" :key="type.key" class="relative group h-full" :class="{'opacity-40 pointer-events-none': isProbation && stats.items[type.key] && !stats.items[type.key].probation_allowed}">
                                                <input type="radio" :value="type.key" v-model="form.leave_type" class="peer sr-only" name="leave_type">
                                                
                                                <div class="h-full px-3 py-3.5 cursor-pointer bg-slate-50/50 border-[2px] border-transparent rounded-2xl text-center group-hover:bg-emerald-50/50 peer-checked:bg-white peer-checked:border-emerald-500 peer-checked:shadow-[0_4px_20px_-6px_rgba(16,185,129,0.25)] transition-all duration-300 transform group-active:scale-95 flex flex-col items-center gap-2.5 relative z-10 overflow-hidden">
                                                    
                                                    <!-- Dynamic SVG Icons based on key -->
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-emerald-500 peer-checked:text-emerald-600 peer-checked:bg-emerald-50 transition-colors transform group-hover:scale-110 peer-checked:scale-110 duration-500">
                                                        <svg v-if="type.key === 'annual'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <svg v-else-if="type.key === 'sick'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                                        <svg v-else-if="type.key === 'personal'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                        <svg v-else-if="type.key === 'maternity'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                                        <svg v-else-if="type.key === 'ordination'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                                    </div>

                                                    <div class="font-black text-xs text-slate-600 peer-checked:text-emerald-800 transition-colors leading-tight">{{ type.name }}</div>
                                                </div>

                                                <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center opacity-0 scale-0 peer-checked:opacity-100 peer-checked:scale-100 transition-all duration-400 shadow-md z-20">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </label>
                                        </div>
                                        <InputError :message="form.errors.leave_type" class="mt-2" />
                                    </div>

                                    <!-- Blocked Warning Alert -->
                                    <div v-if="isBlocked" class="bg-rose-50/80 backdrop-blur border border-rose-100 rounded-2xl p-4 flex items-start gap-3 animate-fadeIn">
                                        <div class="bg-white text-rose-500 p-2 rounded-xl shrink-0 shadow-sm border border-rose-50">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-black text-rose-800 text-sm">ล็อคการใช้งานประเภทนี้</p>
                                            <p class="text-xs font-medium text-rose-600 mt-0.5">สิทธิ์นี้สงวนไว้สำหรับพนักงานที่ผ่านช่วงทดลองงาน (Probation) เท่านั้น</p>
                                        </div>
                                    </div>

                                    <!-- Date & Time Selection Area -->
                                    <div class="bg-emerald-50/30 rounded-[1.5rem] p-5 border border-white box-shadow-glass" :class="{'opacity-50 pointer-events-none grayscale': isBlocked}">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-5 gap-3">
                                            <label class="font-black text-base text-slate-800 flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs shadow-sm shadow-emerald-500/30">2</div>
                                                กำหนดช่วงเวลา <span class="text-rose-500 ml-1 text-sm">*</span>
                                            </label>
                                            <!-- Apple-style Segmented Control -->
                                            <div class="flex bg-slate-200/50 p-1 rounded-lg shadow-inner border border-slate-200/60 w-full sm:w-auto">
                                                <button type="button" @click="form.leave_format = 'daily'" :class="form.leave_format === 'daily' ? 'bg-white text-emerald-700 shadow-sm font-black ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700 font-bold'" class="w-1/2 sm:w-auto px-5 py-2 text-xs rounded-md transition-all duration-300">แบบเต็มวัน</button>
                                                <button type="button" @click="form.leave_format = 'hourly'" :class="form.leave_format === 'hourly' ? 'bg-white text-emerald-700 shadow-sm font-black ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700 font-bold'" class="w-1/2 sm:w-auto px-5 py-2 text-xs rounded-md transition-all duration-300">รายชั่วโมง</button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-500 uppercase ml-2">{{ form.leave_format === 'hourly' ? 'วันที่ขอลา' : 'วันที่เริ่มต้นการลา' }}</label>
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-emerald-400 group-focus-within:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </div>
                                                    <input type="date" v-model="form.start_date" class="w-full pl-10 pr-3 py-2.5 bg-white/80 hover:bg-white focus:bg-white rounded-xl border-transparent shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all cursor-pointer font-bold text-slate-700 ring-1 ring-slate-200 text-sm" required>
                                                </div>
                                                <InputError :message="form.errors.start_date" class="mt-1" />
                                            </div>
                                            
                                            <!-- Daily End Date -->
                                            <div v-if="form.leave_format === 'daily'" class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-500 uppercase ml-2">ถึงวันสุดท้าย</label>
                                                <div class="relative group">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-teal-400 group-focus-within:text-teal-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                    </div>
                                                    <input type="date" v-model="form.end_date" :min="form.start_date" class="w-full pl-10 pr-3 py-2.5 bg-white/80 hover:bg-white focus:bg-white rounded-xl border-transparent shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all cursor-pointer font-bold text-slate-700 ring-1 ring-slate-200 text-sm" required>
                                                </div>
                                                <InputError :message="form.errors.end_date" class="mt-1" />
                                            </div>

                                            <!-- Hourly Times -->
                                            <div v-if="form.leave_format === 'hourly'" class="grid grid-cols-2 gap-3">
                                                <div class="space-y-1.5">
                                                    <label class="text-[11px] font-bold text-slate-500 uppercase ml-2">ตั้งแต่เวลา</label>
                                                    <div class="relative group">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <svg class="h-4 w-4 text-emerald-400 group-focus-within:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        </div>
                                                        <input type="time" v-model="form.start_time" class="w-full pl-9 pr-3 py-2.5 bg-white/80 hover:bg-white focus:bg-white rounded-xl border-transparent shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all cursor-pointer font-bold text-slate-700 text-center ring-1 ring-slate-200 text-sm" required>
                                                    </div>
                                                    <InputError :message="form.errors.start_time" class="mt-1" />
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[11px] font-bold text-slate-500 uppercase ml-2">ถึงเวลา</label>
                                                    <div class="relative group">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <svg class="h-4 w-4 text-teal-400 group-focus-within:text-teal-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        </div>
                                                        <input type="time" v-model="form.end_time" class="w-full pl-9 pr-3 py-2.5 bg-white/80 hover:bg-white focus:bg-white rounded-xl border-transparent shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500/10 transition-all cursor-pointer font-bold text-slate-700 text-center ring-1 ring-slate-200 text-sm" required>
                                                    </div>
                                                    <InputError :message="form.errors.end_time" class="mt-1" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Total days summary banner -->
                                        <transition enter-active-class="transition ease-out duration-500 delay-100" enter-from-class="opacity-0 translate-y-4 scale-[0.98]" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition ease-in duration-300" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-[0.98]">
                                            <div v-if="totalDays > 0" class="mt-4 bg-slate-900 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between shadow-lg shadow-emerald-900/10 text-white relative overflow-hidden group">
                                                <div class="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-t from-emerald-500/30 to-transparent blur-2xl transform translate-x-8 translate-y-8 rounded-full pointer-events-none"></div>
                                                <div class="relative z-10 flex items-center gap-3 w-full sm:w-auto justify-center sm:justify-start mb-2 sm:mb-0">
                                                    <div class="bg-emerald-500/20 text-emerald-400 p-2.5 rounded-lg border border-emerald-400/20">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-[9px] font-bold tracking-wider text-slate-400 uppercase">Calculation</div>
                                                        <div class="text-sm font-bold text-white tracking-wide">ปริมาณวันลารวม (หักโควตา)</div>
                                                    </div>
                                                </div>
                                                <div class="relative z-10 text-2xl font-black flex items-baseline gap-2">
                                                    <span v-if="form.leave_format === 'hourly'" class="text-xs font-bold text-emerald-200 bg-white/10 px-2 py-0.5 rounded-lg backdrop-blur-md border border-white/5 mr-1.5">{{ totalHours }}</span>
                                                    {{ totalDays }} <span class="text-xs font-bold text-emerald-300 translate-y-[-1px]">วัน</span>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>

                                    <!-- 3. Resolution/Reason -->
                                    <div :class="{'opacity-50 pointer-events-none grayscale': isBlocked}">
                                        <label class="block font-black text-base text-slate-800 mb-3 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs shadow-sm shadow-emerald-500/30">3</div>
                                                คำอธิบายเพิ่มเติม
                                            </div>
                                            <span v-if="reasonRequired" class="text-rose-500 text-xs font-bold bg-rose-50 px-2 py-0.5 rounded-full">* จำเป็นต้องระบุ</span>
                                            <span v-else class="text-slate-400 font-bold text-[10px] bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">ทางเลือก</span>
                                        </label>
                                        <div class="relative">
                                            <textarea v-model="form.reason" rows="2" class="w-full rounded-2xl border-transparent ring-1 ring-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white p-4 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition-all resize-none text-slate-700 font-medium text-sm leading-relaxed placeholder:text-slate-400 placeholder:font-normal" :placeholder="reasonRequired ? 'พิมพ์ระบุสาเหตุที่ชัดเจนเพื่อประกอบการพิจารณาอนุมัติ...' : 'บันทึกเพิ่มเติม (ไม่บังคับ)'" :required="reasonRequired"></textarea>
                                        </div>
                                        <InputError :message="form.errors.reason" class="mt-1" />
                                    </div>

                                    <!-- 4. Attachment -->
                                    <div v-if="attachmentThreshold" class="space-y-3" :class="{'opacity-50 pointer-events-none grayscale': isBlocked}">
                                        <label class="block font-black text-base text-slate-800 mb-2 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs shadow-sm shadow-emerald-500/30">4</div>
                                                แนบเอกสารอ้างอิง
                                            </div>
                                            <span v-if="attachmentRequired" class="text-rose-500 text-xs font-bold bg-rose-50 px-2 py-0.5 rounded-full">* บังคับแนบ</span>
                                        </label>

                                        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2 h-0" enter-to-class="opacity-100 translate-y-0 h-auto" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 h-auto" leave-to-class="opacity-0 -translate-y-2 h-0">
                                            <div v-if="attachmentRequired" class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-3 flex items-start gap-3">
                                                <div class="bg-amber-100/80 p-2 rounded-xl text-amber-600 shrink-0 shadow-sm border border-amber-200/50">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-amber-950 font-black">จำเป็นต้องแนบหลักฐาน (เช่น ใบรับรองแพทย์)</p>
                                                    <p class="text-xs text-amber-700/80 mt-0.5 font-medium leading-relaxed">เนื่องจากการ{{ currentStats?.name }} ระบุไว้เกินกำหนด {{ attachmentThreshold }} วันขึ้นไป โปรดสแกนหรือถ่ายรูปเอกสารแนบ</p>
                                                </div>
                                            </div>
                                        </transition>

                                        <div class="relative group h-32">
                                            <div class="absolute inset-0 bg-slate-50/80 rounded-2xl border-2 border-dashed border-slate-300 group-hover:border-emerald-400 transition-colors pointer-events-none z-0 group-hover:bg-emerald-50/50"></div>
                                            <input ref="fileInputRef" type="file" @change="onFileChange" accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                            
                                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none z-0">
                                                <div class="w-12 h-12 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mb-2 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-md transition-all duration-300 transform group-hover:-translate-y-1">
                                                    <svg v-if="form.attachment" class="w-5 h-5 text-emerald-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    <svg v-else class="w-5 h-5 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                </div>
                                                <div class="text-sm font-black text-emerald-600 drop-shadow-sm px-4 text-center truncate w-full" v-if="form.attachment">{{ form.attachment.name }}</div>
                                                <div class="text-sm font-bold text-slate-500 group-hover:text-emerald-700 transition-colors" v-else>คลิกเพื่อเลือก หรือลากไฟล์มาวางตรงนี้</div>
                                                <div class="text-[10px] text-slate-400 mt-1 font-bold tracking-widest uppercase bg-white/60 px-2 py-0.5 rounded-md">JPG, PNG, PDF &mdash; Max 5MB</div>
                                            </div>
                                        </div>
                                        <InputError :message="form.errors.attachment" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Footer Action Buttons -->
                                <div class="mt-4 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-3 relative z-20">
                                    <Link :href="route('leave.index')" class="w-full sm:w-auto px-6 py-3 text-sm font-bold text-slate-500 bg-slate-50/50 hover:bg-slate-100 rounded-xl transition-colors text-center shrink-0">
                                        ยกเลิกและย้อนกลับ
                                    </Link>
                                    <button type="submit" :disabled="form.processing || isBlocked" class="w-full sm:w-auto group relative overflow-hidden inline-flex items-center justify-center gap-2 bg-slate-900 rounded-xl px-8 py-3 text-sm font-black tracking-wide text-white shadow-[0_4px_15px_-4px_rgba(0,0,0,0.5)] hover:shadow-emerald-500/25 hover:bg-emerald-600 transition-all duration-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out z-0 rounded-xl"></div>
                                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white relative z-10" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <svg v-else class="w-4 h-4 relative z-10 transform group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                        <span class="relative z-10">{{ form.processing ? 'กำลังส่งข้อมูล...' : 'ส่งใบขอลา (Submit)' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.box-shadow-glass {
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.02), inset 0 0 0 1px rgba(255, 255, 255, 0.4);
}

/* Custom Scrollbar for quota list if too many elements */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #cbd5e1;
}
</style>
