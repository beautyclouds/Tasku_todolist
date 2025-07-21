<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];

// Sample task stats
const totalTasks = ref(10);
const completedTasks = ref(6);
const notStartedTasks = ref(2);
const pendingTasks = computed(() => totalTasks.value - completedTasks.value);

// Calendar Logic
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
    <div class="p-6 font-['Poppins'] grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- KIRI -->
      <div class="lg:col-span-8 flex flex-col gap-6">
        <!-- Task Cards -->
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
          <p class="text-sm text-gray-600">Project summary, progress chart, or other dynamic data can go here.</p>
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
                <span
                  class="w-8 h-8 flex items-center justify-center mx-auto rounded-full cursor-pointer"
                  :class="isToday(day) ? 'bg-[#113f67] text-white font-semibold' : 'text-gray-800 hover:bg-[#113f67] hover:text-white'">
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
