<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, defineProps } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Board', href: '/board' }]

const props = defineProps<{
  cards: {
    id: number
    title: string
    deadline: string
    priority: string
    status: string
  }[]
}>()

const pendingTasks = computed(() => props.cards.filter(card => card.status === 'Pending'))
const onProgressTasks = computed(() => props.cards.filter(card => card.status === 'In Progress'))
const completedTasks = computed(() => props.cards.filter(card => card.status === 'Completed'))

// Modal
const showModal = ref(false)
const isEditing = ref(false)
const newCard = ref({
  id: null,
  title: '',
  deadline: '',
  priority: 'Normal',
})

// Open Create
const openCreateModal = () => {
  isEditing.value = false
  newCard.value = { id: null, title: '', deadline: '', priority: 'Normal' }
  showModal.value = true
}

// Open Edit
const openEditModal = (card: any) => {
  isEditing.value = true
  newCard.value = { ...card }
  showModal.value = true
}

// Submit (Create/Update)
const submitCard = () => {
  if (isEditing.value) {
    router.put(`/board/${newCard.value.id}`, newCard.value, {
      onSuccess: () => {
        showModal.value = false
        router.reload()
      },
    })
  } else {
    router.post('/board/create', newCard.value, {
      onSuccess: () => {
        showModal.value = false
        router.reload()
      },
    })
  }
}

// Delete
const deleteCard = (id: number) => {
  if (confirm('Are you sure you want to delete this card?')) {
    router.delete(`/board/${id}`, {
      onSuccess: () => router.reload()
    })
  }
}

// Go to detail
const goToCard = (id: number) => {
  router.get(`/board/${id}`)
}
</script>

<template>
  <Head title="Board" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Top -->
    <div class="flex justify-between items-center mb-6 px-6">
      <div class="relative w-full max-w-8xl">
        <input
          type="text"
          placeholder="Search..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#113f67]"
        />
      </div>
      <div class="ml-4 flex items-center">
        <img src="https://ui-avatars.com/api/?name=Indah&background=113f67&color=fff&size=40" alt="Profile" class="w-10 h-10 rounded-full object-cover shadow"/>
      </div>
    </div>

    <!-- Content -->
    <div class="flex flex-col gap-4 p-6 bg-[#f2f2f2] min-h-screen">
      <div class="flex justify-between items-center">
        <input
          type="text"
          placeholder="Search task"
          class="rounded-xl px-4 py-3 border border-gray-300 bg-white shadow-sm w-full max-w-xl"
        />
        <button @click="openCreateModal" class="ml-4 bg-[#033A63] text-white px-6 py-2 rounded-xl shadow-md">
          + Create New
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <template v-for="(section, index) in [
          { label: '🟤 Pending', items: pendingTasks, color: 'text-gray-700', bg: 'bg-[#e1f0fc]' },
          { label: '🟡 In Progress', items: onProgressTasks, color: 'text-yellow-600', bg: 'bg-yellow-100' },
          { label: '🟢 Completed', items: completedTasks, color: 'text-green-600', bg: 'bg-green-100' },
        ]" :key="index">
          <div class="bg-white rounded-xl p-4 shadow-md h-fit">
            <div class="flex items-center justify-between mb-4">
              <span class="font-semibold text-sm" :class="section.color">{{ section.label }}</span>
              <button class="text-xl font-bold text-gray-500">⋯</button>
            </div>
            <div class="flex flex-col gap-3">
              <div
                v-for="task in section.items"
                :key="task.id"
                :class="[section.bg, 'rounded-md p-3 shadow-sm hover:bg-opacity-80 transition']"
              >
                <p class="font-semibold mb-2 cursor-pointer" @click="goToCard(task.id)">{{ task.title }}</p>
                <div class="text-sm text-gray-600 mb-1">📅 {{ task.deadline }}</div>
                <div class="text-xs text-gray-500 mb-2">{{ task.priority }} Priority</div>
                <div class="flex gap-2">
                  <button @click="openEditModal(task)" class="text-xs text-blue-600 hover:underline">Edit</button>
                  <button @click="deleteCard(task.id)" class="text-xs text-red-600 hover:underline">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
        <h2 class="text-lg font-semibold mb-4 text-[#033A63]">{{ isEditing ? 'Edit Card' : 'Create New Board' }}</h2>
        <div class="space-y-3">
          <input v-model="newCard.title" type="text" placeholder="Title" class="w-full border px-3 py-2 rounded" />
          <input v-model="newCard.deadline" type="datetime-local" class="w-full border px-3 py-2 rounded" />
          <select v-model="newCard.priority" class="w-full border px-3 py-2 rounded">
            <option value="Low">Low</option>
            <option value="Normal">Normal</option>
            <option value="High">High</option>
          </select>
        </div>
        <div class="flex justify-end mt-4 gap-2">
          <button @click="showModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button @click="submitCard" class="px-4 py-2 bg-[#033A63] text-white rounded">
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
