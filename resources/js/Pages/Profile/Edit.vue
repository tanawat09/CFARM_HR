<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const tab = ref('profile'); // profile | password | danger
const page = usePage();

const user = computed(() => page.props.auth.user);
const displayName = computed(() => {
    const u = user.value;
    if (u.employee) return `${u.employee.first_name} ${u.employee.last_name}`;
    return u.username || u.email;
});
const initial = computed(() => (displayName.value || '?').charAt(0).toUpperCase());
</script>

<template>
    <Head title="โปรไฟล์" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200/60">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">บัญชีของฉัน</h2>
                    <p class="text-xs text-slate-500 font-medium">จัดการข้อมูลบัญชี ความปลอดภัย และการตั้งค่าส่วนตัว</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Profile header card -->
                <div class="relative bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Cover gradient -->
                    <div class="h-28 bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-600 relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
                        <div class="absolute -bottom-6 left-8 w-28 h-28 bg-white/10 rounded-full"></div>
                        <div class="absolute top-8 right-8 w-20 h-20 bg-white/10 rounded-full blur-2xl"></div>
                    </div>

                    <div class="px-6 pb-6 -mt-12 relative">
                        <div class="flex items-end justify-between flex-wrap gap-4">
                            <div class="flex items-end gap-4">
                                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-3xl shadow-xl ring-4 ring-white">
                                    {{ initial }}
                                </div>
                                <div class="pb-1">
                                    <h3 class="font-extrabold text-xl text-slate-800">{{ displayName }}</h3>
                                    <p class="text-sm text-slate-500 font-medium flex items-center gap-1.5 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        {{ user.email }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-[11px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                            {{ user.role?.name || user.role }}
                                        </span>
                                        <span v-if="user.employee?.employee_code" class="inline-flex items-center gap-1 bg-slate-100 text-slate-600 text-[11px] font-mono font-bold px-2.5 py-1 rounded-full">
                                            {{ user.employee.employee_code }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-1.5 inline-flex gap-1 text-sm font-bold flex-wrap">
                    <button @click="tab = 'profile'" :class="tab === 'profile' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-200/60' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        ข้อมูลส่วนตัว
                    </button>
                    <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-200/60' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        เปลี่ยนรหัสผ่าน
                    </button>
                    <button @click="tab = 'danger'" :class="tab === 'danger' ? 'bg-gradient-to-r from-rose-600 to-pink-600 text-white shadow-lg shadow-rose-200/60' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Danger Zone
                    </button>
                </div>

                <!-- Panels -->
                <transition mode="out-in" enter-active-class="transition duration-200" enter-from-class="opacity-0 translate-y-2">
                    <div :key="tab">
                        <div v-if="tab === 'profile'" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                                class="max-w-xl"
                            />
                        </div>
                        <div v-else-if="tab === 'password'" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
                            <UpdatePasswordForm class="max-w-xl" />
                        </div>
                        <div v-else class="bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-200 rounded-2xl shadow-sm p-6 sm:p-8">
                            <div class="flex items-start gap-3 mb-5">
                                <div class="w-10 h-10 rounded-xl bg-rose-500 flex items-center justify-center text-white shadow-lg shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-lg text-rose-700">โซนอันตราย</h3>
                                    <p class="text-xs font-semibold text-rose-600/80">การกระทำต่อไปนี้ไม่สามารถย้อนกลับได้</p>
                                </div>
                            </div>
                            <DeleteUserForm class="max-w-xl" />
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
