<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, defineProps, ref, watch } from 'vue';

const props = defineProps({
  users: Array,
})

function deleteUser(id) {
  if (confirm("Yakin ingin menghapus user ini?")) {
    router.delete(route('member.destroy', id))
  }
}

const breadcrumbs = [{ title: 'Member', href: '/member' }];
</script>

<template>
    <Head title="Member" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-xl font-bold mb-4">Daftar Member</h1>

            <table class="min-w-full border border-gray-200 rounded">
                <thead>
                    <tr class="bg-[#033A63] text-white">
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Nama</th>
                        <th class="border px-4 py-2">Email</th>
                        <th class="border px-4 py-2">Status</th>
                        <th class="border px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(user, index) in users" :key="user.id">
                        <td class="border px-4 py-2">{{ index + 1 }}</td>
                        <td class="border px-4 py-2">{{ user.name }}</td>
                        <td class="border px-4 py-2">{{ user.email }}</td>
                        <td class="border px-4 py-2">
                            <span
                                :class="user.status === 'Aktif' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
                            >
                                {{ user.status }}
                            </span>
                        </td>
                        <td class="border px-4 py-2 text-center">
                            <button
                                @click="deleteUser(user.id)"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
