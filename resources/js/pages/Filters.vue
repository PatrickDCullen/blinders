<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { index, store } from '@/routes/filters';
import { type BreadcrumbItem } from '@/types';
import { Head, Form } from '@inertiajs/vue3';
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
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { FilterX, Mail } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { ButtonGroup } from '@/components/ui/button-group';

defineProps({ filters: Object });

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Email Filters',
        href: index().url,
    },
];

const isAuthed = ref(false);
const filter = ref('');

const CLIENT_ID = import.meta.env.VITE_GOOGLE_CLIENT_ID;
const API_KEY = import.meta.env.VITE_GOOGLE_API_KEY;

// Discovery doc URL for APIs used by the quickstart
const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
const SCOPES = 'https://www.googleapis.com/auth/gmail.readonly';

let tokenClient;

function gapiLoaded() {
    gapi.load('client', initializeGapiClient);
}

/**
 * Callback after the API client is loaded. Loads the
 * discovery doc to initialize the API.
 */
async function initializeGapiClient() {
    await gapi.client.init({
        apiKey: API_KEY,
        discoveryDocs: [DISCOVERY_DOC],
    });
}

/**
 * Callback after Google Identity Services are loaded.
 */
function gisLoaded() {
    tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: '', // defined later
    });
}

/**
 *  Sign in the user upon button click.
 */
function handleAuthClick() {
    tokenClient.callback = async (resp) => {
        if (resp.error !== undefined) {
            throw (resp);
        }
        isAuthed.value = true;
    };

    if (gapi.client.getToken() === null) {
        // Prompt the user to select a Google Account and ask for consent to share their data
        // when establishing a new session.
        tokenClient.requestAccessToken({prompt: 'consent'});
    } else {
        // Skip display of account chooser and consent dialog for an existing session.
        tokenClient.requestAccessToken({prompt: ''});
    }
}

/**
 *  Sign out the user upon button click.
 */
function handleSignoutClick() {
    const token = gapi.client.getToken();
    if (token !== null) {
        google.accounts.oauth2.revoke(token.access_token);
        gapi.client.setToken('');
        isAuthed.value = false;
    }
}

async function applyFilter() {
    const filter = document.getElementById('filter_input').value;
    // await showFilters(filter);

    let response;
    try {
        response = await gapi.client.gmail.users.messages.list({
        'userId': 'me', 'maxResults': 1, 'labelIds' :["INBOX"], 'q':'from:' + filter
        });
    } catch (err) {
        document.getElementById('filters_content').innerText = err.message;
        return;
    }
    const emailId = response.result.messages[0].id;
    console.log("This is the emails response result in apply Filter");
    console.log(emailId);
    // if (!labels || labels.length == 0) {
    //   document.getElementById('filters_content').innerText = 'No labels found.';
    //   return;
    // }

    let response2;
    try {
        response2 = await gapi.client.gmail.users.messages.get({
            'userId': 'me', 'id': emailId
        });
    } catch (err) {
        document.getElementById('filters_content').innerText = err.message;
        return;
    }
    console.log("response2 result");
    console.log(response2.result);
    const emailMessage = response2.result.snippet;
    // Flatten to string to display
    // const output = labels.reduce(
    //     (str, label) => `${str}${label.name}\n`,
    //     'Labels:\n');
    const output = emailMessage;
    document.getElementById('filters_content').innerText = output;
}

onMounted(() => {
    gapiLoaded();
    gisLoaded();
})
</script>

<template>
    <Head title="Email Filters" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- <p>Filters: {{ filters }}</p>
            <p>{{ !!filters.length }}</p>
            <p>{{ isAuthed && !filters.length }}</p> -->
            <div
                :class="!isAuthed ? 'border border-dashed' : ''"
                class="content-center relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <Empty v-if="!isAuthed">
                    <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <Mail />
                    </EmptyMedia>
                    <EmptyTitle>No Gmail Account Found</EmptyTitle>
                    <EmptyDescription>Please log in to the Gmail account that you would like to add filters to.</EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Button @click="handleAuthClick">Authorize Gmail</Button>
                    </EmptyContent>
                </Empty>

                <Empty v-else-if="isAuthed && !filters.length">
                    <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <FilterX />
                    </EmptyMedia>
                    <EmptyTitle>No Filters Found</EmptyTitle>
                    <EmptyDescription>Add a filter to get started.</EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent>
                        <Form :action="store()" method="post" resetOnSuccess>
                            <ButtonGroup>
                                <Input placeholder="Search..." type="text" name="filter" />
                                <Button type="submit">Add Filter</Button>
                            </ButtonGroup>
                        </Form>
                        <Button variant="destructive" @click="handleSignoutClick">Revoke Gmail Access</Button>
                    </EmptyContent>
                </Empty>
                <Table v-else>
                    <TableCaption>A list of your recent Gmail filters.</TableCaption>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Filter</TableHead>
                        </TableRow>
                        <TableRow v-for="filter in filters" :key="filter.id">
                            <TableCell>
                                {{ filter.filter }}
                            </TableCell>
                        </TableRow>
                    </TableHeader>
                </Table>
            </div>

        </div>
    </AppLayout>
</template>
