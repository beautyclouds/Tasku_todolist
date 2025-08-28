<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  card: Object,
});
</script>

<template>
  <AppLayout>
    <Head title="Detail History Card" />

    <div class="p-6 space-y-4">
      <!-- Header -->
      <div class="items-center justify-between">
        <h2 class="text-xl font-bold">Detail History Card</h2>
        <Link
          href="/history"
          class="text-gray-700 text-2xl font-bold hover:text-gray-900"
        >
          ←
      </Link>
      </div>

      <!-- Card Details -->
      <div class="p-6 border rounded-xl shadow-md bg-white dark:bg-gray-800 space-y-3">
        <p><strong>Judul:</strong> {{ card.title }}</p>
        <p><strong>Status:</strong> 
          <span 
            :class="{
              'text-yellow-600': card.status === 'In Progress',
              'text-green-600': card.status === 'Completed',
              'text-gray-700': card.status === 'Pending',
              'text-red-600': card.status === 'archived'
            }"
          >
            {{ card.status }}
          </span>
        </p>
        <p><strong>Deadline:</strong> {{ card.deadline }}</p>
        <p><strong>Closed At:</strong> {{ card.closed_at ?? '-' }}</p>

        <!-- Members -->
        <div class="mt-4">
          <strong class="mb-2 block">Members:</strong>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="member in card.members"
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
            <span v-if="!card.members || card.members.length === 0" class="text-sm text-gray-400">-</span>
          </div>
        </div>

        <!-- Subtasks -->
        <div class="mt-6">
          <strong>Subtasks:</strong>
          <div class="mt-2 space-y-2">
            <div
              v-for="task in card.tasks"
              :key="task.id"
              class="flex items-start space-x-2 bg-gray-50 dark:bg-gray-700 p-2 rounded-lg"
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
    </div>
  </AppLayout>
</template>
