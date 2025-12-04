<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { defineProps, ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

// ============================
// DEFINISI PROPS
// ============================
const props = defineProps<{
    subtask: any;
    card: any;
    collaborators: any[];
    comments: any[];
}>();

// ============================
// TIPE DATA COMMENT
// ============================
interface CommentUser {
    id: number;
    name: string;
    avatar?: string | null;
}

interface CommentType {
    id: number;
    user_id: number;
    user: CommentUser;
    message: string | null;
    file_path?: string | null;
    created_at: string;
}

// ============================
// USER LOGIN
// ============================
const user = usePage().props.auth.user as CommentUser;

// ============================
// BACK BUTTON
// ============================
const goBack = () => {
    router.visit(`/board/${props.card.id}`);
};

// ============================
// EDIT SUBTASK
// ============================
const isEditing = ref(false);
const editedName = ref(props.subtask.name);
const editedDescription = ref(props.subtask.description);

const saveEdit = () => {
    router.put(
        `/subtask/${props.subtask.id}`,
        {
            name: editedName.value,
            description: editedDescription.value,
        },
        {
            onSuccess: () => (isEditing.value = false),
        }
    );
};

// ============================
// FORMAT DATE DETAIL
// ============================
const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// ============================
// NORMALISASI TANGGAL (FIX TIMEZONE)
// ============================
const normalizeDate = (str: string) => {
    const d = new Date(str); // dari server (UTC)
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); // ubah ke WIB
    return d;
};

// ============================
// COMMENT SYSTEM
// ============================
const comments = ref<CommentType[]>(props.comments ?? []);
const newMessage = ref("");

// Ambil komentar
const fetchComments = async () => {
    const res = await axios.get(`/subtasks/${props.subtask.id}/comments`);
    comments.value = res.data.comments;
};

// Kirim komentar
const sendComment = async () => {
    if (!newMessage.value.trim()) return;

    await axios.post(`/subtasks/${props.subtask.id}/comments`, {
        type: "text",
        message: newMessage.value,
    });

    newMessage.value = "";
    fetchComments();
};

