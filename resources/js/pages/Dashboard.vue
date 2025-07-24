<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
  pendingCount: number;
  inProgressCount: number;
  completedCount: number;
  recentBoards: { id: number; title: string; deadline: string; members: any[] }[];
  recentMembers: {
      photo: any; id: number; name: string 
}[];
  deadlines: { id: number; title: string; deadline: string }[];
  todayTasks: { id: number; title: string; deadline: string }[];
}>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
];

// Calendar
const currentDate = ref(new Date());
const today = new Date();

const isToday = (day: number) =>
  day === today.getDate() &&
  currentDate.value.getMonth() === today.getMonth() &&
  currentDate.value.getFullYear() === today.getFullYear();

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

// Format deadline to easily find it by date
const deadlineMap = computed(() => {
  const map: Record<string, { id: number; title: string }[]> = {};
  props.deadlines.forEach((d) => {
    const date = new Date(d.deadline).toDateString();
    if (!map[date]) map[date] = [];
    map[date].push({ id: d.id, title: d.title });
  });
  return map;
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Summary Cards -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-lg font-semibold text-[#113f67] mb-2">Pending</h3>
                <p class="text-3xl font-bold">{{ props.pendingCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-lg font-semibold text-[#113f67] mb-2">In Progress</h3>
                <p class="text-3xl font-bold">{{ props.inProgressCount }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow text-center">
                <h3 class="text-lg font-semibold text-[#113f67] mb-2">Completed</h3>
                <p class="text-3xl font-bold">{{ props.completedCount }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 font-['Poppins']">
        <!-- LEFT -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                <!-- Overview -->
                <div class="bg-white p-6 rounded-xl shadow border-b">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[#113f67]">Overview</h3>
                        <a href="/board" class="text-sm text-blue-600 hover:underline">See more</a>
                    </div>
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div
                            v-for="board in props.recentBoards"
                            :key="board.id"
                            class="bg-[#a5bbc9] p-4 rounded-xl shadow hover:shadow-md transition"
                        >
                            <h4 class="text-sm font-semibold text-[#113f67] truncate mb-1">
                                📌 {{ board.title }}
                            </h4>
                            <p class="text-xs text-gray-700 mb-1">
                                📅 {{ new Date(board.deadline).toLocaleDateString() }}
                            </p>
                            <div class="flex items-center gap-2 text-xs text-gray-700 flex-wrap">
                                👥
                                <span
                                    v-for="(member, index) in board.members.slice(0, 3)"
                                    :key="index"
                                    class="bg-white px-2 py-1 rounded-full border text-gray-700"
                                >
                                    {{ member.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white p-6 rounded-xl shadow border-b mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-[#113f67]">Team Members</h3>
                        <a href="/members" class="text-sm text-blue-600 hover:underline">See more</a>
                    </div>
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div
                            v-for="member in props.recentMembers"
                            :key="member.id"
                            class="flex items-center gap-3 bg-[#a5bbc9] p-3 rounded-xl shadow hover:shadow-md transition"
                        >
                            <img
                                v-if="member.photo"
                                :src="`/storage/${member.photo}`"
                                alt="Profile photo"
                                class="w-15 h-15 rounded-full object-cover"
                            />
                            <div v-else class="w-15 h-15 rounded-full bg-white flex items-center justify-center border">
                                <svg
                                    class="w-6 h-6 text-gray-400"
                                    fill="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[#113f67] truncate">
                                    {{ member.name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <!-- Calendar -->
                <div class="bg-white p-6 rounded-xl shadow">
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
                                <div class="relative group">
                                    <span
                                    class="w-8 h-8 flex items-center justify-center mx-auto rounded-full cursor-pointer transition"
                                    :class="[
                                        isToday(day) ? 'bg-[#113f67] text-white font-semibold' : 'text-gray-800 hover:bg-[#113f67] hover:text-white',
                                        deadlineMap[new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()] ? 'ring-2 ring-[#113f67]' : ''
                                    ]">
                                        {{ day }}
                                    </span>
                                    <div
                                    v-if="deadlineMap[new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()]"
                                    class="absolute z-10 left-1/2 transform -translate-x-1/2 mt-1 px-2 py-1 text-xs bg-white border rounded shadow-lg w-48 hidden group-hover:block"
                                    >
                                        <div v-for="task in deadlineMap[new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()]" :key="task.id">
                                            📌 {{ task.title }}
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Today Schedule -->
                <div class="bg-white p-6 rounded-xl shadow">
                    <h3 class="text-md font-bold text-[#113f67] mb-4">Today’s Schedule</h3>
                    <ul class="text-sm text-gray-700 space-y-2">
                        <li v-if="props.todayTasks.length === 0">✅ No tasks due today.</li>
                        <li v-for="task in props.todayTasks" :key="task.id">🗓️ {{ task.title }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
