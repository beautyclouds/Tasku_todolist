<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';

const props = defineProps<{
    subtask: any,
    card: any,
    collaborators: any[],
}>();

// 🔙 Kembali ke halaman detail card
const goBack = () => {
    router.visit(`/board/${props.card.id}`);
};

// 📝 Mode Edit
const isEditing = ref(false);
const editedName = ref(props.subtask.name);
const editedDescription = ref(props.subtask.description);

// 💾 Simpan perubahan
const saveEdit = () => {
    router.put(`/subtask/${props.subtask.id}`, {
        name: editedName.value,
        description: editedDescription.value,
    }, {
        onSuccess: () => {
            isEditing.value = false; // keluar dari mode edit
        }
    });
};


// ⏳ Format tanggal
const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head title="Subtask Detail" />

    <AppLayout>
        <div class="p-6 border rounded-xl shadow-md bg-white dark:bg-gray-800 space-y-6">

            <div class="grid grid-cols-3 items-center">
                <!-- Kiri: Tombol Back -->
                <div class="flex justify-start">
                    <button
                        @click="goBack"
                        class="text-3xl font-extrabold text-[#033A63] hover:text-[#022d4d] dark:text-white"
                    >
                        ←
                    </button>
                </div>

                <!-- Tengah: Judul Card (center) -->
                <div class="flex justify-center">
                    <h1 class="text-xl font-bold text-[#033A63] dark:text-gray-200">
                        📋 {{ props.card.title }}
                    </h1>
                </div>

                <!-- Kanan: Tombol Edit -->
                <div class="flex justify-end">
                    <button
                        v-if="!isEditing"
                        @click="isEditing = true"
                        class="text-sm px-4 py-2 rounded-lg bg-[#033A63] hover:bg-blue-800 text-white text-lg dark:bg-black dark:hover:bg-gray-600"
                        title="Edit"
                    >
                        Edit
                    </button>
                </div>
            </div>

            <!-- 📌 Judul Subtask -->
            <div>
                <!-- Normal Mode -->
                <h2 
                    v-if="!isEditing" 
                    class="text-2xl font-bold text-[#033A63] dark:text-gray-100"
                >
                    📌 {{ props.subtask.name }}
                </h2>

                <!-- Edit Mode -->
                <div v-else class="space-y-2">
                    <label class="font-semibold dark:text-gray-200">📌 Judul Subtask:</label>
                    <input
                        v-model="editedName"
                        class="w-full p-3 rounded-lg border dark:bg-black dark:text-white"
                        placeholder="Nama Subtask..."
                    />
                </div>
            </div>

            <!-- 📘 Deskripsi -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📘 Deskripsi:</h2>

                <!-- Normal mode -->
                <div v-if="!isEditing" class="p-4 bg-gray-50 dark:bg-black border rounded-lg">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                        {{ props.subtask.description ?? 'Belum ada deskripsi.' }}
                    </p>
                </div>

                <!-- Edit mode -->
                <div v-else class="space-y-4">
                    <!-- Deskripsi Edit -->
                    <textarea
                        v-model="editedDescription"
                        rows="6"
                        class="w-full p-3 rounded-lg border dark:bg-black dark:text-white"
                        placeholder="Tulis deskripsi..."
                    ></textarea>

                    <!-- Save & Cancel di bawah deskripsi -->
                    <div class="flex gap-3">
                        <button
                            @click="saveEdit"
                            class="px-4 py-2 bg-[#033A63] hover:bg-blue-900 text-white rounded-lg"
                        >
                            Save
                        </button>

                        <button
                            @click="isEditing = false"
                            class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded-lg"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- 🗓️ Tanggal dibuat -->
            <p class="text-sm mb-3 text-gray-600 dark:text-gray-300">
                🗓️ <strong>Dibuat:</strong> {{ formatDate(props.subtask.created_at) }}
            </p>

            <!-- 🕒 Deadline Card -->
            <p class="text-sm mb-3 text-gray-600 dark:text-gray-300">
                🕒 <strong>Deadline Card:</strong>
                {{ props.card.deadline ? formatDate(props.card.deadline) : 'Tidak ada deadline.' }}
            </p>

            <!-- 🔄 Terakhir diupdate -->
            <p class="text-sm text-gray-600 dark:text-gray-300">
                🔄 <strong>Update Terakhir:</strong>
                {{ props.subtask.updated_at ? formatDate(props.subtask.updated_at) : '-' }}
            </p>

            <!-- 👥 Collaborators -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">👥 Collaborators:</h2>
                <div class="flex flex-wrap gap-3">
                    <!-- 🔹 OWNER -->
                    <div
                        class="flex items-center gap-2 bg-gray-100 dark:bg-black px-3 py-1 rounded-full"
                    >
                        <img
                            :src="props.card.user?.avatar
                                ? `/storage/${props.card.user.avatar}`
                                : `https://ui-avatars.com/api/?name=${props.card.user?.name}`"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-black"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ props.card.user?.name }} (Owner)
                        </span>
                    </div>

                    <!-- 🔹 COLLABORATORS -->
                    <div
                        v-for="col in props.collaborators"
                        :key="col.id"
                        class="flex items-center gap-2 bg-gray-100 dark:bg-black px-3 py-1 rounded-full"
                    >
                        <img
                            :src="col.avatar
                                ? `/storage/${col.avatar}`
                                : `https://ui-avatars.com/api/?name=${col.name}`"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-black"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ col.name }}
                        </span>
                    </div>
                </div>

                <p v-if="!props.collaborators || props.collaborators.length === 0" 
                    class="text-gray-500 dark:text-gray-400 text-sm mt-2"
                >
                    Tidak ada collaborator.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