// ============================
// FORMAT LABEL TANGGAL (Today / Yesterday)
// ============================
const formatDateLabel = (dateStr: string) => {
    const date = normalizeDate(dateStr);
    const now = normalizeDate(new Date().toISOString());

    const today = now.toDateString();

    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    if (date.toDateString() === today) return "Today";
    if (date.toDateString() === yesterday.toDateString()) return "Yesterday";

    return date.toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

// ============================
// SHOW LABEL JIKA BERBEDA HARI
// ============================
const shouldShowDateLabel = (index: number) => {
    if (index === 0) return true;

    const current = normalizeDate(comments.value[index].created_at).toDateString();
    const previous = normalizeDate(comments.value[index - 1].created_at).toDateString();

    return current !== previous;
};

// ============================
// ON MOUNT
// ============================
onMounted(() => {
    fetchComments();
});

// FORMAT JAM (HH:MM)
const formatTime = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// ============================
// SCROLL CONTROL COMMENT SECTION
// ============================

const isCommentSticky = ref(false);
const commentHeaderRef = ref<HTMLElement | null>(null);
const commentContainerRef = ref<HTMLElement | null>(null);

onMounted(() => {
    const onScroll = () => {
        const headerEl = commentHeaderRef.value;
        const containerEl = commentContainerRef.value;
        if (!headerEl || !containerEl) return;

        const rect = headerEl.getBoundingClientRect();
        const topbarHeight = 70; // tinggi topbar

        if (rect.top <= topbarHeight) {
            isCommentSticky.value = true;
            // atur tinggi container chat agar scrollable di bawah header
            containerEl.style.maxHeight = `${window.innerHeight - topbarHeight - headerEl.offsetHeight - 20}px`;
        } else {
            isCommentSticky.value = false;
            containerEl.style.maxHeight = 'none';
        }
    };

    window.addEventListener('scroll', onScroll);
    onUnmounted(() => window.removeEventListener('scroll', onScroll));
});


</script>


<template>
    <Head title="Subtask Detail" />

    <AppLayout>
        <div class="space-y-6 rounded-xl border bg-white p-6 shadow-md dark:bg-gray-800">
            <div class="grid grid-cols-3 items-center">
                <!-- Kiri: Tombol Back -->
                <div class="flex justify-start">
                    <button @click="goBack" class="text-3xl font-extrabold text-[#033A63] hover:text-[#022d4d] dark:text-white">←</button>
                </div>

                <!-- Tengah: Judul Card (center) -->
                <div class="flex justify-center">
                    <h1 class="text-xl font-bold text-[#033A63] dark:text-gray-200">📋 {{ props.card.title }}</h1>
                </div>

                <!-- Kanan: Tombol Edit -->
                <div class="flex justify-end">
                    <button
                        v-if="!isEditing"
                        @click="isEditing = true"
                        class="rounded-lg bg-[#033A63] px-4 py-2 text-lg text-sm text-white hover:bg-blue-800 dark:bg-black dark:hover:bg-gray-600"
                        title="Edit"
                    >
                        Edit
                    </button>
                </div>
            </div>

            <!-- 📌 Judul Subtask -->
            <div>
                <!-- Normal Mode -->
                <h2 v-if="!isEditing" class="text-2xl font-bold text-[#033A63] dark:text-gray-100">📌 {{ props.subtask.name }}</h2>

                <!-- Edit Mode -->
                <div v-else class="space-y-2">
                    <label class="font-semibold dark:text-gray-200">📌 Judul Subtask:</label>
                    <input v-model="editedName" class="w-full rounded-lg border p-3 dark:bg-black dark:text-white" placeholder="Nama Subtask..." />
                </div>
            </div>

            <!-- 📘 Deskripsi -->
            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📘 Deskripsi:</h2>

                <!-- Normal mode -->
                <div v-if="!isEditing" class="rounded-lg border bg-gray-50 p-4 dark:bg-black">
                    <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">
                        {{ props.subtask.description ?? 'Belum ada deskripsi.' }}
                    </p>
                </div>

                <!-- Edit mode -->
                <div v-else class="space-y-4">
                    <!-- Deskripsi Edit -->
                    <textarea
                        v-model="editedDescription"
                        rows="6"
                        class="w-full rounded-lg border p-3 dark:bg-black dark:text-white"
                        placeholder="Tulis deskripsi..."
                    ></textarea>

                    <!-- Save & Cancel di bawah deskripsi -->
                    <div class="flex gap-3">
                        <button @click="saveEdit" class="rounded-lg bg-[#033A63] px-4 py-2 text-white hover:bg-blue-900">Save</button>

                        <button @click="isEditing = false" class="rounded-lg bg-gray-400 px-4 py-2 text-white hover:bg-gray-500">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- 🗓️ Tanggal dibuat -->
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">🗓️ <strong>Dibuat:</strong> {{ formatDate(props.subtask.created_at) }}</p>

            <!-- 🕒 Deadline Card -->
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
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
                    <div class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-black">
                        <img
                            :src="
                                props.card.user?.avatar
                                    ? `/storage/${props.card.user.avatar}`
                                    : `https://ui-avatars.com/api/?name=${props.card.user?.name}`
                            "
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-black"
                        />
                        <span class="text-sm font-medium dark:text-gray-200"> {{ props.card.user?.name }} (Owner) </span>
                    </div>

                    <!-- 🔹 COLLABORATORS -->
                    <div
                        v-for="col in props.collaborators"
                        :key="col.id"
                        class="flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 dark:bg-black"
                    >
                        <img
                            :src="col.avatar ? `/storage/${col.avatar}` : `https://ui-avatars.com/api/?name=${col.name}`"
                            class="h-8 w-8 rounded-full border-2 border-white shadow dark:border-black"
                        />
                        <span class="text-sm font-medium dark:text-gray-200">
                            {{ col.name }}
                        </span>
                    </div>
                </div>

                <p v-if="!props.collaborators || props.collaborators.length === 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada collaborator.
                </p>
            </div>

            <!-- 💬 KOMENTAR SUBTASK -->
            <div class="mt-12 relative">
                <!-- HEADER KOMENTAR -->
                <h2
                    ref="commentHeaderRef"
                    class="flex justify-center text-lg font-semibold text-[#033A63] dark:text-gray-100 sticky z-10 bg-white dark:bg-gray-800 py-2 border-b"
                    :style="{ top: isCommentSticky ? '70px' : 'auto' }"
                >
                    💬 Komentar
                </h2>

                <!-- LIST KOMENTAR -->
                <div
                    ref="commentContainerRef"
                    class="space-y-2 p-2 overflow-y-auto transition-all duration-200"
                >

                    <!-- Tambahan space rapi di atas komentar -->
                    <div class="h-2"></div>
                    <div
                        v-for="(comment, index) in comments"
                        :key="comment.id"
                        class="flex flex-col gap-1"
                    >
                        <!-- Label tanggal -->
                        <div
                            v-if="shouldShowDateLabel(index)"
                            class="text-center text-gray-800 text-xs my-3"
                        >
                            {{ formatDateLabel(comment.created_at) }}
                        </div>

                        <!-- Chat row -->
                        <div
                            class="flex gap-2"
                            :class="comment.user_id === user.id ? 'justify-end' : 'justify-start'"
                        >
                            <!-- Avatar hanya untuk orang lain -->
                            <img
                                v-if="comment.user_id !== user.id"
                                :src="comment.user.avatar
                                    ? `/storage/${comment.user.avatar}`
                                    : `https://ui-avatars.com/api/?name=${comment.user.name}`"
                                class="h-8 w-8 rounded-full shadow border"
                            />

                            <!-- Chat Column -->
                            <div class="flex flex-col max-w-[70%]">
                                <!-- Nama pengirim (hanya untuk orang lain) -->
                                <span
                                    v-if="comment.user_id !== user.id"
                                    class="text-[11px] font-semibold mb-1 text-left text-gray-700"
                                >
                                    {{ comment.user.name }}
                                </span>

                                <!-- Bubble dengan timestamp di dalam -->
                                <div
                                    :class="[
                                        'relative px-2 pr-5 pb-4 py-2 rounded-xl shadow-md leading-relaxed w-fit',
                                        comment.user_id === user.id
                                            ? 'bg-[#055A99] text-white self-end'
                                            : 'bg-gray-200 text-gray-800 self-start'
                                    ]"
                                >
                                    {{ comment.message }}

                                    <!-- TIMESTAMP DI DALAM BUBBLE -->
                                    <span
                                        class="absolute bottom-1 right-2 text-[9px]"
                                        :class="comment.user_id === user.id ? 'text-gray-200' : 'text-gray-600'"
                                    >
                                        {{ formatTime(comment.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Small spacing after each bubble -->
                        <div class="h-1"></div>
                    </div>
                </div>

                <!-- INPUT -->
                <div
                    class="flex items-center gap-2 mt-4 pt-3 border-t"
                    :class="isCommentSticky ? 'sticky bottom-0 bg-white dark:bg-gray-800 z-10' : ''"
                >
                    <input
                        v-model="newMessage"
                        type="text"
                        class="flex-1 rounded-lg border px-3 py-2 dark:bg-black dark:text-white"
                        placeholder="Tulis komentar..."
                    />

                    <button
                        @click="sendComment"
                        class="rounded-lg bg-[#033A63] px-4 py-2 text-white hover:bg-[#055A99]"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
