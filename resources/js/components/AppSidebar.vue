<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Clock, LayoutGrid, Users } from 'lucide-vue-next';
import { computed } from 'vue';

import AppLogo from './AppLogo.vue';

const page = usePage();
const totalUnread = computed(() => page.props.unread_count as number);

const mainNavItems = computed((): NavItem[] => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Board',
        href: '/board',
        icon: BookOpen,
        // Kita tambahin properti badge di sini
        badge: totalUnread.value > 0 ? totalUnread.value : null,
    },
    {
        title: 'Member',
        href: '/member',
        icon: Users,
    },
    {
        title: 'History',
        href: '/history',
        icon: Clock,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
