<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, onMounted, onUnmounted, onBeforeUnmount, ref } from 'vue';

// ============================
// DEFINISI PROPS
// ============================
const props = defineProps<{
    subtask: SubTaskType;
    card: any;
    collaborators: any[];
    // comments: any[];
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
    file_name?: string | null;
    file_type?: string | null;
    file_size?: number | null;
    created_at: string;
    updated_at: string;
    parent_id?: number | null;
    parent?: {
        id: number;
        user_id: number;
        user: CommentUser;
        message: string | null;
    } | null;
}

interface SubTaskType {
    id: number;
    name: string;
    description?: string | null;
    is_done: boolean;
    is_close?: boolean;
    created_at: string;
    updated_at: string;
    unread_comments_count?: number;
    first_unread_comment_id?: number | null;
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
        },
    );
};

// ============================
// FORMATTING & TIME UTILS
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

const normalizeDate = (str: string) => {
    const d = new Date(str);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d;
};

const formatDateLabel = (dateStr: string) => {
    const date = normalizeDate(dateStr);
    const now = normalizeDate(new Date().toISOString());

    const today = now.toDateString();

    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    if (date.toDateString() === today) return 'Today';
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

    return date.toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const shouldShowDateLabel = (index: number) => {
    if (index === 0) return true;

    const current = normalizeDate(comments.value[index].created_at).toDateString();
    const previous = normalizeDate(comments.value[index - 1].created_at).toDateString();

    return current !== previous;
};

const formatTime = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

// ============================
// SCROLL CONTROL & STICKY HEADER + GLOBAL CLICK HANDLER
// ============================
const isCommentSticky = ref(false);
const commentHeaderRef = ref<HTMLElement | null>(null);
const commentContainerRef = ref<HTMLElement | null>(null);

const scrollToBottom = () => {
    const container = commentContainerRef.value;
    if (container) container.scrollTop = container.scrollHeight;
};

// declare handlers in outer scope so we can add/remove the same reference
const onScroll = () => {
    const headerEl = commentHeaderRef.value;
    const containerEl = commentContainerRef.value;
    if (!headerEl || !containerEl) return;

    const rect = headerEl.getBoundingClientRect();
    const topbarHeight = 70; // tinggi topbar

    if (rect.top <= topbarHeight) {
        isCommentSticky.value = true;
        containerEl.style.maxHeight = `${window.innerHeight - topbarHeight - headerEl.offsetHeight - 20}px`;
    } else {
        isCommentSticky.value = false;
        containerEl.style.maxHeight = 'none';
    }
};

// klik global: jangan tutup saat klik di tombol atau dropdown (cek kelas)
const onWindowClick = (e: any) => {
    // safety: jika e.target tidak ada close, keluar
    try {
        if ((e.target as Element).closest && (e.target as Element).closest('.menu-dropdown')) return;
        if ((e.target as Element).closest && (e.target as Element).closest('.menu-btn')) return;
    } catch {
        // ignore
    }
    closeMenu();
};

onMounted(() => {
    window.addEventListener('scroll', onScroll);
    window.addEventListener('click', onWindowClick);

    // panggil fetchComments di mount
    fetchComments();
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('click', onWindowClick);
});

// ============================
// COMMENT SYSTEM
// ============================
const comments = ref<CommentType[]>([]);
const newMessage = ref('');

// Ambil komentar
const fetchComments = async () => {
    const res = await axios.get(`/subtasks/${props.subtask.id}/comments`);

    // Backend kamu mengembalikan { comments: [...] }
    comments.value = res.data.comments ?? res.data;

    cancelReply();

    await nextTick();
    scrollToBottom();
};

// Modifikasi fungsi sendComment untuk menangani reply ID (jika backend mendukung)
const sendComment = async () => {
    if (!newMessage.value.trim() && !selectedFile.value) return;

    try {
        if (selectedFile.value) {
            const formData = new FormData();
            formData.append('type', 'file');
            formData.append('message', newMessage.value);

            // ✅ KIRIM parent_id HANYA JIKA REPLY
            if (replyToId.value !== null) {
                formData.append('parent_id', String(replyToId.value));
            }

            formData.append('file', selectedFile.value);

            await axios.post(
                `/subtasks/${props.subtask.id}/comments`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
        } else {
            await axios.post(`/subtasks/${props.subtask.id}/comments`, {
                type: 'text',
                message: newMessage.value,
                parent_id: replyToId.value ?? null,
            });
        }

        // 🔥 tandai sudah dibaca di backend
        await axios.post(route('subtask.markRead', props.subtask.id))

        // 🔥 HAPUS UNREAD DI FRONTEND
        unreadCount.value = 0
        firstUnreadId.value = null

        // 🔄 Reset state
        newMessage.value = '';
        removeSelectedFile();
        replyToId.value = null;
        replyToUser.value = null;
        replyTo.value = null;

        await fetchComments();
    } catch (err: any) {
        console.error(err.response?.data ?? err);
        alert(err.response?.data?.message ?? 'Gagal mengirim komentar');
    }
};



// ============================
// MENU STATE
// ============================
const activeMenuId = ref<number | null>(null);

const toggleMenu = (id: number) => {
    activeMenuId.value = activeMenuId.value === id ? null : id;
};

const closeMenu = () => {
    activeMenuId.value = null;
};

// onMounted(() => {
//    window.addEventListener('click', (e: any) => {
//        // Jika klik di dalam menu dropdown → jangan tutup
//        if (e.target.closest('.menu-dropdown')) return;
//
//        // Jika klik tombol titik 3 → jangan tutup
//        if (e.target.closest('.menu-btn')) return;
//
//        // Selain itu → tutup
//        closeMenu();
//   });
///});
onUnmounted(() => {
    window.removeEventListener('click', closeMenu);
});

// ============================
// 🌟 EDIT COMMENT STATE (LOGIKA PERBAIKAN) 🌟
// ============================
const isEditingComment = ref(false);
const editingCommentId = ref<number | null>(null);
const editingOldMessage = ref('');
// ⭐ VARIABEL BARU KHUSUS UNTUK EDIT
const editedMessage = ref('');

// Mulai edit
const startEditComment = (comment: CommentType) => {
    isEditingComment.value = true;
    editingCommentId.value = comment.id;
    editingOldMessage.value = comment.message ?? '';
    editedMessage.value = comment.message ?? ''; // Isi ke input edit
    activeMenuId.value = null; // Tutup menu setelah klik edit
};

// Batal edit
const cancelEdit = () => {
    isEditingComment.value = false;
    editingCommentId.value = null;
    editingOldMessage.value = '';
    editedMessage.value = '';
};

// Simpan edit
const saveEditedComment = async () => {
    if (!editingCommentId.value || !editedMessage.value.trim()) return;

    // Cek apakah ada perubahan
    if (editedMessage.value.trim() === editingOldMessage.value.trim()) {
        cancelEdit();
        return;
    }

    try {
        await axios.put(`/comments/${editingCommentId.value}`, {
            message: editedMessage.value, // Kirim pesan yang sudah diedit
            edited: true,
        });
    } catch (error) {
        console.error('Gagal menyimpan edit:', error);
        alert('Gagal menyimpan perubahan. Coba lagi.');
    }

    cancelEdit();
    await fetchComments();
};

// ============================
// 🗑️ DELETE COMMENT LOGIC
// ============================
const deleteComment = async (commentId: number) => {
    // Konfirmasi penghapusan
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    try {
        // Kirim permintaan DELETE ke backend
        await axios.delete(`/comments/${commentId}`);

        // Tutup menu dan refresh list komentar
        activeMenuId.value = null;
        await fetchComments();
    } catch (error) {
        console.error('Failed to delete comment:', error);
        alert('Gagal menghapus pesan. Coba lagi.');
    }
};

// ============================
// 🌟 NOTIFIKASI TOAST STATE 🌟
// ============================
const showCopySuccess = ref(false); // State untuk menampilkan notifikasi
let timeoutId: number | null = null;

// Fungsi untuk menampilkan notifikasi sukses selama 2 detik
const triggerSuccessToast = () => {
    // 1. Bersihkan timeout sebelumnya (jika ada)
    if (timeoutId !== null) {
        clearTimeout(timeoutId);
    }

    // 2. Tampilkan notifikasi
    showCopySuccess.value = true;

    // 3. Atur agar notifikasi hilang setelah 2000 ms (2 detik)
    timeoutId = setTimeout(() => {
        showCopySuccess.value = false;
        timeoutId = null;
    }, 2000); // 2 detik
};

// ============================
// 📋 COPY COMMENT LOGIC (Diubah)
// ============================
const copyComment = async (message: string | null) => {
    if (!message) {
        alert('Pesan kosong, tidak ada yang disalin.');
        activeMenuId.value = null;
        return;
    }

    try {
        await navigator.clipboard.writeText(message);

        // ❌ HAPUS: alert('Pesan berhasil disalin ke clipboard!');

        // ✅ GANTI: Panggil toast yang baru
        triggerSuccessToast();

        activeMenuId.value = null;
    } catch (err) {
        console.error('Gagal menyalin.', err);
        alert('Gagal menyalin pesan.');
    }
};

//=================
// REPLAY COMMENT
//=================

const replyToId = ref<number | null>(null);
const replyToUser = ref<string | null>(null);
const replyTo = ref<CommentType | null>(null);

const startReply = (comment: CommentType) => {
    replyToId.value = comment.id;
    replyToUser.value = comment.user.name;
    replyTo.value = comment; // <-- penting

    console.log('Komentar yang di reply', comment);
};

const cancelReply = () => {
    replyToId.value = null;
    replyToUser.value = null;
    replyTo.value = null;
};

// ============================
// UPLOAD FILE
// ============================
const selectedFile = ref<File | null>(null);
const filePreviewUrl = ref<string | null>(null);

const handleFileUpload = (event: Event) => {
    const input = event.target as HTMLInputElement | null;
    const file = input?.files?.[0] ?? null;
    if (!file) {
        selectedFile.value = null;
        filePreviewUrl.value = null;
        return;
    }

    selectedFile.value = file;
    // create preview only for local previewing (image/video/audio)
    // revoke previous object URL if any
    if (filePreviewUrl.value) {
        try {
            URL.revokeObjectURL(filePreviewUrl.value);
        } catch {}
    }
    filePreviewUrl.value = URL.createObjectURL(file);
};

const removeSelectedFile = () => {
    selectedFile.value = null;
    if (filePreviewUrl.value) {
        try {
            URL.revokeObjectURL(filePreviewUrl.value);
        } catch {}
    }
    filePreviewUrl.value = null;
};

// -----------------------------
// Utility checks used in template
// -----------------------------
const isLocalImageFile = (file?: File | null, mime?: string | null) => {
    if (file) return file.type.startsWith('image/');
    if (mime) return mime.startsWith('image/');
    return false;
};

const isImageMime = (mime?: string | null) => {
    return !!mime && mime.startsWith('image/');
};

const isVideoMime = (mime?: string | null) => {
    return !!mime && mime.startsWith('video/');
};

const isAudioMime = (mime?: string | null) => {
    return !!mime && mime.startsWith('audio/');
};

// -----------------------------
// Helper to format file size (used in template)
// -----------------------------
const formatFileSize = (bytes?: number | null) => {
    if (!bytes && bytes !== 0) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
};

//INDIKATOR UNREAD
const firstUnreadIndex = computed(() => {
    if (!props.subtask.unread_comments_count) return -1;
    return comments.value.length - props.subtask.unread_comments_count;
});

//UNREAD HILANG SAAT KELUAR HALAMAN
onBeforeUnmount(() => {
    axios.post(route('subtask.markRead', props.subtask.id))

    // 🔥 reset indikator
    unreadCount.value = 0
    firstUnreadId.value = null
})


//UNREAD HILANG SAAT KIRIM PESAN BARU
const unreadCount = ref(props.subtask.unread_comments_count ?? 0)
const firstUnreadId = ref<number | null>(props.subtask.first_unread_comment_id ?? null)

</script>

<template>
    <Head title="Subtask Detail" />

    <AppLayout>
        <div class="space-y-6 rounded-xl border bg-white p-6 shadow-md dark:bg-gray-800">
            <div class="grid grid-cols-3 items-center">
                <div class="flex justify-start">
                    <button @click="goBack" class="text-3xl font-extrabold text-[#033A63] hover:text-[#022d4d] dark:text-white">←</button>
                </div>

                <div class="flex justify-center">
                    <h1 class="text-xl font-bold text-[#033A63] dark:text-gray-200">📋 {{ props.card.title }}</h1>
                </div>

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

            <div>
                <h2 v-if="!isEditing" class="text-2xl font-bold text-[#033A63] dark:text-gray-100">📌 {{ props.subtask.name }}</h2>

                <div v-else class="space-y-2">
                    <label class="font-semibold dark:text-gray-200">📌 Judul Subtask:</label>
                    <input v-model="editedName" class="w-full rounded-lg border p-3 dark:bg-black dark:text-white" placeholder="Nama Subtask..." />
                </div>
            </div>

            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">📘 Deskripsi:</h2>

                <div v-if="!isEditing" class="rounded-lg border bg-gray-50 p-4 dark:bg-black">
                    <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">
                        {{ props.subtask.description ?? 'Belum ada deskripsi.' }}
                    </p>
                </div>

                <div v-else class="space-y-4">
                    <textarea
                        v-model="editedDescription"
                        rows="6"
                        class="w-full rounded-lg border p-3 dark:bg-black dark:text-white"
                        placeholder="Tulis deskripsi..."
                    ></textarea>

                    <div class="flex gap-3">
                        <button @click="saveEdit" class="rounded-lg bg-[#033A63] px-4 py-2 text-white hover:bg-blue-900">Save</button>

                        <button @click="isEditing = false" class="rounded-lg bg-gray-400 px-4 py-2 text-white hover:bg-gray-500">Cancel</button>
                    </div>
                </div>
            </div>
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">🗓️ <strong>Dibuat:</strong> {{ formatDate(props.subtask.created_at) }}</p>

            <p class="mb-3 text-sm text-gray-600 dark:text-gray-300">
                🕒 <strong>Deadline Card:</strong>
                {{ props.card.deadline ? formatDate(props.card.deadline) : 'Tidak ada deadline.' }}
            </p>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                🔄 <strong>Update Terakhir:</strong>
                {{ props.subtask.updated_at ? formatDate(props.subtask.updated_at) : '-' }}
            </p>

            <div>
                <h2 class="mb-2 font-semibold dark:text-gray-200">👥 Collaborators:</h2>
                <div class="flex flex-wrap gap-3">
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

            <div class="relative mt-12">
                <h2
                    ref="commentHeaderRef"
                    class="sticky z-10 flex justify-center rounded-lg border-b bg-white py-2 text-lg font-semibold text-[#033A63] dark:bg-black dark:text-gray-100"
                    :style="{ top: isCommentSticky ? '70px' : 'auto' }"
                >
                    💬 Komentar
                </h2>

                <div ref="commentContainerRef" class="space-y-2 overflow-y-auto p-2 transition-all duration-200">
                    <div class="h-2"></div>
                    <div
                        v-for="(comment, index) in comments"
                        :key="comment.id"
                        class="flex flex-col gap-1"
                        @contextmenu.prevent="startReply(comment)"
                    >
                        <div v-if="shouldShowDateLabel(index)" class="my-3 text-center text-xs text-gray-800">
                            {{ formatDateLabel(comment.created_at) }}
                        </div>

                        <!-- Indikator unread -->
                        <div
                            v-if="firstUnreadId && comment.id === firstUnreadId"
                            class="relative my-3 text-center"
                        >
                            <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-[#033A63] dark:bg-gray-400"></div>

                            <span
                                class="relative inline-block rounded-full bg-[#033A63] px-3 py-1 text-xs font-semibold text-white shadow-md dark:bg-gray-400"
                            >
                                {{ unreadCount }} pesan baru
                            </span>
                        </div>

                        <div class="flex gap-2" :class="comment.user_id === user.id ? 'justify-end' : 'justify-start'">
                            <img
                                v-if="comment.user_id !== user.id"
                                :src="
                                    comment.user.avatar ? `/storage/${comment.user.avatar}` : `https://ui-avatars.com/api/?name=${comment.user.name}`
                                "
                                class="h-8 w-8 rounded-full border shadow"
                            />

                            <div class="flex max-w-[70%] flex-col">
                                <span
                                    v-if="comment.user_id !== user.id"
                                    class="mb-1 text-left text-[11px] font-semibold text-gray-700 dark:text-gray-300"
                                >
                                    {{ comment.user.name }}
                                </span>

                                <div
                                    class="group relative w-fit min-w-[80px] rounded-xl px-2 py-2 pr-7 pb-4 leading-relaxed shadow-md"
                                    :class="[
                                        comment.user_id === user.id
                                            ? 'self-end bg-[#055A99] text-white'
                                            : 'self-start bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                        comment.parent ? 'bg-[#055A99]' : '',
                                    ]"
                                >
                                    <!-- Titik 3 untuk menampilkan menu replay, copy, edit, dan hapus -->
                                    <button
                                        class="menu-btn absolute top-1 text-gray-700 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                        :class="comment.user_id === user.id ? 'left-[-20px]' : 'right-[-20px]'"
                                        @click.stop="toggleMenu(comment.id)"
                                    >
                                        ⋮
                                    </button>

                                    <div
                                        v-if="activeMenuId === comment.id"
                                        class="menu-dropdown absolute top-5 z-20 w-32 rounded-lg border bg-white text-sm shadow-md dark:bg-gray-800"
                                        :class="comment.user_id === user.id ? 'left-[-140px]' : 'right-[-140px]'"
                                        @click.stop
                                    >
                                        <!-- Menu user yang login -->
                                        <template v-if="comment.user_id === user.id">
                                            <div
                                                class="cursor-pointer px-3 py-2 text-blue-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                @click="
                                                    startEditComment(comment);
                                                    activeMenuId = null;
                                                "
                                            >
                                                Edit
                                            </div>

                                            <div
                                                class="cursor-pointer px-3 py-2 text-red-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                @click="deleteComment(comment.id)"
                                            >
                                                Delete
                                            </div>

                                            <div
                                                class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="
                                                    startReply(comment);
                                                    activeMenuId = null;
                                                "
                                            >
                                                Reply
                                            </div>

                                            <div
                                                class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="copyComment(comment.message)"
                                            >
                                                Copy
                                            </div>
                                        </template>
                                        <!-- Menu User lain -->
                                        <template v-else>
                                            <div
                                                class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="
                                                    startReply(comment);
                                                    activeMenuId = null;
                                                "
                                            >
                                                Reply
                                            </div>

                                            <div
                                                class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="copyComment(comment.message)"
                                            >
                                                Copy
                                            </div>
                                        </template>
                                    </div>

                                    <!-- 🟦 REPLY BUBBLE (Jika comment ini adalah balasan)nah, iki sg ga kepanggil -->
                                    <div
                                        v-if="comment.parent"
                                        class="mb-1 rounded-lg border-l-4 p-2 transition-all duration-300"
                                        :class="[
                                            comment.user_id === user.id
                                                ? 'border-[#2D79B0] bg-[#e6f4ff]' // Latar belakang lebih terang untuk balasan di bubble sendiri
                                                : 'border-gray-400 bg-gray-300 dark:border-gray-600 dark:bg-gray-600', // Latar belakang di bubble lawan
                                        ]"
                                    >
                                        <div
                                            class="text-xs font-semibold"
                                            :class="comment.user_id === user.id ? 'text-[#033A63]' : 'text-gray-800 dark:text-gray-200'"
                                        >
                                            Replying to {{ comment.parent.user.name }}
                                        </div>

                                        <div
                                            class="truncate text-xs"
                                            :class="comment.user_id === user.id ? 'text-[#033A63]' : 'text-gray-800 dark:text-gray-200'"
                                        >
                                            {{ comment.parent.message }}
                                        </div>
                                    </div>

                                    <!-- ========== FILE PREVIEW ========== -->
                                    <div v-if="comment.file_path" class="mt-2">
                                        <!-- Jika FILE adalah gambar -->
                                        <img
                                            v-if="comment.file_type?.startsWith('image/') ?? false"
                                            :src="`/storage/${comment.file_path}`"
                                            class="max-h-60 rounded-lg border shadow"
                                        />

                                        <!-- Jika FILE adalah video -->
                                        <video
                                            v-else-if="comment.file_type?.startsWith('video/') ?? false"
                                            controls
                                            class="max-h-60 rounded-lg shadow"
                                        >
                                            <source :src="`/storage/${comment.file_path}`" :type="comment.file_type ?? ''" />
                                            Browser anda tidak mendukung video.
                                        </video>

                                        <!-- Jika FILE adalah audio -->
                                        <audio v-else-if="comment.file_type?.startsWith('audio/') ?? false" controls class="mt-2 w-full">
                                            <source :src="`/storage/${comment.file_path}`" :type="comment.file_type ?? ''" />
                                        </audio>

                                        <!-- Jika PDF -->
                                        <a
                                            v-else-if="comment.file_type === 'application/pdf'"
                                            :href="`/storage/${comment.file_path}`"
                                            target="_blank"
                                            class="mt-1 flex items-center gap-2 underline"
                                            :class="comment.user_id === user.id ? 'text-gray-200' : 'text-gray-600'"
                                        >
                                            📄 {{ comment.file_name }} ({{ formatFileSize(comment.file_size) }})
                                        </a>

                                        <!-- File lainnya (ZIP, DOCX, DLL) -->
                                        <a
                                            v-else
                                            :href="`/storage/${comment.file_path}`"
                                            target="_blank"
                                            class="mt-1 flex items-center gap-2 underline"
                                            :class="comment.user_id === user.id ? 'text-gray-200' : 'text-gray-600'"
                                        >
                                            📎 Download {{ comment.file_name }} ({{ formatFileSize(comment.file_size) }})
                                        </a>
                                    </div>

                                    <!-- ========== MESSAGE CONTENT ========== -->
                                    <div class="whitespace-pre-wrap">
                                        <span>{{ comment.message }}</span>
                                    </div>

                                    <!-- Indikator setelah diedit -->
                                    <span
                                        class="absolute right-2 bottom-1 flex items-center gap-1 text-[9px]"
                                        :class="comment.user_id === user.id ? 'text-gray-200' : 'text-gray-600 dark:text-gray-200'"
                                    >
                                        <span
                                            v-if="comment.updated_at !== comment.created_at"
                                            class="italic opacity-80"
                                            :class="comment.user_id === user.id ? 'text-gray-200/90' : 'text-gray-600/90'"
                                        >
                                            (edited)
                                        </span>

                                        <span>{{ formatTime(comment.created_at) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="h-1"></div>
                    </div>
                </div>

                <!-- INPUT -->
                <!-- Untuk mode edit -->
                <div v-if="isEditingComment" class="mt-4 w-full border-t pt-4">
                    <div class="mb-1 flex items-center justify-between rounded-t-lg bg-gray-100 p-1 dark:bg-gray-700">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300"> Mengedit pesan... </span>
                        <button @click="cancelEdit" class="p-1 text-lg leading-none font-bold text-gray-500 hover:text-red-600">&times;</button>
                    </div>

                    <div class="flex w-full items-center gap-2">
                        <input
                            v-model="editedMessage"
                            class="flex-1 rounded-lg border px-3 py-2 dark:bg-black dark:text-white"
                            @keyup.enter="saveEditedComment"
                            placeholder="Edit pesan Anda..."
                        />
                        <button
                            @click="saveEditedComment"
                            class="rounded-lg bg-[#033A63] px-3 py-2 text-white hover:bg-[#055A99] disabled:bg-[#3B8BC9]"
                            :disabled="!editedMessage.trim() || editedMessage.trim() === editingOldMessage.trim()"
                        >
                            Save
                        </button>
                    </div>
                </div>

                <div v-else class="relative mt-4 w-full border-t pt-4">
                    <!-- Untuk notif copy berhasil -->
                    <Transition name="fade">
                        <div
                            v-if="showCopySuccess"
                            class="absolute -top-10 left-1/2 z-30 -translate-x-1/2 rounded-lg bg-gray-200 p-2 px-4 text-sm text-gray-500 shadow-xl transition-opacity duration-300"
                        >
                            Pesan berhasil disalin ke clipboard!
                        </div>
                    </Transition>

                    <!-- ========== REPLY PREVIEW ABOVE INPUT ========== -->
                    <div
                        v-if="replyTo"
                        class="mb-2 flex items-start justify-between rounded-lg border-l-4 border-blue-500 bg-gray-200 px-3 py-2 dark:bg-gray-700"
                    >
                        <div>
                            <p class="text-sm font-semibold">{{ replyTo.user.name }}</p>
                            <p class="line-clamp-1 text-xs text-gray-600 dark:text-gray-300">
                                {{ replyTo.message }}
                            </p>
                        </div>

                        <button @click="cancelReply" class="text-gray-600 hover:text-red-500 dark:text-gray-300">✕</button>
                    </div>

                    <!-- ========== FILE PREVIEW (BARU) ========== -->
                    <div v-if="selectedFile" class="mb-2 rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ selectedFile.name }} ({{ (selectedFile.size / 1024).toFixed(1) }} KB)
                            </span>

                            <button @click="removeSelectedFile" class="text-sm text-red-500 hover:text-red-700">✕</button>
                        </div>
                    </div>

                    <!-- INPUT BAR -->
                    <div class="flex w-full items-center gap-2">
                        <!-- TEXT INPUT -->
                        <input
                            id="new-message-input"
                            v-model="newMessage"
                            class="flex-1 rounded-lg border px-3 py-2 dark:bg-black dark:text-white"
                            @keyup.enter="sendComment"
                            placeholder="Tulis komentar baru..."
                        />
                        <!-- Tombol upload -->
                        <label
                            for="file-input"
                            class="cursor-pointer rounded-lg bg-gray-200 px-3 py-2 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600"
                        >
                            📎
                        </label>
                        <input id="file-input" type="file" class="hidden" @change="handleFileUpload" />
                        <button
                            @click="sendComment"
                            class="rounded-lg bg-[#033A63] px-4 py-2 text-white hover:bg-[#055A99] disabled:bg-[#3B8BC9]"
                            :disabled="!newMessage.trim() && !selectedFile"
                        >
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
