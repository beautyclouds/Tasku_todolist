<script setup lang="ts">
defineProps<{
    title: string;
    id: number;
}>();
</script>

<template>
             
    <div
        class="cursor-pointer rounded-md bg-[#e1f0fc] p-3 shadow-sm transition hover:bg-blue-100 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
    >
                           
        <p class="font-semibold">{{ title }}</p>

           
    </div>
</template>
