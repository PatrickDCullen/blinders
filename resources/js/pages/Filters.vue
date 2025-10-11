<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { filters } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import Heading from '@/components/Heading.vue';
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty';
import Button from '@/components/ui/button/Button.vue';
import { Mail } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Email Filters',
        href: filters().url,
    },
];

const isEmpty = true;
</script>

<template>
    <Head title="Email Filters" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                :class="isEmpty ? 'border border-dashed' : ''"
                class="content-center relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <Empty v-if="isEmpty">
                    <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <Mail />
                    </EmptyMedia>
                    <EmptyTitle>No Gmail Account Found</EmptyTitle>
                    <EmptyDescription>Please log in to the Gmail account that you would like to add filters to.</EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button>Authorize Gmail</Button>
                    </EmptyContent>
                </Empty>
            </div>
        </div>
    </AppLayout>
</template>
