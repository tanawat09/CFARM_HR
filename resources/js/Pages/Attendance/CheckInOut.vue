<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useGeolocation } from '@/Composables/useGeolocation';
import CameraCapture from '@/Components/CameraCapture.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    mode: String, // 'checkin', 'checkout', 'completed'
    employee: Object,
    today_attendance: Object
});

const { coords, error: geoError, getLocation } = useGeolocation();

const isLoading = ref(false);
const errorMessage = ref(null);
const cameraRef = ref(null);

const form = useForm({
    latitude: null,
    longitude: null,
    accuracy: null,
    photo: null, 
    note: '',
    outside_reason: ''
});

onMounted(() => {
    if (props.mode !== 'completed') {
        fetchLocation();
    }
});

const fetchLocation = async () => {
    isLoading.value = true;
    errorMessage.value = null;
    try {
        const loc = await getLocation();
        form.latitude = loc.latitude;
        form.longitude = loc.longitude;
        form.accuracy = loc.accuracy;
    } catch (err) {
        errorMessage.value = err;
    } finally {
        isLoading.value = false;
    }
};

// Photo capture logic is handled by <CameraCapture>

const submit = () => {
    if (!form.latitude || !form.longitude) {
        errorMessage.value = 'ไม่พบข้อมูลตำแหน่ง (GPS) กรุณารอสักครู่หรือเปิดรับตำแหน่ง';
        return;
    }
    if (!form.photo) {
        errorMessage.value = 'กรุณาถ่ายรูปเพื่อยืนยันตัวตน';
        return;
    }

    const routeName = props.mode === 'checkin' ? route('attendance.checkIn') : route('attendance.checkOut');
    
    form.post(routeName, {
        preserveScroll: true,
        onSuccess: () => {
            if (cameraRef.value) cameraRef.value.stopCamera();
        },
        onError: (errors) => {
            if(errors.message) errorMessage.value = errors.message;
        }
    });
};
</script>

<template>
    <Head title="ลงเวลา" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ mode === 'checkin' ? 'ลงเวลาเข้างาน (Check-in)' : (mode === 'checkout' ? 'ลงเวลาออกงาน (Check-out)' : 'เสร็จสิ้นการลงเวลาวันนี้') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="glass sm:rounded-3xl overflow-hidden shadow-2xl border border-white/60">
                    
                    <div v-if="mode === 'completed'" class="p-6 text-center text-green-600">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-xl font-bold">คุณทำรายการสำเร็จแล้วสำหรับวันนี้</h3>
                    </div>

                    <div v-else class="p-6">
                        <!-- Error Message -->
                        <div v-if="errorMessage || geoError || form.errors.message" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {{ errorMessage || geoError || form.errors.message }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <!-- Camera Column -->
                            <div class="flex flex-col items-center">
                                <div class="bg-indigo-50 border border-indigo-100 rounded-full px-4 py-1.5 mb-4 text-indigo-700 font-semibold text-sm flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    ยืนยันตัวตนด้วยใบหน้า
                                </div>
                                
                                <!-- Camera component -->
                                <CameraCapture v-model="form.photo" ref="cameraRef" />
                                <InputError :message="form.errors.photo" class="mt-3 text-center" />
                            </div>

                            <!-- Form Column -->
                            <div class="flex flex-col justify-between">
                                <div>
                                    <div class="bg-indigo-50 border border-indigo-100 rounded-full px-4 py-1.5 mb-4 text-indigo-700 font-semibold text-sm flex items-center gap-2 shadow-sm inline-flex">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        ข้อมูลการระบุตำแหน่ง
                                    </div>
                                    
                                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 mb-6 text-sm">
                                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-50 cursor-default">
                                            <span class="text-slate-500 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div> ละติจูด</span>
                                            <span class="font-mono text-slate-800 font-bold bg-slate-100 px-2 py-1 rounded">{{ form.latitude ? form.latitude.toFixed(6) : 'รอพิกัด...' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-50 cursor-default">
                                            <span class="text-slate-500 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-blue-500"></div> ลองจิจูด</span>
                                            <span class="font-mono text-slate-800 font-bold bg-slate-100 px-2 py-1 rounded">{{ form.longitude ? form.longitude.toFixed(6) : 'รอพิกัด...' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-500 flex items-center gap-2"><div class="w-2 h-2 rounded-full bg-emerald-500"></div> ความแม่นยำ</span>
                                            <span>
                                                <span v-if="form.accuracy" class="px-2 py-1 rounded font-bold" :class="form.accuracy < 30 ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-700'">
                                                    ± {{ Math.round(form.accuracy) }} เมตร
                                                </span>
                                                <span v-else class="font-mono text-slate-800 font-bold bg-slate-100 px-2 py-1 rounded">รอพิกัด...</span>
                                            </span>
                                        </div>
                                        
                                        <button @click="fetchLocation" type="button" class="mt-4 w-full flex items-center justify-center gap-2 py-2 text-sm font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition" :disabled="isLoading">
                                            <svg v-if="isLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            {{ isLoading ? 'กำลังดึงตำแหน่ง...' : 'รีเฟรชตำแหน่ง GPS' }}
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.latitude" class="mt-1 mb-4" />

                                    <!-- If Employee is not full geofence -->
                                    <div class="mb-5 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                        <label class="block font-bold text-sm text-slate-700 mb-2">เหตุผลกรณีอยู่นอกพื้นที่ <span class="text-slate-400 font-normal">(ถ้ามี)</span></label>
                                        <textarea v-model="form.outside_reason" class="border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 rounded-xl shadow-sm w-full transition" rows="2" placeholder="เช่น ขออนุญาตแวะซื้อวัสดุอุปกรณ์"></textarea>
                                        <InputError :message="form.errors.outside_reason" class="mt-1" />
                                    </div>

                                    <div class="mb-2 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                                        <label class="block font-bold text-sm text-slate-700 mb-2">หมายเหตุการปฏิบัติงาน</label>
                                        <input v-model="form.note" type="text" class="border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 rounded-xl shadow-sm w-full transition" placeholder="รายงานสั้นๆ (Optional)">
                                        <InputError :message="form.errors.note" class="mt-1" />
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <button 
                                        @click="submit" 
                                        class="w-full flex justify-center items-center gap-3 py-5 text-xl tracking-wide font-extrabold shadow-xl rounded-2xl transition-all transform hover:-translate-y-1 hover:shadow-2xl disabled:opacity-50 disabled:hover:translate-y-0 disabled:cursor-not-allowed"
                                        :class="mode === 'checkin' ? 'bg-gradient-to-br from-indigo-600 to-indigo-800 text-white' : 'bg-gradient-to-br from-rose-500 to-red-700 text-white'"
                                        :disabled="form.processing || isLoading || !form.photo"
                                    >
                                        <svg v-if="form.processing" class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ form.processing ? 'กำลังบันทึก...' : (mode === 'checkin' ? '🚀 บันทึกเข้างาน' : '🏁 บันทึกออกงาน') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
