<script setup lang="ts">
import ListView from "@/components/filelist/ListView.vue";
import GridView from "@/components/filelist/GridView.vue";
import {onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";

const view_type = ref("grid");
const viewer_type = ref("video");
const files = ref(null);
const thumbs = ref(null);
const viewer_dialog = ref(false);

const current_storage = ref(null);
const current_directory = ref("/");

const viewer_source = ref(null);

const route = useRoute();
const router = useRouter();

watch(
    () => [route.params.storagePath, route.params.storageId],
    async newStoragePath => {
        changeDirectory(route.params.storageId as string, "/" + route.params.storagePath as string);
    }
);

router.beforeEach((to, from) => {
    if (viewer_dialog.value === true) {
        viewer_dialog.value = false;
        return false;
    }
    return true;
})

// ディレクトリ変更
function changeDirectory(storage: string, dir: string) {
    if (viewer_dialog.value === true) {
        viewer_dialog.value = false;
        return;
    }
    current_storage.value = storage;
    current_directory.value = dir;
    router.push({
        path: `/storage/${current_storage.value}${current_directory.value}`
    })
    files.value = null;
    fetch(`/api/storage/${storage}/list?path=${dir}`)
        .then((response) => response.json())
        .then((json) => {
            files.value = json["response"];

        });
}

function fileClicked(file) {
    switch (file["filetype"]) {
        case "directory":
            changeDirectory(current_storage.value, `${current_directory.value}${file["name"]}/`);
            break;

        case "video":
            viewer_type.value = "video";
            viewer_source.value = `/api/storage/${current_storage.value}/get?path=${current_directory.value}${file["name"]}`;
            viewer_dialog.value = true;
            break;
    }
}

onMounted(() => {
    current_storage.value = route.params.storageId as string;
    current_directory.value = "/" + route.params.storagePath as string;
    changeDirectory(current_storage.value, current_directory.value);
})
</script>

<template>
    <p class="pa-5 pb-0">{{ current_directory }}</p>
    <v-label class="px-5">at {{ current_storage }}</v-label>

    <ListView v-if="view_type === 'list'" :files="files" />
    <GridView @file-clicked="fileClicked" v-else-if="view_type === 'grid'" :files="files" />

    <v-dialog
        v-model="viewer_dialog"
        fullscreen
        scrollable
        v-if="viewer_type === 'video'"
    >
        <video style="min-height: 100%;max-height: 100%;background-color: #101010;" :src="viewer_source" controls></video>
    </v-dialog>
</template>

<style scoped>

</style>
