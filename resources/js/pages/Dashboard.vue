<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

const totalTasks = ref(10);
const completedTasks = ref(6);
const notStartedTasks = ref(2);
const pendingTasks = computed(() => totalTasks.value - completedTasks.value);

const currentDate = ref(new Date());
const today = new Date();

const isToday = (day: number) => {
    return (
        day === today.getDate() &&
        currentDate.value.getMonth() === today.getMonth() &&
        currentDate.value.getFullYear() === today.getFullYear()
    );
};

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    return new Date(year, month + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1).getDay();
});

const prevMonth = () => {
    const date = new Date(currentDate.value);
    date.setMonth(date.getMonth() - 1);
    currentDate.value = date;
};

const nextMonth = () => {
    const date = new Date(currentDate.value);
    date.setMonth(date.getMonth() + 1);
    currentDate.value = date;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Top Bar -->
        <div class="flex justify-between items-center mb-6 px-6">
            <!-- Search -->
            <div class="relative w-full max-w-8xl">
                <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#113f67]"/>
                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </div>

            <!-- Profile -->
            <div class="ml-4 flex items-center">
                <img src="https://ui-avatars.com/api/?name=Indah&background=113f67&color=fff&size=40" alt="Profile" class="w-10 h-10 rounded-full object-cover shadow"/>
            </div>
        </div>

        <!-- Dashboard Min Content -->
        <div class="bg-gray-100 p-6 font-['Poppins'] grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- KIRI -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Task Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-6 rounded-xl shadow text-center">
                        <h3 class="text-lg font-semibold text-[#113f67] mb-2">Not Started</h3>
                        <p class="text-3xl font-bold text-gray-700">{{ notStartedTasks }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow text-center">
                        <h3 class="text-lg font-semibold text-[#113f67] mb-2">In Progress</h3>
                        <p class="text-3xl font-bold text-gray-700">{{ pendingTasks }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow text-center">
                        <h3 class="text-lg font-semibold text-[#113f67] mb-2">Completed</h3>
                        <p class="text-3xl font-bold text-gray-700">{{ completedTasks }}</p>
                    </div>
                </div>

                <!-- Overview -->
                <div class="bg-white p-6 rounded-xl shadow border-b">
                    <h3 class="text-lg font-semibold text-[#113f67] mb-4">Overview</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="bg-blue-100 p-4 rounded-xl">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">Project Management</h4>
                                <span class="text-gray-500">•••</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>👥</span>
                                <span>Alice, Bob</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>📅</span>
                                <span>1 December 2025, 09.00</span>
                            </div>
                            <div class="w-full bg-gray-300 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#113f67] h-2 rounded-full" style="width: 70%"></div>
                            </div>
                        </div>

                        <div class="bg-blue-100 p-4 rounded-xl">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">UI Redesign</h4>
                                <span class="text-gray-500">•••</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>👥</span>
                                <span>Charlie, Diana</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>📅</span>
                                <span>5 December 2025, 14.00</span>
                            </div>
                            <div class="w-full bg-gray-300 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#113f67] h-2 rounded-full" style="width: 40%"></div>
                            </div>
                        </div>

                        <div class="bg-blue-100 p-4 rounded-xl">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-semibold text-gray-800">Testing & QA</h4>
                                <span class="text-gray-500">•••</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>👥</span>
                                <span>Eva, Felix</span>
                            </div>
                            <div class="flex gap-2 items-center text-xs text-gray-600 mb-2">
                                <span>📅</span>
                                <span>10 December 2025, 10.00</span>
                            </div>
                            <div class="w-full bg-gray-300 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#113f67] h-2 rounded-full" style="width: 90%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h3 class="text-lg font-semibold text-[#113f67] mb-4">Team Members</h3>
                    <ul class="text-sm text-gray-700 space-y-1">
                        <li>👤 Alice</li>
                        <li>👤 Bob</li>
                        <li>👤 Charlie</li>
                    </ul>
                </div>
            </div>

            <!-- KANAN -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <!-- Calendar -->
                <div class="bg-white p-6 rounded-xl shadow border-b">
                    <h3 class="text-md font-bold text-[#113f67] mb-4">Upcoming Tasks</h3>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <button class="text-sm px-2 py-1 rounded hover:bg-gray-300" @click="prevMonth">←</button>
                            <p class="text-sm font-semibold">
                                {{ monthNames[currentDate.getMonth()] }} {{ currentDate.getFullYear() }}
                            </p>
                            <button class="text-sm px-2 py-1 rounded hover:bg-gray-300" @click="nextMonth">→</button>
                        </div>
                        <div class="grid grid-cols-7 text-center text-xs text-gray-500 mb-2">
                            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                        </div>
                        <div class="grid grid-cols-7 text-center text-sm gap-y-1">
                            <template v-for="n in firstDayOfMonth" :key="'pad-' + n">
                                <span></span>
                            </template>
                            <template v-for="day in daysInMonth" :key="'day-' + day">
                                <span class="w-8 h-8 flex items-center justify-center mx-auto rounded-full cursor-pointer" :class="isToday(day) ? 'bg-[#113f67] text-white font-semibold' : 'text-gray-800 hover:bg-[#113f67] hover:text-white'">
                                    {{ day }}
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Today Schedule -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h3 class="text-md font-bold text-[#113f67] mb-4">Today’s Schedule</h3>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li>🕘 09:00 - Team Standup</li>
                        <li>🕚 11:00 - Design Review</li>
                        <li>🕒 15:00 - Code Deployment</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
