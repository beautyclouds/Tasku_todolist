<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, defineProps, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Board', href: '/board' }];

const props = defineProps<{
    cards: {
        id: number;
        title: string;
        deadline: string;
        priority: string;
        status: string;
        members?: { id: number; name: string; photo: string | null }[];
        subtasks?: { id: number; title: string; is_completed: boolean }[];
    }[];
}>();

const pendingTasks = computed(() => props.cards.filter((card) => card.status === 'Pending'));
const onProgressTasks = computed(() => props.cards.filter((card) => card.status === 'In Progress'));
const completedTasks = computed(() => props.cards.filter((card) => card.status === 'Completed'));

const showModal = ref(false);
const isEditing = ref(false);
const newCard = ref({
    id: null as number | null,
    title: '',
    deadline: '',
    priority: 'Normal',
});

// Validasi form supaya tombol Save hanya aktif kalau title dan deadline diisi
const isFormValid = computed(() => {
    return newCard.value.title.trim() !== '' && newCard.value.deadline !== '';
});

const openCreateModal = () => {
    isEditing.value = false;
    newCard.value = { id: null, title: '', deadline: '', priority: 'Normal' };
    showModal.value = true;
};

const openEditModal = (card: any) => {
    isEditing.value = true;
    newCard.value = { ...card };
    showModal.value = true;
};

const submitCard = () => {
    const url = isEditing.value ? `/board/${newCard.value.id}` : '/board/create';

    if (isEditing.value) {
        router.put(url, newCard.value, {
            onSuccess: () => {
                showModal.value = false;
                router.reload();
            },
        });
    } else {
        router.post(url, newCard.value, {
            onSuccess: () => {
                showModal.value = false;
                router.reload();
            },
        });
    }
};

const deleteCard = (id: number) => {
    if (confirm('Are you sure you want to delete this card?')) {
        router.delete(`/board/${id}`, {
            onSuccess: () => router.reload(),
        });
    }
};

const goToCard = (id: number) => {
    router.get(`/board/${id}`);
};

const isOverdue = (deadline: string): boolean => {
    const today = new Date();
    const due = new Date(deadline);
    return due.getTime() < today.getTime();
};

type NormalizedSubtask = { id: number; title: string; is_completed: boolean };
const getSubtasks = (task: any): NormalizedSubtask[] => {
    const raw = (task?.subtasks ?? task?.tasks ?? []) as any[];
    return raw.map((s) => ({
        id: s.id,
        title: (s.title ?? s.name ?? '') as string,
        is_completed: (s.is_completed ?? s.is_done ?? false) as boolean,
    }));
};

// Update status card saat checkbox subtask diubah
const toggleSubtask = (card: any, subtask: any) => {
    subtask.is_completed = !subtask.is_completed;

    router.put(`/board/${card.id}/subtasks`, {
        subtasks: card.subtasks
    }, {
        onSuccess: () => {
            // opsional: reload page
            // router.reload();
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

</script>

<template>
    <Head title="Board" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-col gap-4 bg-[#f2f2f2] p-6 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <input
                    type="text"
                    placeholder="Search task"
                    class="w-full max-w-xl rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                />
                <button
                    @click="openCreateModal"
                    class="ml-4 rounded-xl bg-[#033A63] px-6 py-2 text-white shadow-md dark:bg-[#34699A] dark:hover:bg-blue-500"
                >
                    + Create New
                </button>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <template
                    v-for="(section, index) in [
                        { label: '🟤 Pending', items: pendingTasks, color: 'text-gray-700', bg: 'bg-[#e1f0fc]' },
                        { label: '🟡 In Progress', items: onProgressTasks, color: 'text-yellow-600', bg: 'bg-yellow-100' },
                        { label: '🟢 Completed', items: completedTasks, color: 'text-green-600', bg: 'bg-green-100' },
                    ]"
                    :key="index"
                >
                    <div class="h-fit rounded-xl bg-white p-4 shadow-md dark:bg-[#333333] dark:text-white">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-sm font-semibold" :class="[section.color, 'dark:text-gray-200']">{{ section.label }}</span>
                            <button class="text-xl font-bold text-gray-500 dark:text-gray-400">⋯</button>
                        </div>
                        <div class="flex flex-col gap-3">
                            <div
                                v-for="task in section.items"
                                :key="task.id"
                                :class="[section.bg, 'hover:bg-opacity-80 rounded-md p-3 shadow-sm transition']"
                            >
                                <p class="mb-2 cursor-pointer font-semibold dark:text-gray-100" @click="goToCard(task.id)">
                                    {{ task.title }}
                                </p>

                                <!-- Subtasks -->
                                <ul v-if="getSubtasks(task).length" class="mb-2 text-xs space-y-1">
                                    <li v-for="sub in getSubtasks(task)" :key="sub.id" class="flex items-center gap-2">
                                        <input type="checkbox" :checked="sub.is_completed" disabled />
                                        <span :class="{ 'line-through text-gray-400': sub.is_completed }">
                                            {{ sub.title }}
                                        </span>
                                    </li>
                                </ul>

                                <div v-if="task.members && task.members.length" class="mb-2 flex items-center gap-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">👥</span>
                                    <div class="flex -space-x-2">
                                        <img
                                            v-for="member in task.members"
                                            :key="member.id"
                                            :src="member.photo ? `/storage/${member.photo}` : `https://ui-avatars.com/api/?name=${member.name}`"
                                            :alt="member.name"
                                            class="h-6 w-6 rounded-full border-2 border-white object-cover shadow dark:border-gray-700"
                                        />
                                    </div>
                                </div>
                                <div class="mb-1 text-sm text-gray-600 dark:text-gray-300">📅 {{ task.deadline }}</div>
                                <div
                                    v-if="task.status !== 'Completed' && isOverdue(task.deadline)"
                                    class="mb-1 text-xs font-semibold text-red-600 dark:text-red-400"
                                >
                                    ⚠️ Overdue
                                </div>
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ task.priority }} Priority</div>

                                <div class="flex gap-2">
                                    <button
                                        @click="openEditModal(task)"
                                        class="text-xs text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteCard(task.id)"
                                        class="text-xs text-red-600 hover:underline dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal tetap sama -->
        <div v-if="showModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800 dark:text-white">
                <h2 class="mb-4 text-lg font-semibold text-[#033A63] dark:text-gray-200">{{ isEditing ? 'Edit Card' : 'Create New Board' }}</h2>
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul</label>
                    <input
                        v-model="newCard.title"
                        type="text"
                        placeholder="Masukkan Judul"
                        class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                    />

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                    <input
                        v-model="newCard.deadline"
                        type="datetime-local"
                        class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />

                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Prioritas</label>
                    <select v-model="newCard.priority" class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="Low">Low</option>
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button @click="showModal = false" class="rounded bg-gray-300 px-4 py-2 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Cancel
                    </button>
                    <button
                        @click="submitCard"
                        :disabled="!isFormValid"
                        class="rounded bg-[#033A63] px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-500"
                    >
                        {{ isEditing ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
