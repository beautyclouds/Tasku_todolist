<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    pendingCount: number;
    inProgressCount: number;
    completedCount: number;
    recentBoards: { id: number; title: string; deadline: string; members: any[] }[];
    recentMembers: {
        photo: any;
        id: number;
        name: string;
    }[];
    deadlines: { id: number; title: string; deadline: string }[];
    todayTasks: { id: number; title: string; deadline: string }[];
}>();

const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }];

// Calendar
const currentDate = ref(new Date());
const today = new Date();

const isToday = (day: number) =>
    day === today.getDate() && currentDate.value.getMonth() === today.getMonth() && currentDate.value.getFullYear() === today.getFullYear();

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

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
        <div class="min-h-screen bg-[#f2f2f2] dark:bg-[#1a1a1a]">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3">
                <div class="rounded-xl bg-white p-6 text-center shadow dark:bg-[#333333] dark:text-white">
                    <h3 class="mb-2 text-lg font-semibold text-[#113f67] dark:text-gray-200">Pending</h3>
                    <p class="text-3xl font-bold">{{ props.pendingCount }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 text-center shadow dark:bg-[#333333] dark:text-white">
                    <h3 class="mb-2 text-lg font-semibold text-[#113f67] dark:text-gray-200">In Progress</h3>
                    <p class="text-3xl font-bold">{{ props.inProgressCount }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 text-center shadow dark:bg-[#333333] dark:text-white">
                    <h3 class="mb-2 text-lg font-semibold text-[#113f67] dark:text-gray-200">Completed</h3>
                    <p class="text-3xl font-bold">{{ props.completedCount }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-4 font-['Poppins'] lg:grid-cols-12">
                <!-- LEFT -->
                <div class="flex flex-col gap-6 lg:col-span-8">
                    <!-- Overview -->
                    <div class="rounded-xl border-b bg-white p-6 shadow dark:bg-[#333333] dark:text-white">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-[#113f67] dark:text-gray-200">Overview</h3>
                            <a href="/board" class="text-sm text-blue-600 hover:underline dark:text-gray-200">See more</a>
                        </div>

                        <div class="space-y-4">
                            <template v-if="props.recentBoards && props.recentBoards.length > 0">
                                <div
                                    v-for="board in props.recentBoards"
                                    :key="board.id"
                                    class="border-b pb-3 last:border-none"
                                >
                                    <!-- Judul + Deadline -->
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-semibold text-[#113f67]">📌 {{ board.title }}</h4>
                                        <span class="text-xs text-gray-500">─── {{ new Date(board.deadline).toLocaleDateString() }}</span>
                                    </div>

                                    <!-- Progress + Members -->
                                    <div class="mt-1 flex items-center justify-between text-sm text-gray-700">
                                        <span>
                                            Progress: 
                                            <span class="font-medium text-[#113f67]">
                                                {{ Math.floor(Math.random() * 101) }}%
                                            </span>
                                        </span>
                                        <span>
                                        👥
                                            <span
                                                v-for="(member, index) in board.members?.slice(0, 3)"
                                                :key="index"
                                                class="ml-1"
                                            >
                                                {{ member.name }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="text-gray-500 italic dark:text-gray-400">
                                    Belum ada papan yang baru-baru ini dilihat.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="flex flex-col gap-6 lg:col-span-4">
                    <!-- Calendar -->
                    <div class="rounded-xl bg-white p-6 shadow dark:bg-[#333333] dark:text-white">
                        <h3 class="text-md mb-4 font-bold text-[#113f67] dark:text-gray-200">Upcoming Tasks</h3>
                        <div class="rounded-lg bg-gray-100 p-4">
                            <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-800 dark:text-gray-200">
                                <div class="mb-2 flex items-center justify-between">
                                    <button
                                        class="rounded px-2 py-1 text-sm hover:bg-gray-300 dark:text-gray-300 dark:hover:bg-gray-700"
                                        @click="prevMonth"
                                    >
                                        ←
                                    </button>
                                    <p class="text-sm font-semibold dark:text-gray-100">
                                        {{ monthNames[currentDate.getMonth()] }} {{ currentDate.getFullYear() }}
                                    </p>
                                    <button
                                        class="rounded px-2 py-1 text-sm hover:bg-gray-300 dark:text-gray-300 dark:hover:bg-gray-700"
                                        @click="nextMonth"
                                    >
                                        →
                                    </button>
                                </div>
                                <div class="mb-2 grid grid-cols-7 text-center text-xs text-gray-500 dark:text-gray-400">
                                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                                </div>
                                <div class="grid grid-cols-7 gap-y-1 text-center text-sm">
                                    <template v-for="n in firstDayOfMonth" :key="'pad-' + n">
                                        <span></span>
                                    </template>
                                    <template v-for="day in daysInMonth" :key="'day-' + day">
                                        <div class="group relative">
                                            <span
                                                class="mx-auto flex h-8 w-8 cursor-pointer items-center justify-center rounded-full transition"
                                                :class="[
                                                    isToday(day)
                                                        ? 'bg-[#113f67] font-semibold text-white dark:bg-gray-600 dark:text-white' // UBAH INI
                                                        : 'text-gray-800 hover:bg-[#113f67] hover:text-white dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white', // UBAH INI
                                                    deadlineMap[new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()]
                                                        ? 'ring-2 ring-[#113f67] dark:ring-gray-500' // UBAH INI
                                                        : '',
                                                ]"
                                            >
                                                {{ day }}
                                            </span>
                                            <div
                                                v-if="deadlineMap[new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()]"
                                                class="absolute left-1/2 z-10 mt-1 hidden w-48 -translate-x-1/2 transform rounded border bg-white px-2 py-1 text-xs shadow-lg group-hover:block dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                            >
                                                >
                                                <div
                                                    v-for="task in deadlineMap[
                                                        new Date(currentDate.getFullYear(), currentDate.getMonth(), day).toDateString()
                                                    ]"
                                                    :key="task.id"
                                                >
                                                    📌 {{ task.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Today Schedule -->
                        <div class="rounded-xl bg-white p-6 shadow dark:bg-[#333333] dark:text-white">
                            <h3 class="text-md mb-4 font-bold text-[#113f67] dark:text-gray-200">Today’s Schedule</h3>
                            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <li v-if="props.todayTasks.length === 0">✅ No tasks due today.</li>
                                <li v-for="task in props.todayTasks" :key="task.id">🗓️ {{ task.title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
