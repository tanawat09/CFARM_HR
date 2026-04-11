<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="เข้าสู่ระบบ" />

        <div v-if="status" class="mb-6 flex items-center gap-3 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-700">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            {{ status }}
        </div>

        <div class="mb-8 text-center">
            <div class="inline-flex w-16 h-16 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 items-center justify-center shadow-xl shadow-emerald-200/60 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-3xl font-black tracking-tight text-slate-800">
                ยินดีต้อนรับ
            </h2>
            <p class="mt-1 text-sm font-medium text-slate-500">
                เข้าสู่ระบบเพื่อใช้งาน CFARM HR Dashboard
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block font-bold text-xs text-slate-500 uppercase tracking-wider mb-2">อีเมล</label>
                <div class="relative">
                    <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                    <input
                        id="email"
                        type="email"
                        class="w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-semibold bg-slate-50/60 transition"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@cfarm.com"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block font-bold text-xs text-slate-500 uppercase tracking-wider">รหัสผ่าน</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition"
                    >
                        ลืมรหัสผ่าน?
                    </Link>
                </div>
                <div class="relative">
                    <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full pl-11 pr-11 py-3 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-semibold bg-slate-50/60 transition"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                        <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L15 15m0 0l6 6m-6-6l4.243-4.243" /></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2 cursor-pointer group">
                <div class="relative">
                    <input type="checkbox" v-model="form.remember" class="sr-only peer">
                    <div class="w-10 h-6 bg-slate-300 rounded-full peer-checked:bg-gradient-to-r peer-checked:from-emerald-500 peer-checked:to-teal-500 transition"></div>
                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                </div>
                <span class="text-sm font-semibold text-slate-600 group-hover:text-slate-800 transition">จดจำฉันไว้</span>
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-emerald-300/50 hover:shadow-emerald-400/60 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                {{ form.processing ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
            </button>
        </form>
    </GuestLayout>
</template>
