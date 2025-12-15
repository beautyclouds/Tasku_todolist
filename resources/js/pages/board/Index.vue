<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, defineProps, ref, watch } from 'vue';

/**
 * Menghitung total komentar yang belum dibaca dari semua SubTask dalam satu Board Card.
 * @param {Array} tasks - Array SubTask (card.tasks)
 * @returns {number}
 */
const totalUnreadComments = (tasks) => {
    if (!tasks || tasks.length === 0) {
        return 0;
    }

    // Menggunakan reduce untuk menjumlahkan properti unread_comments_count dari setiap task
    return tasks.reduce((total, task) => {
        // Pastikan task.unread_comments_count itu angka dan bukan null/undefined
        return total + (task.unread_comments_count || 0);
    }, 0);
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Board', href: '/board' }];

const props = defineProps<{
    myBoards: any[];
    collaborationBoards: any[];
    filters?: { search?: string };
}>();

// Search input (terisi dari server jika ada)
const search = ref(props.filters?.search ?? '');

// Debounce helper simple
let searchTimeout: number | undefined;
watch(search, (val) => {
    if (searchTimeout) clearTimeout(searchTimeout);
    // debounce 400ms
    searchTimeout = window.setTimeout(() => {
        router.get(
            route('board.index'),
            { search: val },
            {
                preserveState: true,
                replace: true,
            },
        );
    }, 400);
});

// ✅ fungsi untuk reset pencarian
const resetSearch = () => {
    search.value = '';
    router.get(
        route('board.index'),
        {},
        {
            preserveState: false,
            replace: true,
        },
    );
};

// computed arrays (my boards)
const pendingTasks = computed(() => props.myBoards.filter((c) => c.status === 'Pending'));
const onProgressTasks = computed(() =>
    props.myBoards
        .filter((c) => c.status === 'In Progress')
        .sort((a, b) => {
            if (a.is_revised && !b.is_revised) return -1;
            if (!a.is_revised && b.is_revised) return 1;
            return b.id - a.id;
        }),
);
const completedTasks = computed(() => props.myBoards.filter((c) => c.status === 'Completed'));

const showModal = ref(false);
const isEditing = ref(false);
const newCard = ref({
    id: null as number | null,
    title: '',
    deadline: '',
    priority: 'Normal',
});

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
    if (!deadline) return false;
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

// Toggle single subtask locally + send updated subtasks array to backend
const toggleSubtask = (card: any, subtask: NormalizedSubtask) => {
    subtask.is_completed = !subtask.is_completed;

    const normalized = getSubtasks(card).map((s) => ({
        id: s.id,
        is_completed: s.is_completed,
    }));

    router.put(`/board/${card.id}/subtasks`, {
        subtasks: normalized,
    });
};

function closeCard(id: number) {
    if (confirm('Apakah kamu yakin ingin menutup card ini?')) {
        router.put(
            `/board/${id}/close`,
            {},
            {
                onSuccess: () => router.reload(),
            },
        );
    }
}
</script>

<template>
    <Head title="Board" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-screen flex-col gap-4 bg-[#f2f2f2] p-6 dark:bg-gray-800">
            <!-- Bagian Search + Tombol -->
            <div class="flex items-center justify-between">
                <div class="flex w-full max-w-xl items-center gap-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search task"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm dark:border-gray-800 dark:bg-black dark:text-white dark:placeholder-gray-100"
                    />
                    <!-- ✅ tombol reset -->
                    <button
                        v-if="search"
                        @click="resetSearch"
                        class="rounded bg-gray-400 px-4 py-2 text-white shadow-sm hover:bg-gray-400 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-500"
                    >
                        Reset
                    </button>
                </div>
                <button
                    @click="openCreateModal"
                    class="ml-4 rounded-xl bg-[#033A63] px-6 py-2 text-white shadow-md dark:bg-black dark:hover:bg-gray-600"
                >
                    + Create New
                </button>
            </div>

            <!-- Grid 4 kolom -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <template
                    v-for="(section, index) in [
                        { label: '🟤 Pending (My Boards)', items: pendingTasks, color: 'text-gray-700', bg: 'bg-orange-100 dark:bg-orange-200/30' },
                        {
                            label: '🟡 In Progress (My Boards)',
                            items: onProgressTasks,
                            color: 'text-yellow-600',
                            bg: 'bg-yellow-100 dark:bg-yellow-200/30',
                        },
                        {
                            label: '🟢 Completed (My Boards)',
                            items: completedTasks,
                            color: 'text-green-600',
                            bg: 'bg-green-100 dark:bg-green-200/30',
                        },
                        {
                            label: '🔵 Collaboration',
                            items: props.collaborationBoards,
                            color: 'text-blue-600',
                            bg: 'bg-blue-100 dark:bg-blue-200/30',
                        },
                    ]"
                    :key="index"
                >
                    <div class="h-fit rounded-xl bg-white p-4 shadow-md dark:bg-black dark:text-white">
                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-sm font-semibold" :class="[section.color, 'dark:text-gray-200']">{{ section.label }}</span>
                            <button class="text-xl font-bold text-gray-500 dark:text-gray-400">⋯</button>
                        </div>
                        <div class="flex flex-col gap-3">
                            <div
                                v-for="task in section.items"
                                :key="task.id"
                                class="hover:bg-opacity-80 relative rounded-md p-3 shadow-sm transition"
                                :class="section.bg"
                            >
                                <!-- Badge Revisi -->
                                <div class="absolute top-2 right-2 flex gap-1">
                                    <span
                                        v-if="totalUnreadComments(task.tasks) > 0"
                                        class="flex items-center rounded-full bg-red-500 px-2 py-0.5 text-xs font-medium text-white shadow-md"
                                    >
                                        {{ totalUnreadComments(task.tasks) }}
                                    </span>

                                    <span
                                        v-if="task.is_revised"
                                        class="rounded-full bg-red-200 px-2 py-0.5 text-[10px] font-semibold text-red-700 shadow"
                                    >
                                        Revisi
                                    </span>

                                    <span
                                        v-else-if="section.label.includes('Collaboration')"
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-[10px] font-semibold shadow',
                                            task.status === 'Pending' && 'bg-orange-200 text-orange-700',
                                            task.status === 'In Progress' && 'bg-yellow-200 text-yellow-700',
                                            task.status === 'Completed' && 'bg-green-200 text-green-700',
                                        ]"
                                    >
                                        {{ task.status }}
                                    </span>
                                </div>
                                <div class="mb-2 flex items-center justify-between">
                                    <p
                                        class="cursor-pointer overflow-hidden font-semibold text-ellipsis whitespace-nowrap dark:text-gray-100"
                                        @click="goToCard(task.id)"
                                    >
                                        {{ task.title }}
                                    </p>
                                </div>

                                <!-- Subtasks -->
                                <ul v-if="getSubtasks(task).length" class="mb-2 space-y-1 text-xs">
                                    <li v-for="sub in getSubtasks(task)" :key="sub.id" class="flex items-center gap-2">
                                        <input type="checkbox" :checked="sub.is_completed" @change="toggleSubtask(task, sub)" disabled />
                                        <span :class="{ 'text-gray-400 line-through': sub.is_completed }">
                                            {{ sub.title }}
                                        </span>
                                    </li>
                                </ul>

                                <!-- Collaborators -->
                                <div v-if="task.collaborators && task.collaborators.length" class="mb-2 flex items-center gap-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">👥</span>
                                    <div class="flex -space-x-2">
                                        <img
                                            v-for="collaborator in task.collaborators"
                                            :key="collaborator.id"
                                            :src="
                                                collaborator.photo
                                                    ? `/storage/${collaborator.photo}`
                                                    : `https://ui-avatars.com/api/?name=${collaborator.name}`
                                            "
                                            :alt="collaborator.name"
                                            class="h-6 w-6 rounded-full border-2 border-white object-cover shadow dark:border-gray-700"
                                        />
                                    </div>
                                </div>

                                <div class="mb-1 text-sm text-gray-600 dark:text-gray-300">📅 {{ task.deadline }}</div>
                                <div
                                    v-if="task.status !== 'Completed' && isOverdue(task.deadline)"
                                    class="mb-1 text-xs font-semibold text-red-600 dark:text-red-700"
                                >
                                    ⚠️ Overdue
                                </div>
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-200">{{ task.priority }} Priority</div>

                                <div class="flex gap-2">
                                    <button
                                        @click="openEditModal(task)"
                                        class="text-xs text-blue-600 hover:underline dark:text-blue-500 dark:hover:text-blue-300"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="task.user.id === $page.props.auth.user.id"
                                        @click="deleteCard(task.id)"
                                        class="text-xs text-red-600 hover:underline dark:text-red-700 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>

                                    <button
                                        v-if="task.status === 'Completed' && task.user.id === $page.props.auth.user.id"
                                        @click="closeCard(task.id)"
                                        class="text-xs text-red-600 hover:underline dark:text-red-600 dark:hover:text-red-300"
                                    >
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ⬇️ TARUH MODAL DI SINI -->
            <div v-if="showModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg dark:bg-gray-800 dark:text-white">
                    <h2 class="mb-4 text-lg font-semibold text-[#033A63] dark:text-gray-200">
                        {{ isEditing ? 'Edit Card' : 'Create New Board' }}
                    </h2>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Judul </label>
                        <input
                            v-model="newCard.title"
                            type="text"
                            placeholder="Masukkan Judul"
                            class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        />

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Deadline </label>
                        <input
                            v-model="newCard.deadline"
                            type="datetime-local"
                            class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Status Prioritas </label>
                        <select
                            v-model="newCard.priority"
                            class="w-full rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="Low">Low</option>
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="showModal = false"
                            class="rounded bg-gray-300 px-4 py-2 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitCard"
                            :disabled="!isFormValid"
                            class="dark:bg-gray-00 rounded bg-[#033A63] px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-gray-500"
                        >
                            {{ isEditing ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
