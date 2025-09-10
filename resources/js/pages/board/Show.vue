<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, defineProps, ref } from 'vue';

const props = defineProps<{
    card: {
        id: number;
        title: string;
        deadline: string;
        priority: string;
        status: string;
        user: { id: number; name: string };
        tasks: {
            id: number;
            name: string;
            description?: string | null;
            is_done: boolean;
            histories?: {
                id: number;
                action: string;
                user: { id: number; name: string };
                created_at: string;
            }[];
        }[];
        collaborators?: { id: number; name: string; photo: string | null }[];
    };
    allUsers: {
        id: number;
        name: string;
        email: string;
        photo: string | null;
    }[];
}>();

const form = useForm({ name: '', description: '' });
const inviteForm = useForm({ email: '' });

const addSubTask = () => {
    if (form.name.trim() === '') return;
    form.post(`/board/${props.card.id}/tasks`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            router.reload({ only: ['card'] });
        },
    });
};

const toggleTask = (taskId: number) => {
    router.post(
        `/board/tasks/${taskId}/toggle`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['card'] });
            },
        },
    );
};

const inviteMember = () => {
    if (!inviteForm.email.trim()) return;
    inviteForm.post(`/board/${props.card.id}/invite`, {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            router.reload({ only: ['card'] });
        },
    });
};

const formattedDeadline = computed(() => {
    const date = new Date(props.card.deadline);
    return date.toLocaleString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

// 🔹 State untuk modal riwayat
const showHistoryModal = ref(false);
const selectedHistories = ref<
    { id: number; action: string; user: { id: number; name: string }; created_at: string }[]
>([]);

const openHistory = (histories: any[]) => {
    selectedHistories.value = histories || [];
    showHistoryModal.value = true;
};

const closeHistory = () => {
    showHistoryModal.value = false;
    selectedHistories.value = [];
};
</script>

<template>
    <Head title="Card Detail" />
    <AppLayout>
        <div class="p-6 border rounded-xl shadow-md bg-white dark:bg-gray-800 space-y-3">
            <!-- 🔙 Tombol Kembali -->
            <a
                href="/board"
                class="inline-flex items-center text-3xl font-extrabold text-[#033A63] transition hover:text-[#022d4d] dark:text-blue-400 dark:hover:text-blue-300"
            >
                ←
            </a>

            <h1
                class="mb-2 flex items-center gap-2 text-2xl font-bold text-[#033A63] dark:text-gray-100"
            >
                📋 {{ props.card.title }}
            </h1>

            <p
                class="mb-1 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300"
            >
                🗓️ {{ formattedDeadline }}
            </p>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                Priority: {{ props.card.priority }} | Status:
                {{ props.card.status }}
            </p>

            <!-- ✅ Show Collaborators -->
            <div
                v-if="props.card.collaborators && props.card.collaborators.length"
                class="mb-4"
            >
                <h2 class="mb-2 font-semibold dark:text-gray-200">
                    👥 Collaborators:
                </h2>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Owner -->
                    <div
                        class="flex items-center gap-2 rounded-full bg-gray-100 px-2 py-1 dark:bg-gray-700"
                    >
                        <img
                            :src="`https://ui-avatars.com/api/?name=${props.card.user.name}`"
                            :alt="props.card.user.name"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-gray-700"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ props.card.user.name }} (Owner)
                        </span>
                    </div>

                    <!-- Collaborators -->
                    <div
                        v-for="collaborator in props.card.collaborators"
                        :key="collaborator.id"
                        class="flex items-center gap-2 rounded-full bg-gray-100 px-2 py-1 dark:bg-gray-700"
                    >
                        <img
                            :src="
                                collaborator.photo
                                    ? `/storage/${collaborator.photo}`
                                    : `https://ui-avatars.com/api/?name=${collaborator.name}`
                            "
                            :alt="collaborator.name"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-gray-700"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ collaborator.name }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ✅ Invite Member -->
            <div class="mb-6">
                <h2 class="mb-2 font-semibold dark:text-gray-200">
                    ➕ Invite Member
                </h2>
                <form @submit.prevent="inviteMember" class="flex items-center gap-2">
                    <select
                        v-model="inviteForm.email"
                        class="flex-1 rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="" disabled>Select a user...</option>
                        <option
                            v-for="user in props.allUsers"
                            :key="user.id"
                            :value="user.email"
                            :disabled="
                                props.card.collaborators?.some((c) => c.id === user.id)
                            "
                        >
                            {{ user.name }} ({{ user.email }})
                        </option>
                    </select>
                    <button
                        class="w-[100px] rounded bg-[#033A63] py-1.5 text-l text-white transition hover:bg-[#022d4d] dark:bg-[#34699A] dark:hover:bg-blue-500"
                    >
                        Invite
                    </button>
                </form>
            </div>

            <!-- ✅ Sub Tasks -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📌 Sub Tasks:</h2>
                <ul class="mb-4 space-y-2 text-gray-800 dark:text-gray-200">
                    <li v-for="task in props.card.tasks" :key="task.id" class="flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                :checked="task.is_done"
                                @change="toggleTask(task.id)"
                                class="h-4 w-4 accent-[#033A63] dark:accent-blue-500"
                            />
                            <span
                                :class="{
                                    'text-gray-500 line-through dark:text-gray-400': task.is_done,
                                }"
                            >
                                {{ task.name }}
                            </span>
                        </div>

                        <!-- 🔹 Deskripsi subtask -->
                        <p
                            v-if="task.description"
                            class="ml-6 text-sm text-gray-500 dark:text-gray-400"
                        >
                            {{ task.description }}
                        </p>

                        <!-- 🔹 Info riwayat terakhir -->
                        <p
                            v-if="props.card.collaborators && props.card.collaborators.length > 0 && task.histories && task.histories.length"
                            class="ml-6 text-xs italic text-gray-600 dark:text-gray-400"
                        >
                            ({{ task.histories[0].user.name }}
                            {{ task.histories[0].action }}
                            pada
                            {{ new Date(task.histories[0].created_at).toLocaleString() }})
                        </p>
                    </li>
                </ul>


                <!-- ✅ Form tambah task -->
                <form @submit.prevent="addSubTask" class="flex flex-col gap-2">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Add new task..."
                        class="flex-1 rounded border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#033A63] focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500"
                    />
                    <textarea
                        v-model="form.description"
                        placeholder="Add description (optional)"
                        class="flex-1 rounded border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#033A63] focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500"
                    ></textarea>
                    <button
                        class="w-[100px] rounded bg-[#033A63] py-1.5 text-l text-white transition hover:bg-[#022d4d] dark:bg-[#34699A] dark:hover:bg-blue-500"
                    >
                        Add
                    </button>
                </form>
            </div>
        </div>

        <!-- 🔹 Modal Riwayat -->
        <div
            v-if="showHistoryModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <h3 class="mb-4 text-lg font-bold text-gray-800 dark:text-white">
                    🕑 Riwayat Subtask
                </h3>
                <ul class="space-y-2">
                    <li
                        v-for="history in selectedHistories"
                        :key="history.id"
                        class="text-sm text-gray-700 dark:text-gray-200"
                    >
                        <span class="font-semibold">{{ history.user.name }}</span>
                        {{ history.action }} at
                        {{ new Date(history.created_at).toLocaleString() }}
                    </li>
                </ul>
                <button
                    @click="closeHistory"
                    class="mt-4 rounded bg-red-500 px-4 py-2 text-white hover:bg-red-600"
                >
                    Close
                </button>
            </div>
        </div>
    </AppLayout>
</template>
