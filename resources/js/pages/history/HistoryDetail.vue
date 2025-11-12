<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  card: Object,
});
</script>

<template>
  <Head title="Detail History Card" />
  <AppLayout>
    <!-- Card Details -->
    <div class="p-6 border rounded-xl shadow-md bg-white dark:bg-[#1a1a1a] space-y-3">
      <!-- 🔙 Tombol Kembali -->
      <a
        href="/history"
        class="inline-flex items-center text-3xl font-extrabold text-[#033A63] transition hover:text-[#022d4d] dark:text-gray-200 dark:hover:text-blue-300"
      >
        ←
      </a>
      <h1
        class="mb-2 flex items-center gap-2 text-2xl font-bold text-[#033A63] dark:text-gray-100"
      >
        📋 {{ props.card.title }}
      </h1>
      <p>
        <span><strong>Priority:</strong> {{ props.card.priority }}</span> | 
        <span><strong>Status:</strong>{{ props.card.status }}</span>
      </p>

      <p><strong>🗓️ Deadline:</strong> {{ card.deadline }}</p>
      <p><strong>🗓️ Closed At:</strong> {{ card.closed_at ?? '-' }}</p>

      <!-- Owner -->
      <div class="mt-4">
        <strong class="mb-2 block">Owner:</strong>
        <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-700">
          <img
            :src="card.user?.photo ? card.user.photo : `https://ui-avatars.com/api/?name=${card.user?.name}`"
            :alt="card.user?.name"
            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-gray-700 object-cover"
          />
          <span class="text-sm font-medium dark:text-gray-200">
            {{ card.user?.name }} 
          </span>
        </div>
      </div>

      <!-- Collaborators -->
      <div class="mt-4">
        <strong class="mb-2 block">👥 Collaborators:</strong>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="member in card.collaborators"
            :key="member.id"
            class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-700"
          >
            <img
              :src="member.photo ? member.photo : `https://ui-avatars.com/api/?name=${member.name}`"
              :alt="member.name"
              class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-gray-700 object-cover"
            />
            <span class="text-sm font-medium dark:text-gray-200">{{ member.name }}</span>
          </div>
          <span v-if="!card.collaborators || card.collaborators.length === 0" class="text-sm text-gray-400">-</span>
        </div>
      </div>

      <!-- Subtasks -->
      <div class="mt-6">
        <strong>📌 Subtasks:</strong>
        <div class="mt-2 space-y-2">
          <div
            v-for="task in card.tasks"
            :key="task.id"
            class="flex items-start space-x-2 bg-gray-50 dark:bg-[#333333] p-2 rounded-lg"
          >
            <input type="checkbox" checked disabled class="mt-1" />
            <div>
              <p class="font-medium dark:text-gray-200">{{ task.name }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-300">{{ task.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
