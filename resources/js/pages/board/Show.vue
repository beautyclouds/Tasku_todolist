<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, defineProps } from 'vue'

const props = defineProps<{
  card: {
    id: number
    title: string
    deadline: string
    priority: string
    status: string
    tasks: { id: number; name: string; is_done: boolean }[]
  }
}>()

const form = useForm({ name: '' })

const addSubTask = () => {
  if (form.name.trim() === '') return
  form.post(`/board/${props.card.id}/tasks`, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      router.reload({ only: ['card'] })
    },
  })
}

// Toggle checkbox
const toggleTask = (taskId: number) => {
  router.post(`/board/tasks/${taskId}/toggle`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['card'] })
    }
  })
}

const formattedDeadline = computed(() => {
  const date = new Date(props.card.deadline)
  return date.toLocaleString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})
</script>

<template>
  <Head title="Card Detail" />

  <AppLayout>
    <div class="p-6 max-w-3xl mx-auto bg-white rounded-xl shadow-md mt-6">
      <h1 class="text-2xl font-bold mb-2 text-[#033A63] flex items-center gap-2">
        📋 {{ props.card.title }}
      </h1>

      <p class="text-sm text-gray-600 mb-1 flex items-center gap-2">
        🗓️ {{ formattedDeadline }}
      </p>
      <p class="text-sm text-gray-500 mb-4">Priority: {{ props.card.priority }} | Status: {{ props.card.status }}</p>

      <div>
        <h2 class="font-semibold mb-2">Sub Tasks:</h2>
        <ul class="mb-4 text-gray-800 space-y-2">
          <li
            v-for="task in props.card.tasks"
            :key="task.id"
            class="flex items-center gap-2"
          >
            <input
              type="checkbox"
              :checked="task.is_done"
              @change="toggleTask(task.id)"
              class="accent-[#033A63] w-4 h-4"
            />
            <span :class="{ 'line-through text-gray-500': task.is_done }">{{ task.name }}</span>
          </li>
        </ul>

        <form @submit.prevent="addSubTask" class="flex gap-2">
          <input
            v-model="form.name"
            type="text"
            placeholder="Add new task..."
            class="border px-3 py-2 rounded w-full border-gray-300 focus:ring-2 focus:ring-[#033A63] focus:outline-none"
          />
          <button
            class="bg-[#033A63] text-white px-4 py-2 rounded hover:bg-[#022d4d] transition"
          >
            Add
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
