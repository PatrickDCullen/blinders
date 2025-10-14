<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { index } from '@/routes/filters';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { onMounted, ref } from 'vue';

const props = defineProps({ filter: String });

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Email Filters',
        href: index().url,
    },
];

const snippet = ref("");

async function applyFilter() {
    const filter = props.filter;

    let response;
    try {
        response = await gapi.client.gmail.users.messages.list({
        'userId': 'me', 'maxResults': 1, 'labelIds' :["INBOX"], 'q':'from:' + filter
        });
    } catch (err) {
        console.error(err.message);
        return;
    }
    const emailId = response.result.messages[0].id;
    console.log("This is the emails response result in apply Filter");
    console.log(emailId);

    let response2;
    try {
        response2 = await gapi.client.gmail.users.messages.get({
            'userId': 'me', 'id': emailId
        });
    } catch (err) {
        console.error(err.message);
        return;
    }
    const emailMessage = response2.result.snippet;
    console.log(emailMessage);
    snippet.value = emailMessage;
}

onMounted(() => {
    applyFilter();
})
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <p>{{  filter  }}</p>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                 <p>{{  snippet }}</p>
            </div>
        </div>
    </AppLayout>
</template>
