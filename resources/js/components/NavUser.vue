<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3'; // Tambah Link untuk popover
import { Bell, ChevronsUpDown } from 'lucide-vue-next'; // Tambah Bell
import { computed, ref } from 'vue'; // Import computed dan ref
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user as User;
const { isMobile, state } = useSidebar();

// --- START: LOGIC NOTIFIKASI DITAMBAHKAN ---

// 1. Variabel Reaktif
const unreadCount = computed(() => (page.props.unreadCount as number) || 0);
const recentNotifications = computed(() => (page.props.recentNotifications as any[]) || []);
const isPopoverOpen = ref(false); // State untuk Popover

// 2. Fungsi Aksi
const togglePopover = () => {
    isPopoverOpen.value = !isPopoverOpen.value;
    // TO DO: Optional: Panggil API untuk menandai notif yang terbuka sebagai "dibaca" di sini.
};

const getNotifIcon = (action: string): string => {
    if (action.includes('Comment')) return '💬';
    if (action.includes('Closed')) return '✅';
    if (action.includes('Updated')) return '✏️';
    return '🔔';
};

const generateNotifText = (data: any): string => {
    // Sesuaikan ini dengan struktur data yang di-share Laravel
    if (data.action && data.actor_name && data.card_title) {
        return `**${data.actor_name}** melakukan **${data.action.toLowerCase()}** pada Card: ${data.card_title}.`;
    }
    return 'Notifikasi tak dikenal.';
};

const getNotifLink = (notif: any): string => {
    // Sesuaikan routing di sini
    if (notif.data.subtask_id) {
        return route('subtask.detail', { subtask: notif.data.subtask_id });
    }
    return route('notifications.index');
};

const formatRelativeTime = (time: string): string => {
    // TODO: Gunakan library seperti dayjs/moment.js atau fungsi native untuk format yang lebih baik
    return new Date(time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const markAsRead = (notifId: string) => {
    // Panggil Inertia (atau Axios, jika Inertia tidak di-setup) untuk POST ke route yang baru
    router.post(
        route('notifications.markAsRead', { notification: notifId }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log(`Notification ${notifId} marked as read via Inertia.`);
                // Karena kita menggunakan Inertia POST, props Inertia akan diperbarui (unreadCount)
            },
            onError: (errors) => {
                console.error('Failed to mark as read:', errors);
            },
        },
    );
};
// --- END: LOGIC NOTIFIKASI DITAMBAHKAN ---
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <UserInfo :user="user" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <div class="relative flex items-center gap-4">
                    <button @click="togglePopover" class="relative rounded-full p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <Bell class="h-6 w-6 dark:text-gray-200" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-0 right-0 inline-flex h-4 w-4 translate-x-1/2 -translate-y-1/2 transform items-center justify-center rounded-full bg-red-600 text-xs leading-none font-bold text-white"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </button>

                    <Transition name="fade">
                        <div
                            v-if="isPopoverOpen"
                            class="absolute bottom-full left-0 z-50 mb-2 w-80 rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
                                <h3 class="text-lg font-bold dark:text-white">Notifikasi</h3>
                                <Link :href="route('notifications.index')" class="text-sm text-blue-600 hover:underline">Lihat Semua</Link>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <div v-if="recentNotifications.length > 0">
                                    <a
                                        v-for="notif in recentNotifications"
                                        :key="notif.id"
                                        :href="getNotifLink(notif)"
                                        @click="
                                            isPopoverOpen = false;
                                            markAsRead(notif.id);
                                        "
                                        :class="[
                                            'flex gap-2 border-b p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700',
                                            notif.read_at === null ? 'bg-blue-50/50 dark:bg-gray-700/50' : '',
                                        ]"
                                    >
                                        <span class="flex-shrink-0 text-lg">{{ getNotifIcon(notif.data.action) }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-sm dark:text-white" v-html="generateNotifText(notif.data)"></p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ formatRelativeTime(notif.created_at) }}</p>
                                        </div>
                                    </a>
                                </div>
                                <p v-else class="p-4 text-center text-sm text-gray-500 italic">Belum ada notifikasi.</p>
                            </div>
                        </div>
                    </Transition>

                    <NavUserContent />
                </div>

                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
