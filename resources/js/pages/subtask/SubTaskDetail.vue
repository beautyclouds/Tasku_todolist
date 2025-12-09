<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
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
    updated_at: string; 
    parent_id?: number | null;

    parent?: {
        id: number;
        user_id: number;
        user: CommentUser;
        message: string | null;
    } | null;
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

    if (date.toDateString() === today) return "Today";
    if (date.toDateString() === yesterday.toDateString()) return "Yesterday";

    return date.toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
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
  // safety: jika e.target tidak ada closest, keluar
  try {
    if ((e.target as Element).closest && (e.target as Element).closest('.menu-dropdown')) return;
    if ((e.target as Element).closest && (e.target as Element).closest('.menu-btn')) return;
  } catch (err) {
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
const comments = ref<CommentType[]>(props.comments ?? []);
const newMessage = ref("");

// Ambil komentar
const fetchComments = async () => {
    const res = await axios.get(`/subtasks/${props.subtask.id}/comments`);
    
    // Backend kamu mengembalikan { comments: [...] }
    comments.value = res.data.comments ?? res.data;

    await nextTick();
    scrollToBottom();
};



// Modifikasi fungsi sendComment untuk menangani reply ID (jika backend mendukung)
const sendComment = async () => {
    if (!newMessage.value.trim()) return;

    try {
        const res = await axios.post(`/subtasks/${props.subtask.id}/comments`, {
            type: "text",
            message: newMessage.value,
            parent_id: replyToId.value ?? null,
        });

        // Opsional: kalau backend mereturn comment baru, kamu bisa push langsung
        // comments.value.push(res.data.comment ?? res.data);

        // Reset input + reply state *HANYA* jika request sukses
        newMessage.value = "";
        replyToId.value = null;
        replyToUser.value = null;
        replyTo.value = null;

        // refresh comments (atau gunakan pushing di atas)
        await fetchComments();
    } catch (error) {
        console.error("Gagal mengirim komentar:", error);
        // Beri tahu user atau biarkan saja
        alert("Gagal mengirim komentar. Coba lagi.");
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

onMounted(() => {
    window.addEventListener("click", (e: any) => {
        // Jika klik di dalam menu dropdown → jangan tutup
        if (e.target.closest(".menu-dropdown")) return;

        // Jika klik tombol titik 3 → jangan tutup
        if (e.target.closest(".menu-btn")) return;

        // Selain itu → tutup
        closeMenu();
    });
});
onUnmounted(() => {
    window.removeEventListener("click", closeMenu);
});


// ============================
// 🌟 EDIT COMMENT STATE (LOGIKA PERBAIKAN) 🌟
// ============================
const isEditingComment = ref(false);
const editingCommentId = ref<number | null>(null);
const editingOldMessage = ref("");
// ⭐ VARIABEL BARU KHUSUS UNTUK EDIT
const editedMessage = ref("");


// Mulai edit
const startEditComment = (comment: CommentType) => {
    isEditingComment.value = true;
    editingCommentId.value = comment.id;
    editingOldMessage.value = comment.message ?? "";
    editedMessage.value = comment.message ?? ""; // Isi ke input edit
    activeMenuId.value = null; // Tutup menu setelah klik edit
};

// Batal edit
const cancelEdit = () => {
    isEditingComment.value = false;
    editingCommentId.value = null;
    editingOldMessage.value = "";
    editedMessage.value = "";
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
        console.error("Gagal menyimpan edit:", error);
        alert("Gagal menyimpan perubahan. Coba lagi.");
    }

    cancelEdit();
    await fetchComments();
};

// ============================
// 🗑️ DELETE COMMENT LOGIC
// ============================
const deleteComment = async (commentId: number) => {
    // Konfirmasi penghapusan
    if (!confirm("Are you sure you want to delete this comment?")) {
        return;
    }

    try {
        // Kirim permintaan DELETE ke backend
        await axios.delete(`/comments/${commentId}`);
        
        // Tutup menu dan refresh list komentar
        activeMenuId.value = null;
        await fetchComments();
    } catch (error) {
        console.error("Failed to delete comment:", error);
        alert("Gagal menghapus pesan. Coba lagi.");
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
    replyTo.value = comment;            // <-- penting
};

const cancelReply = () => {
    replyToId.value = null;
    replyToUser.value = null;
    replyTo.value = null;
};

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


            <div class="mt-12 relative">
                <h2
                    ref="commentHeaderRef"
                    class="flex justify-center text-lg font-semibold text-[#033A63] dark:text-gray-100 sticky z-10 bg-white dark:bg-gray-800 py-2 border-b"
                    :style="{ top: isCommentSticky ? '70px' : 'auto' }"
                >
                    💬 Komentar
                </h2>

                <div
                    ref="commentContainerRef"
                    class="space-y-2 p-2 overflow-y-auto transition-all duration-200"
                >

                    <div class="h-2"></div>
                    <div
                        v-for="(comment, index) in comments"
                        :key="comment.id"
                        class="flex flex-col gap-1"
                    >
                        <div
                            v-if="shouldShowDateLabel(index)"
                            class="text-center text-gray-800 text-xs my-3"
                        >
                            {{ formatDateLabel(comment.created_at) }}
                        </div>

                        <div
                            class="flex gap-2"
                            :class="comment.user_id === user.id ? 'justify-end' : 'justify-start'"
                        >
                            <img
                                v-if="comment.user_id !== user.id"
                                :src="comment.user.avatar
                                    ? `/storage/${comment.user.avatar}`
                                    : `https://ui-avatars.com/api/?name=${comment.user.name}`"
                                class="h-8 w-8 rounded-full shadow border"
                            />

                            <div class="flex flex-col max-w-[70%]">
                                <span
                                    v-if="comment.user_id !== user.id"
                                    class="text-[11px] font-semibold mb-1 text-left text-gray-700 dark:text-gray-300"
                                >
                                    {{ comment.user.name }}
                                </span>

                                <div
                                    class="relative group px-2 pr-7 pb-4 py-2 rounded-xl shadow-md leading-relaxed w-fit min-w-[80px]"
                                    :class="[
                                        comment.user_id === user.id ? 'bg-[#055A99] text-white self-end' : 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 self-start',
                                        comment.parent ? 'ml-6 border-l-4 border-blue-500 bg-blue-50 dark:bg-gray-800' : ''
                                    ]"
                                >
                                    <!-- Titik 3 untuk menampilkan menu replay, copy, edit, dan hapus -->
                                    <button
                                        class="menu-btn absolute top-1 text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                        :class="comment.user_id === user.id ? 'left-[-20px]' : 'right-[-20px]'"
                                        @click.stop="toggleMenu(comment.id)"
                                    >
                                        ⋮
                                    </button>

                                    <div
                                        v-if="activeMenuId === comment.id"
                                        class="menu-dropdown absolute top-5 z-20 w-32 rounded-lg border bg-white shadow-md text-sm dark:bg-gray-800"
                                        :class="comment.user_id === user.id ? 'left-[-140px]' : 'right-[-140px]'"
                                        @click.stop
                                    >
                                        <!-- Menu user yang login -->
                                        <template v-if="comment.user_id === user.id">
    
                                            <div
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-blue-500 dark:hover:bg-gray-700"
                                                @click="startEditComment(comment); activeMenuId = null"
                                            >
                                                Edit
                                            </div>
    
                                            <div 
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-red-500 dark:hover:bg-gray-700"
                                                @click="deleteComment(comment.id)"
                                            >
                                                Delete
                                            </div>

                                            <div 
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="startReply(comment); activeMenuId = null"
                                            >
                                                Reply
                                            </div>
    
                                            <div 
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="copyComment(comment.message)"
                                            >
                                                Copy
                                            </div>
                                        </template>
                                        <!-- Menu User lain -->
                                        <template v-else>
    
                                            <div 
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="startReply(comment); activeMenuId = null"
                                            >
                                                Reply
                                            </div>

                                            <div 
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
                                                @click="copyComment(comment.message)"
                                            >
                                                Copy
                                            </div>
                                        </template>
                                    </div>

                                    <!-- 🟦 REPLY BUBBLE (Jika comment ini adalah balasan)nah, iki sg ga kepanggil -->
                                    <div
                                        v-if="comment.parent"
                                        class="mb-1 p-2 rounded-lg border-l-4 border-blue-500 bg-blue-50 dark:bg-gray-800 dark:border-blue-400"
                                    >
                                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300">
                                            Replying to {{ comment.parent.user.name }}
                                        </div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                            {{ comment.parent.message }}
                                        </div>
                                    </div>

                                    <!-- ========== MESSAGE CONTENT ========== -->
                                    <div class="whitespace-pre-wrap">
                                        <span>{{ comment.message }}</span>
                                    </div>
                                    <!-- Indikator setelah diedit -->
                                    <span
                                        class="absolute bottom-1 right-2 text-[9px] flex items-center gap-1"
                                        :class="comment.user_id === user.id ? 'text-gray-200' : 'text-gray-600'"
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
                <div v-if="isEditingComment" class="w-full mt-4 border-t pt-4">
                    <div class="flex justify-between items-center mb-1 p-1 rounded-t-lg bg-gray-100 dark:bg-gray-700">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Mengedit pesan...
                        </span>
                        <button @click="cancelEdit" class="text-gray-500 hover:text-red-600 font-bold text-lg leading-none p-1">
                            &times; </button>
                    </div>

                    <div class="flex gap-2 items-center w-full">
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

                <div v-else class="relative w-full mt-4 border-t pt-4">
                    <!-- Untuk notif copy berhasil -->
                    <Transition name="fade">
                        <div 
                            v-if="showCopySuccess"
                            class="absolute -top-10 left-1/2 -translate-x-1/2 p-2 px-4 rounded-lg bg-gray-200 text-gray-500 text-sm shadow-xl z-30 transition-opacity duration-300"
                        >
                            Pesan berhasil disalin ke clipboard!
                        </div>
                    </Transition>

                    <!-- ========== REPLY PREVIEW ABOVE INPUT ========== -->
                    <div 
                        v-if="replyTo"
                        class="mb-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg flex justify-between items-start border-l-4 border-blue-500"
                    >
                        <div>
                            <p class="text-sm font-semibold">{{ replyTo.user.name }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1">
                                {{ replyTo.message }}
                            </p>
                        </div>

                        <button 
                            @click="cancelReply"
                            class="text-gray-600 dark:text-gray-300 hover:text-red-500"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="flex gap-2 items-center w-full"> 
                        <input
                            id="new-message-input"
                            v-model="newMessage"
                            class="flex-1 rounded-lg border px-3 py-2 dark:bg-black dark:text-white"
                            @keyup.enter="sendComment"
                            placeholder="Tulis komentar baru..."
                        />
                        <button
                            @click="sendComment"
                            class="rounded-lg bg-[#033A63] px-4 py-2 text-white hover:bg-[#055A99] disabled:bg-[#3B8BC9]"
                            :disabled="!newMessage.trim()"
                        >
                            Send
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>