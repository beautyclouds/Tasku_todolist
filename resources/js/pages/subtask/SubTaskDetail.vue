<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps<{
    subtask: any,
    card: any,
    collaborators: any[],
}>();

// Kembali ke halaman detail card
const goBack = () => {
    router.visit(`/board/${props.card.id}`);
};
</script>

<template>
    <Head title="Subtask Detail" />

    <AppLayout>
        <div class="p-6 border rounded-xl shadow-md bg-white dark:bg-gray-800 space-y-6">

            <!-- 🔙 Tombol Kembali -->
            <button
                @click="goBack"
                class="inline-flex items-center text-3xl font-extrabold text-[#033A63] transition hover:text-[#022d4d] dark:text-gray-200 dark:hover:text-gray-200"
            >
                ←
            </button>

            <!-- 📝 Judul SubTask -->
            <h1 class="flex items-center gap-2 text-2xl font-bold text-[#033A63] dark:text-gray-100">
                📝 {{ props.subtask.name }}
            </h1>

            <!-- 📅 Tanggal Dibuat -->
            <p class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                🗓️ Dibuat pada:
                <span class="font-semibold">{{ props.subtask.created_at }}</span>
            </p>

            <!-- 👥 Collaborators -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">👥 Collaborators:</h2>

                <!-- List Collaborator -->
                <div v-if="props.collaborators.length > 0" class="flex flex-wrap items-center gap-3">
                    <div
                        v-for="col in props.collaborators"
                        :key="col.id"
                        class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-black"
                    >
                        <img
                            :src="col.avatar 
                                ? `/storage/${col.avatar}`
                                : `https://ui-avatars.com/api/?name=${col.name}`"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-black"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ col.name }}
                        </span>
                    </div>
                </div>

                <p v-else class="text-gray-500 dark:text-gray-400 text-sm">
                    Tidak ada collaborator.
                </p>
            </div>

            <!-- 📝 Deskripsi Subtask -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📘 Deskripsi:</h2>

                <div class="rounded-lg border p-4 bg-gray-50 dark:bg-black dark:border-gray-700">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                        {{ props.subtask.description ?? 'Belum ada deskripsi.' }}
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
