<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    members: { id: number; name: string; photo: string | null; created_at: string }[];
}>();

const showModal = ref(false);

const form = useForm({
    name: '',
    photo: null as File | null,
});

function addMember() {
    form.post(route('member.store'), {
        forceFormData: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            router.reload({ only: ['members'] }); // refresh list
        },
    });
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function handleFileUpload(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.photo = target.files[0];
    }
}

function deleteMember(id: number) {
    if (confirm('Yakin ingin menghapus member ini?')) {
        router.delete(route('member.destroy', id), {
            onSuccess: () => router.reload({ only: ['members'] }),
        });
    }
}
</script>

<template>
    <Head title="Member" />
    <AppLayout :breadcrumbs="[{ title: 'Member', href: '/member' }]">
        <div class="flex min-h-screen flex-col gap-6 bg-[#f2f2f2] p-6 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <input
                    type="text"
                    placeholder="Search members"
                    class="w-full max-w-xl rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                />
                <button
                    @click="showModal = true"
                    class="ml-4 rounded-xl bg-[#033A63] px-5 py-2 text-white shadow-md dark:bg-[#34699A] dark:hover:bg-blue-500"
                >
                    + Add Member
                </button>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-md dark:bg-[#333333]">
                <table class="w-full text-left">
                    <thead class="border-b border-gray-300 bg-blue-100 text-[#113f67] dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        <tr class="text-sm font-semibold">
                            <th class="px-6 py-3">Photo</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Joined</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm dark:text-gray-300">
                        <tr
                            v-for="member in members"
                            :key="member.id"
                            class="border-t border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-600"
                        >
                            <td class="px-6 py-3">
                                <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-gray-200 dark:bg-gray-500">
                                    <img
                                        v-if="member.photo"
                                        :src="`/storage/${member.photo}`"
                                        alt="Profile photo"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <svg v-else class="h-6 w-6 text-gray-400 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"
                                        />
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-3 font-medium text-[#113f67] dark:text-gray-200">{{ member.name }}</td>
                            <td class="px-6 py-3 text-[#113f67] dark:text-gray-200">{{ member.id }}</td>
                            <td class="px-6 py-3 text-[#113f67] dark:text-gray-200">
                                {{ new Date(member.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button
                                    @click="deleteMember(member.id)"
                                    class="text-sm text-red-600 hover:underline dark:text-red-400 dark:hover:text-red-300"
                                >
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-[#113f67] dark:text-gray-200">Add New Member</h2>
                <form @submit.prevent="addMember" class="flex flex-col gap-4" enctype="multipart/form-data">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Full Name"
                        class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        required
                    />
                    <input
                        type="file"
                        @change="handleFileUpload"
                        class="rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        accept="image/*"
                    />
                    <div class="mt-2 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-sm dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-[#113f67] px-4 py-2 text-sm text-white dark:bg-blue-600 dark:hover:bg-blue-500"
                            :disabled="form.processing"
                        >
                            Add
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
