<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Member',
    href: '/member',
  },
]

// Data Member
const members = ref<
  { name: string; id: string; joined: string; photo: string }[]
>([])

// Modal & Form
const showModal = ref(false)
const newMember = ref({
  name: '',
  photoFile: null as File | null,
  photoUrl: '',
})

// Fungsi Buat ID Angka
function generateId() {
  return String(members.value.length + 1)
}

// Format tanggal hari ini
function formatDate() {
  const now = new Date()
  const options: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }
  return now.toLocaleDateString('en-GB', options)
}

// Tambah Member
function addMember() {
  members.value.push({
    name: newMember.value.name,
    photo: newMember.value.photoUrl || '',
    id: generateId(),
    joined: formatDate(),
  })
  closeModal()
}

// Ambil file foto
function handlePhotoUpload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) {
    newMember.value.photoFile = file
    newMember.value.photoUrl = URL.createObjectURL(file)
  }
}

function closeModal() {
  showModal.value = false
  newMember.value = { name: '', photoFile: null, photoUrl: '' }
}
</script>

<template>
  <Head title="Member" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-6 px-6">
      <!-- Search -->
      <div class="relative w-full max-w-8xl">
        <input
          type="text"
          placeholder="Search..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#113f67]"
        />
        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
      </div>

      <!-- Profile -->
      <div class="ml-4 flex items-center">
        <img src="https://ui-avatars.com/api/?name=Indah&background=113f67&color=fff&size=40" alt="Profile" class="w-10 h-10 rounded-full object-cover shadow" />
      </div>
    </div>

    <div class="flex flex-col gap-6 p-6 bg-[#f2f2f2] min-h-screen">
      <!-- Search + Add Button -->
      <div class="flex justify-between items-center">
        <input
          type="text"
          placeholder="Search members"
          class="rounded-xl px-4 py-3 border border-gray-300 bg-white shadow-sm w-full max-w-xl"
        />
        <button @click="showModal = true" class="ml-4 bg-[#033A63] text-white px-5 py-2 rounded-xl shadow-md">
          + Add Member
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl overflow-hidden shadow-md">
        <table class="w-full text-left">
          <thead class="bg-blue-100 text-[#113f67] border-b border-gray-300">
            <tr class="text-sm font-semibold">
              <th class="py-3 px-6">Photo</th>
              <th class="py-3 px-6">Name</th>
              <th class="py-3 px-6">ID</th>
              <th class="py-3 px-6">Joined</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr
              v-for="(member, index) in members"
              :key="index"
              class="border-t border-gray-200 hover:bg-gray-50"
            >
              <td class="py-3 px-6">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                  <img
                    v-if="member.photo"
                    :src="member.photo"
                    alt="Photo"
                    class="w-full h-full object-cover"
                  />
                  <svg
                    v-else
                    class="w-6 h-6 text-gray-400"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"
                    />
                  </svg>
                </div>
              </td>
              <td class="py-3 px-6 text-[#113f67] font-medium">{{ member.name }}</td>
              <td class="py-3 px-6 text-[#113f67]">{{ member.id }}</td>
              <td class="py-3 px-6 text-[#113f67]">{{ member.joined }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
      <div class="bg-white rounded-xl p-6 shadow-lg w-full max-w-md">
        <h2 class="text-lg font-semibold text-[#113f67] mb-4">Add New Member</h2>
        <form @submit.prevent="addMember" class="flex flex-col gap-4">
          <input
            v-model="newMember.name"
            type="text"
            placeholder="Full Name"
            class="rounded-lg border border-gray-300 px-4 py-2"
            required
          />
          <input
            type="file"
            @change="handlePhotoUpload"
            class="rounded-lg border border-gray-300 px-4 py-2"
            accept="image/*"
          />
          <div class="flex justify-end gap-2 mt-2">
            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-[#113f67] text-white rounded-lg text-sm">
              Add
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
