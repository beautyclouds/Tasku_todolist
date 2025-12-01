<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { defineProps, ref, watch } from 'vue'

const props = defineProps({
    users: Array,       // berisi shared_cards juga
    filters: Object,
})

// search state
const search = ref(props.filters?.search || '')

// debounce search
let searchTimeout
watch(search, (value) => {
    if (searchTimeout) clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        router.get(
            route('member.index'),
            { search: value },
            { preserveState: true, replace: true }
        )
    }, 400)
})

function resetSearch() {
    search.value = ''
    router.get(route('member.index'), {}, { preserveState: false, replace: true })
}

// accordion state
const openMember = ref(null)
function toggleMember(id) {
    openMember.value = openMember.value === id ? null : id
}

function timeAgo(dateString) {
    const date = new Date(dateString)

    // Jika bukan tanggal valid → return "-"
    if (isNaN(date)) return "-"

    const now = new Date()
    const diff = (now - date) / 1000

    if (diff < 60) return "baru saja"
    if (diff < 3600) return Math.floor(diff / 60) + " menit lalu"
    if (diff < 86400) return Math.floor(diff / 3600) + " jam lalu"
    return Math.floor(diff / 86400) + " hari lalu"
}

const breadcrumbs = [{ title: 'Member', href: '/member' }]
</script>


<template>
    <Head title="Member" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 min-h-screen bg-[#f2f2f2] dark:bg-gray-800">
            <h1 class="text-xl font-bold mb-4">Members</h1>

            <!-- Search Bar -->
             <div class="mb-4 flex items-center gap-2">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama member..."
                    class="w-full max-w-sm border border-gray-300 bg-white rounded-md px-3 py-2
                        focus:outline-none focus:ring focus:ring-blue-200 
                        dark:bg-black dark:border-gray-700"
                />

                <button
                    v-if="search"
                    @click="resetSearch"
                    class="bg-gray-400 text-white px-4 py-2 rounded-md 
                        hover:bg-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600"
                >
                    Reset
                </button>
            </div>

            <!-- Member List ------------------------------------------------ -->
            <div v-for="user in props.users" :key="user.id" class="mb-4 bg-white rounded-lg shadow-sm dark:bg-black">

                <!-- Header (collapsible) -->
                <button
                    @click="toggleMember(user.id)"
                    class="w-full flex justify-between items-center p-4 text-left"
                >
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white">{{ user.name }}</p>
                        <p class="text-gray-500 text-sm dark:text-gray-300">
                            ({{ user.shared_cards.length }} shared cards)
                        </p>
                    </div>

                    <span class="text-xl font-bold text-gray-700 dark:text-gray-300">
                        {{ openMember === user.id ? '▼' : '▸' }}
                    </span>
                </button>

                <!-- CONTENT ---------------------------------------------------- -->
                <transition name="fade">
                    <div v-if="openMember === user.id" class="border-t border-gray-200 dark:border-gray-700 p-4">

                        <!-- Jika shared cards = 0 -->
                        <div v-if="user.shared_cards.length === 0" class="text-gray-500 italic">
                            - Tidak ada card yang sama
                        </div>

                        <!-- List card bersama -->
                        <div 
                            v-for="card in user.shared_cards"
                            :key="card.id"
                            class="mb-4 pb-2 border-b last:border-none dark:border-gray-700"
                        >
                            <p class="font-semibold text-gray-800 dark:text-white">
                                • {{ card.title }}
                            </p>

                            <!-- status -->
                            <p class="text-sm mt-1">
                                <span
                                    v-if="card.status === 'Pending'"
                                    class="text-blue-600"
                                >
                                    [Pending] 🕗
                                </span>

                                <span
                                    v-if="card.status === 'In Progress'"
                                    class="text-yellow-600"
                                >
                                    [In Progress] ⏳
                                </span>

                                <span
                                    v-if="card.status === 'Completed'"
                                    class="text-green-600"
                                >
                                    [Completed] ✔️
                                </span>

                                <span
                                    v-if="card.status === 'Archived'"
                                    class="text-red-600"
                                >
                                    [Closed] ❌
                                </span>
                            </p>

                            <!-- Subtask -->
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Subtask: {{ card.done_subtasks }}/{{ card.total_subtasks }}
                                — Terakhir diupdate {{ timeAgo(card.updated_at) }}
                            </p>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- No results -->
            <div
                v-if="props.users.length === 0"
                class="text-gray-500 italic text-center mt-10"
            >
                Tidak ada member yang ditemukan
            </div>
        </div>
    </AppLayout>
</template>
