<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { defineProps, ref, watch } from 'vue'

const props = defineProps({
  users: Array,
  filters: Object,
})

// reactive state
const search = ref(props.filters?.search || '')

// 🔍 Jalankan search otomatis (realtime)
let searchTimeout
watch(search, (value) => {
  if (searchTimeout) clearTimeout(searchTimeout)
  // beri sedikit delay (debounce 400ms)
  searchTimeout = setTimeout(() => {
    router.get(
      route('member.index'),
      { search: value },
      { preserveState: true, replace: true }
    )
  }, 400)
})

// 🔁 Reset search (hanya muncul jika kolom terisi)
function resetSearch() {
  search.value = ''
  router.get(route('member.index'), {}, { preserveState: false, replace: true })
}

// 🗑️ Hapus user
function deleteUser(id) {
  if (confirm('Yakin ingin menghapus user ini?')) {
    router.delete(route('member.destroy', id))
  }
}

const breadcrumbs = [{ title: 'Member', href: '/member' }]

const viewBoards = (id) => {
  router.get('/boards', { member_id: id })
}
</script>

<template>
    <Head title="Member" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 min-h-screen bg-[#f2f2f2] dark:bg-gray-800">
            <h1 class="text-xl font-bold mb-4">Daftar Member</h1>

            <!-- Filter -->
            <div class="flex items-center gap-2 mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama member..."
                    class="w-full max-w-sm border border-gray-300 bg-white rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200 dark:bg-black dark:border-gray-800"
                />

                <!-- Tombol reset muncul hanya jika search terisi -->
                <button
                    v-if="search"
                    @click="resetSearch"
                    class="bg-gray-400 text-white hover:bg-gray-500 px-4 py-2 rounded-md dark:bg-gray-700 dark:text-white dark:hover:bg-gray-500"
                >
                    Reset
                </button>
            </div>

            <!-- Daftar Member -->
            <div
                v-for="(user, index) in props.users"
                :key="user.id"
                class="bg-white flex items-center justify-between p-4 mb-3 rounded-lg shadow-sm dark:bg-black"
            >
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ user.name }}
                    </p>
                    <p class="text-gray-500 text-sm dark:text-gray-300">
                        {{ user.email }} •
                        <span
                            :class="user.status === 'Aktif'
                            ? 'text-green-600 font-semibold'
                            : 'text-red-600 font-semibold'"
                        >
                            {{ user.status }}
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="deleteUser(user.id)"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                    >
                        Remove
                    </button>
                </div>
            </div>

            <!-- Jika tidak ada hasil -->
            <div
                v-if="props.users.length === 0"
                class="text-gray-500 italic text-center mt-10"
            >
                Tidak ada member yang ditemukan
            </div>
        </div>
    </AppLayout>
</template>
