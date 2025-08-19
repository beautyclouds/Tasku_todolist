<script setup lang="ts">
defineProps<{
    title: string;
    id: number;
    status: string; // ✅ tambahkan props status
    subtasks: { id: number; title: string; is_completed: boolean }[];
}>();
</script>

<template>
    <div
        class="relative cursor-pointer rounded-md bg-[#e1f0fc] p-3 shadow-sm transition hover:bg-blue-100 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
    >
        <!-- ✅ Badge status -->
        <span
            class="absolute top-2 right-2 px-2 py-1 rounded text-xs font-semibold"
            :class="{
                'bg-yellow-200 text-yellow-800': status === 'Pending',
                'bg-blue-200 text-blue-800': status === 'In Progress',
                'bg-green-200 text-green-800': status === 'Completed',
                'bg-gray-200 text-gray-800': !['Pending','In Progress','Completed'].includes(status),
            }"
        >
            {{ status }}
        </span>

        <!-- Judul card -->
        <p class="font-semibold mb-2">{{ title }}</p>

        <!-- List subtasks -->
        <ul class="text-sm space-y-1">
            <li
                v-for="subtask in subtasks"
                :key="subtask.id"
                class="flex items-center gap-2"
            >
                <input
                    type="checkbox"
                    :checked="subtask.is_completed"
                    disabled
                />
                <span :class="{ 'line-through text-gray-400': subtask.is_completed }">
                    {{ subtask.title }}
                </span>
            </li>
        </ul>
    </div>
</template>
