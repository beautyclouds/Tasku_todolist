<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

let interval: any;

onMounted(() => {
    interval = setInterval(() => {
        // Ini bakal nge-refresh page.props (termasuk unread_count) tanpa reload halaman full
        router.reload({ only: ['unread_count'] });
    }, 10000); // samain 10 detik
});

onUnmounted(() => {
    clearInterval(interval);
});

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>

                    <span
                        v-if="item.badge"
                        class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                    >
                        {{ item.badge }}
                    </span>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
