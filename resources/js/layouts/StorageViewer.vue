<script setup lang="ts">
import ListView from "@/components/filelist/ListView.vue";
import GridView from "@/components/filelist/GridView.vue";
import {onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";

let view_type = ref("grid");
let files = ref(null);
let thumbs = ref(null);

let current_storage = ref(null);
let current_directory = ref("/");

const route = useRoute();
const router = useRouter();

watch(
    () => [route.params.storagePath, route.params.storageId],
    async newStoragePath => {
        current_storage.value = route.params.storageId as string;
        current_directory.value = "/" + route.params.storagePath as string;
        changeDirectory(current_storage.value, current_directory.value);
    }
);

// ディレクトリ変更
function changeDirectory(storage: string, dir: string) {
    files.value = null;
    fetch(`/api/storage/${storage}/list?path=${dir}`)
        .then((response) => response.json())
        .then((json) => {
            files.value = json["response"];

        });
}

function fileClicked(file) {
    if (file["filetype"] === "directory") {
        current_directory.value = `${current_directory.value}${file["name"]}/`;
        router.push({
            path: `/storage/${current_storage.value}${current_directory.value}`
        })
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
</template>

<style scoped>

</style>
