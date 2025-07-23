// ✅ Show.vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, defineProps, ref } from 'vue'

const props = defineProps<{
  card: {
    id: number
    title: string
    deadline: string
    priority: string
    status: string
    tasks: { id: number; name: string; is_done: boolean }[]
    members?: { id: number; name: string; photo: string | null }[]
  }
  allMembers: {
    id: number
    name: string
    photo: string | null
  }[]
}>()

const form = useForm({ name: '' })
const inviteForm = useForm({ name: '' })

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

const toggleTask = (taskId: number) => {
  router.post(`/board/tasks/${taskId}/toggle`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['card'] })
    }
  })
}

const inviteMember = () => {
  if (!inviteForm.name.trim()) return
  inviteForm.post(`/board/${props.card.id}/invite`, {
    preserveScroll: true,
    onSuccess: () => {
      inviteForm.reset()
      router.reload({ only: ['card'] })
    },
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
    <div class="p-6 w-full mx-auto bg-white rounded-xl shadow-md mt-6">

      <!-- 🔙 Tombol Kembali -->
      <div class="mb-4">
        <a
          href="/board"
          class="inline-flex items-center font-extrabold text-3xl text-[#033A63]  hover:text-[#022d4d] transition"
        >
          ←
        </a>
      </div>

      <h1 class="text-2xl font-bold mb-2 text-[#033A63] flex items-center gap-2">
        📋 {{ props.card.title }}
      </h1>

      <p class="text-sm text-gray-600 mb-1 flex items-center gap-2">
        🗓️ {{ formattedDeadline }}
      </p>
      <p class="text-sm text-gray-500 mb-4">
        Priority: {{ props.card.priority }} | Status: {{ props.card.status }}
      </p>

      <!-- ✅ Show Members -->
      <div v-if="props.card.members && props.card.members.length" class="mb-4">
        <h2 class="font-semibold mb-2">👥 Members:</h2>
        <div class="flex -space-x-2">
          <img
            v-for="member in props.card.members"
            :key="member.id"
            :src="member.photo ? `/storage/${member.photo}` : `https://ui-avatars.com/api/?name=${member.name}`"
            :alt="member.name"
            class="w-8 h-8 rounded-full border-2 border-white shadow"
          />
        </div>
      </div>

      <!-- ✅ Invite Member -->
      <div class="mb-6">
        <h2 class="font-semibold mb-2">➕ Invite Member</h2>
        <form @submit.prevent="inviteMember" class="flex gap-2 items-center">
          <select v-model="inviteForm.name" class="border px-3 py-2 rounded w-full">
            <option value="" disabled>Select a member...</option>
            <option
              v-for="member in props.allMembers"
              :key="member.id"
              :value="member.name"
              :disabled="props.card.members?.some(m => m.id === member.id)"
            >
              {{ member.name }}
            </option>
          </select>
          <button class="bg-[#033A63] text-white px-4 py-2 rounded hover:bg-[#022d4d] transition">
            Invite
          </button>
        </form>
      </div>

      <div>
        <h2 class="font-semibold mb-2">📌 Sub Tasks:</h2>
        <ul class="mb-4 text-gray-800 space-y-2">
          <li v-for="task in props.card.tasks" :key="task.id" class="flex items-center gap-2">
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
          <button class="bg-[#033A63] text-white px-4 py-2 rounded hover:bg-[#022d4d] transition">
            Add
          </button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
