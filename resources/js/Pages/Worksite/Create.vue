<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import GoogleMap from '@/Components/GoogleMap.vue';

const props = defineProps({
    branches: Array
});

const form = useForm({
    name: '',
    code: '',
    address: '',
    branch_id: '',
    latitude: '',
    longitude: '',
    radius_meters: 100, // default
});

const isLocating = ref(false);

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert("เบราว์เซอร์ของคุณไม่รองรับการระบุตำแหน่ง GPS");
        return;
    }
    
    isLocating.value = true;
    
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = Number(position.coords.latitude.toFixed(6));
            form.longitude = Number(position.coords.longitude.toFixed(6));
            form.clearErrors('latitude');
            form.clearErrors('longitude');
            isLocating.value = false;
        },
        (error) => {
            isLocating.value = false;
            let msg = "เกิดข้อผิดพลาดในการดึงตำแหน่ง";
            if(error.code === 1) msg = "กรุณาอนุญาตการเข้าถึงตำแหน่ง GPS (Allow Location)";
            alert(msg);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

const submit = () => {
    form.post(route('worksites.store'));
};
</script>

<template>
    <Head title="เพิ่มสาขาใหม่" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('worksites.index')" class="text-slate-400 hover:text-indigo-600 transition bg-white p-2 rounded-xl shadow-sm border border-slate-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight tracking-tight">🏢 เพิ่มสาขาใหม่</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 relative">
                    <!-- Map Preview Banner Area -->
                    <div class="h-64 bg-slate-100 relative">
                        <GoogleMap 
                            :latitude="form.latitude ? Number(form.latitude) : null" 
                            :longitude="form.longitude ? Number(form.longitude) : null" 
                            class="!rounded-none !border-0"
                        />
                        <div class="absolute inset-0 ring-1 ring-inset ring-black/10 pointer-events-none"></div>
                        
                        <!-- Floating Location Hint -->
                        <div v-if="!form.latitude" class="absolute bottom-4 inset-x-0 flex justify-center pointer-events-none">
                            <span class="bg-indigo-900/80 backdrop-blur text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg">
                                แผนที่จะแสดงผลเมื่อระบุพิกัด GPS
                            </span>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <!-- General Info Section -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 border-b pb-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    ข้อมูลทั่วไป
                                </h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-bold text-sm text-slate-700 mb-2">รหัสสาขา <span class="text-rose-500">*</span></label>
                                        <input type="text" v-model="form.code" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase" placeholder="e.g. WS-001" required>
                                        <InputError :message="form.errors.code" class="mt-2" />
                                    </div>
                                    <div>
                                        <label class="block font-bold text-sm text-slate-700 mb-2">สาขาที่สังกัด</label>
                                        <select v-model="form.branch_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">อิสระ (ไม่ขึ้นกับสาขา)</option>
                                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.branch_id" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-sm text-slate-700 mb-2">ชื่อสาขา <span class="text-rose-500">*</span></label>
                                    <input type="text" v-model="form.name" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="ระบุชื่อสาขา..." required>
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <div>
                                    <label class="block font-bold text-sm text-slate-700 mb-2">ที่อยู่ / รายละเอียด</label>
                                    <textarea v-model="form.address" rows="3" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none" placeholder="รายละเอียดที่ตั้ง..."></textarea>
                                    <InputError :message="form.errors.address" class="mt-2" />
                                </div>
                            </div>

                            <!-- Geofencing Section -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-extrabold text-slate-800 flex items-center gap-2 border-b pb-2">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    พิกัดและพื้นที่ลงเวลา (Geofence)
                                </h3>

                                <div class="bg-indigo-50 rounded-2xl p-5 border border-indigo-100 relative overflow-hidden">
                                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-200 rounded-full mix-blend-multiply blur-xl opacity-30"></div>
                                    
                                    <div class="flex justify-between items-center mb-4">
                                        <label class="font-bold text-sm text-indigo-900">ค้นหาพิกัดด่วน</label>
                                        <button type="button" @click="getCurrentLocation" :disabled="isLocating" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition shadow-sm disabled:opacity-50">
                                            <svg v-if="isLocating" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ isLocating ? 'กำลังค้นหา...' : 'ดึงตำแหน่งปัจจุบัน' }}
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold text-xs text-indigo-800 mb-1">ละติจูด (Latitude) <span class="text-rose-500">*</span></label>
                                            <input type="number" step="any" v-model="form.latitude" class="w-full rounded-xl border-indigo-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="13.7563" required>
                                            <InputError :message="form.errors.latitude" class="mt-1" />
                                        </div>
                                        <div>
                                            <label class="block font-semibold text-xs text-indigo-800 mb-1">ลองจิจูด (Longitude) <span class="text-rose-500">*</span></label>
                                            <input type="number" step="any" v-model="form.longitude" class="w-full rounded-xl border-indigo-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="100.5018" required>
                                            <InputError :message="form.errors.longitude" class="mt-1" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-sm text-slate-700 mb-2">ระยะรัศมีการลงเวลา (เมตร) <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-4">
                                        <input type="range" v-model="form.radius_meters" min="10" max="1000" step="10" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                        <div class="bg-slate-100 border border-slate-200 text-slate-800 font-bold px-4 py-2 rounded-xl min-w-[100px] text-center">
                                            {{ form.radius_meters }} m
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-2">ระยะห่างจากพิกัด GPS ศูนย์กลางที่อนุญาตให้พนักงานลงเวลาได้</p>
                                    <InputError :message="form.errors.radius_meters" class="mt-2" />
                                </div>

                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-slate-100 flex items-center justify-end gap-4 bg-slate-50/50 -mx-8 -mb-8 p-6 px-8 relative z-10">
                            <Link :href="route('worksites.index')" class="text-slate-500 font-bold hover:text-slate-800 transition px-4 py-2">
                                ยกเลิก
                            </Link>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 rounded-xl px-8 py-3 text-sm font-bold text-white shadow-lg hover:shadow-indigo-500/30 transition transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ form.processing ? 'กำลังบันทึก...' : 'บันทึกสาขาใหม่' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
