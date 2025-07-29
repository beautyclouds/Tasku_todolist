// ✅ Show.vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, defineProps } from 'vue';

const props = defineProps<{
    card: {
        id: number;
        title: string;
        deadline: string;
        priority: string;
        status: string;
        tasks: { id: number; name: string; is_done: boolean }[];
        members?: { id: number; name: string; photo: string | null }[];
    };
    allMembers: {
        id: number;
        name: string;
        photo: string | null;
    }[];
}>();

const form = useForm({ name: '' });
const inviteForm = useForm({ name: '' });

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
    if (!inviteForm.name.trim()) return;
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
</script>

<template>
    <Head title="Card Detail" />
    <AppLayout>
        <div class="mx-auto mt-6 w-full rounded-xl bg-white p-6 shadow-md dark:bg-[#333333] dark:text-white">
            <!-- 🔙 Tombol Kembali -->
            <a
                href="/board"
                class="inline-flex items-center text-3xl font-extrabold text-[#033A63] transition hover:text-[#022d4d] dark:text-blue-400 dark:hover:text-blue-300"
            >
                  ←
            </a>

            <h1 class="mb-2 flex items-center gap-2 text-2xl font-bold text-[#033A63] dark:text-gray-100">📋 {{ props.card.title }}</h1>

            <p class="mb-1 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">🗓️ {{ formattedDeadline }}</p>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Priority: {{ props.card.priority }} | Status: {{ props.card.status }}</p>

            <!-- ✅ Show Members -->
            <div v-if="props.card.members && props.card.members.length" class="mb-4">
                <h2 class="mb-2 font-semibold dark:text-gray-200">👥 Members:</h2>
                <div class="flex -space-x-2">
                    <img
                        v-for="member in props.card.members"
                        :key="member.id"
                        :src="member.photo ? `/storage/${member.photo}` : `https://ui-avatars.com/api/?name=${member.name}`"
                        :alt="member.name"
                        class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-gray-700"
                    />
                </div>
            </div>

            <!-- ✅ Invite Member -->
            <div class="mb-6">
                <h2 class="mb-2 font-semibold dark:text-gray-200">➕ Invite Member</h2>
                <form @submit.prevent="inviteMember" class="flex items-center gap-2">
                    <select
                        v-model="inviteForm.name"
                        class="flex-1 rounded border px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="" disabled>Select a member...</option>
                        <option
                            v-for="member in props.allMembers"
                            :key="member.id"
                            :value="member.name"
                            :disabled="props.card.members?.some((m) => m.id === member.id)"
                        >
                            {{ member.name }}
                        </option>
                    </select>
                    <button
                        class="w-[100px] rounded bg-[#033A63] py-1.5 text-l text-white transition hover:bg-[#022d4d] dark:bg-[#34699A] dark:hover:bg-blue-500"
                    >
                          Invite
                    </button>
                </form>
            </div>

            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📌 Sub Tasks:</h2>
                <ul class="mb-4 space-y-2 text-gray-800 dark:text-gray-200">
                    <li v-for="task in props.card.tasks" :key="task.id" class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            :checked="task.is_done"
                            @change="toggleTask(task.id)"
                            class="h-4 w-4 accent-[#033A63] dark:accent-blue-500"
                        />
                        <span :class="{ 'text-gray-500 line-through dark:text-gray-400': task.is_done }">
                            {{ task.name }}
                        </span>
                    </li>
                </ul>

                <form @submit.prevent="addSubTask" class="flex gap-2">
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Add new task..."
                        class="flex-1 rounded border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#033A63] focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-blue-500"
                    />
                    <button
                        class="w-[100px] rounded bg-[#033A63] py-1.5 text-l text-white transition hover:bg-[#022d4d] dark:bg-[#34699A] dark:hover:bg-blue-500"
                    >
                          Add
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
