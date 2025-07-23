<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps<{
  members: { id: number; name: string; photo: string | null; created_at: string }[]
}>()

const showModal = ref(false)

const form = useForm({
  name: '',
  photo: null as File | null,
})

function addMember() {
  form.post(route('member.store'), {
    forceFormData: true,
    onSuccess: () => {
      showModal.value = false
      form.reset()
      router.reload({ only: ['members'] }) // refresh list
    },
  })
}

function closeModal() {
  showModal.value = false
  form.reset()
}

function handleFileUpload(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files?.length) {
    form.photo = target.files[0]
  }
}

function deleteMember(id: number) {
  if (confirm('Yakin ingin menghapus member ini?')) {
    router.delete(route('member.destroy', id), {
      onSuccess: () => router.reload({ only: ['members'] }),
    })
  }
}
</script>

<template>
  <Head title="Member" />
  <AppLayout :breadcrumbs="[{ title: 'Member', href: '/member' }]">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-6 px-6">
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
              <th class="py-3 px-6 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr
              v-for="member in members"
              :key="member.id"
              class="border-t border-gray-200 hover:bg-gray-50"
            >
              <td class="py-3 px-6">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                  <img
                    v-if="member.photo"
                    :src="`/storage/${member.photo}`"
                    alt="Profile photo"
                    class="w-10 h-10 rounded-full object-cover"
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
              <td class="py-3 px-6 text-[#113f67]">
                {{ new Date(member.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
              </td>
              <td class="py-3 px-6 text-right">
                <button @click="deleteMember(member.id)" class="text-red-600 hover:underline text-sm">
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
      <div class="bg-white rounded-xl p-6 shadow-lg w-full max-w-md">
        <h2 class="text-lg font-semibold text-[#113f67] mb-4">Add New Member</h2>
        <form @submit.prevent="addMember" class="flex flex-col gap-4" enctype="multipart/form-data">
          <input
            v-model="form.name"
            type="text"
            placeholder="Full Name"
            class="rounded-lg border border-gray-300 px-4 py-2"
            required
          />
          <input
            type="file"
            @change="handleFileUpload"
            class="rounded-lg border border-gray-300 px-4 py-2"
            accept="image/*"
          />
          <div class="flex justify-end gap-2 mt-2">
            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-[#113f67] text-white rounded-lg text-sm" :disabled="form.processing">
              Add
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
