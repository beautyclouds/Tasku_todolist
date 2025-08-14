<script setup lang="ts">
defineProps<{
    title: string;
    id: number;
    subtasks: { id: number; title: string; is_completed: boolean }[];
}>();
</script>

<template>
    <div
        class="cursor-pointer rounded-md bg-[#e1f0fc] p-3 shadow-sm transition hover:bg-blue-100 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
    >
        <p class="font-semibold mb-2">{{ title }}</p>

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
