<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const showSecret = ref(false);
const showToken = ref(false);

const props = defineProps({
    channelSecret: String,
    channelAccessToken: String,
    webhookUrl: String,
});

const form = useForm({
    channel_secret: props.channelSecret || '',
    channel_access_token: props.channelAccessToken || '',
});

const showSuccess = ref(false);

const submit = () => {
    form.post(route('settings.line.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true;
            setTimeout(() => showSuccess.value = false, 3000);
        },
    });
};

const copyWebhook = async () => {
    try {
        await navigator.clipboard.writeText(props.webhookUrl);
        alert('คัดลอก Webhook URL เรียบร้อยแล้ว');
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
};
</script>

<template>
    <Head title="ตั้งค่า LINE" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-emerald-800 leading-tight">
                ตั้งค่าการเชื่อมต่อ LINE (LINE Messaging API)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Success Message -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-[-10px]"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 "
                >
                    <div v-if="showSuccess" class="p-4 bg-emerald-100/80 backdrop-blur-md border border-emerald-200 rounded-2xl shadow-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-emerald-800 font-medium tracking-wide">บันทึกข้อมูลเรียบร้อยแล้ว</span>
                    </div>
                </Transition>

                <!-- Configuration Form -->
                <div class="bg-white/70 backdrop-blur-lg overflow-hidden shadow-xl sm:rounded-3xl border border-white/50">
                    <div class="p-8 sm:p-10">
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-slate-800 bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                                ข้อมูล Channel
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                นำข้อมูลเหล่านี้มาจากหน้าเว็บ LINE Developers ของคุณ
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Channel Secret -->
                            <div>
                                <label for="channel_secret" class="block text-sm font-semibold text-slate-700">
                                    Channel Secret
                                </label>
                                <div class="mt-2 relative">
                                    <input
                                        id="channel_secret"
                                        :type="showSecret ? 'text' : 'password'"
                                        v-model="form.channel_secret"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-white/50 backdrop-blur-sm transition-all duration-200 pr-12"
                                        placeholder="ใส่ Channel Secret เช่น 4a9c61d8b..."
                                    />
                                    <button type="button" @click="showSecret = !showSecret" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-emerald-600 transition-colors">
                                        <svg v-if="!showSecret" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                    <p v-if="form.errors.channel_secret" class="mt-2 text-sm text-red-600">{{ form.errors.channel_secret }}</p>
                                </div>
                            </div>

                            <!-- Channel Access Token -->
                            <div>
                                <label for="channel_access_token" class="block text-sm font-semibold text-slate-700">
                                    Channel Access Token (Long-lived)
                                </label>
                                <div class="mt-2 relative">
                                    <div v-if="!showToken" class="block w-full rounded-xl border border-slate-200 shadow-sm bg-white/50 backdrop-blur-sm p-3 min-h-[6rem] font-mono text-sm text-slate-600 break-all cursor-text" @click="showToken = true" style="letter-spacing: 2px;">
                                        {{ form.channel_access_token ? '•'.repeat(Math.min(form.channel_access_token.length, 80)) : '' }}
                                        <span v-if="!form.channel_access_token" class="text-slate-400" style="letter-spacing: normal;">ใส่ Access Token แบบตัวยาวๆ ที่สร้างใหม่</span>
                                    </div>
                                    <textarea
                                        v-else
                                        id="channel_access_token"
                                        v-model="form.channel_access_token"
                                        rows="4"
                                        class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-white/50 backdrop-blur-sm transition-all duration-200"
                                        placeholder="ใส่ Access Token แบบตัวยาวๆ ที่สร้างใหม่"
                                    ></textarea>
                                    <button type="button" @click="showToken = !showToken" class="absolute top-3 right-3 flex items-center p-1 text-slate-400 hover:text-emerald-600 transition-colors">
                                        <svg v-if="!showToken" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                    </button>
                                    <p v-if="form.errors.channel_access_token" class="mt-2 text-sm text-red-600">{{ form.errors.channel_access_token }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-emerald-600 hover:to-teal-600 focus:bg-emerald-600 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-300 shadow-md hover:shadow-lg disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    บันทึกการตั้งค่า
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Webhook URL Info Box -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 overflow-hidden shadow-xl sm:rounded-3xl border border-slate-700 relative">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-emerald-500/10 blur-2xl"></div>
                    <div class="p-8 sm:p-10 relative z-10">
                        <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Webhook URL สำหรับตั้งค่าใน LINE
                        </h3>
                        <p class="text-slate-300 text-sm mb-4">
                            นำ URL นี้ไปใส่ในช่อง Webhook URL ในหน้า LINE Developers
                        </p>
                        <div class="flex items-center gap-3 bg-slate-950/50 p-4 rounded-xl border border-slate-700 shadow-inner">
                            <code class="text-emerald-400 text-sm flex-1 break-all select-all">{{ props.webhookUrl }}</code>
                            <button
                                @click="copyWebhook"
                                class="shrink-0 inline-flex items-center p-2 bg-slate-700/50 hover:bg-emerald-500 text-slate-300 hover:text-white rounded-lg transition-colors border border-slate-600 hover:border-emerald-400"
                                title="คัดลอก"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
