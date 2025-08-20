<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  cards: Array,
});
</script>

<template>
    <AppLayout>
        <Head title="History" />

        <div class="p-6 space-y-6 min-h-screen bg-[#f2f2f2]">
            <h2 class="text-2xl font-bold">History</h2>

            <!-- Card List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="card in cards"
                    :key="card.id"
                    class="p-5 bg-white rounded-xl shadow-md border hover:shadow-lg transition hover:-translate-y-1"
                >
                    <!-- Card Title -->
                    <h3 class="text-lg font-semibold mb-3">{{ card.title }}</h3>

                    <!-- Info Bar: Deadline & Closed At -->
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Deadline: {{ card.deadline }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 7h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Closed: {{ card.closed_at }}</span>
                        </div>
                    </div>

                    <!-- Members -->
                    <div v-if="card.members" class="mb-2 flex items-center gap-2">
                        <span class="text-xs font-semibold dark:text-gray-300">Members:</span>
                        <div class="flex -space-x-2">
                            <img
                                v-for="member in card.members"
                                :key="member.id"
                                :src="member.photo ? member.photo : `https://ui-avatars.com/api/?name=${member.name}`"
                                :alt="member.name"
                                class="h-6 w-6 rounded-full border-2 border-white shadow object-cover dark:border-gray-700"
                            />
                        </div>
                    </div>

                    <!-- Jika card tidak punya member, tetap tampil "Members: -" -->
                    <div v-else class="mb-2 flex items-center gap-2">
                        <span class="text-xs font-semibold dark:text-gray-300">Members:</span>
                        <span class="text-xs text-gray-400">-</span>
                    </div>

                    <!-- Lihat Detail -->
                    <Link
                        :href="`/history/${card.id}`"
                        class="inline-block text-sm font-medium text-blue-600 hover:underline"
                    >
                        Lihat Detail
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
