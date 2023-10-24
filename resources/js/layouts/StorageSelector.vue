<script setup lang="ts">
import GridView from "@/components/storagelist/GridView.vue";
import {onMounted, ref, watch} from "vue";
import {useRoute, useRouter} from "vue-router";

let storages = ref(null);
let thumbs = ref(null);

const route = useRoute();
const router = useRouter();

function storageClicked(storage) {
    router.push({
        path: `/storage/${storage.id}/`
    })
}

onMounted(() => {
    fetch(`/api/storage/list`)
        .then((response) => response.json())
        .then((json) => {
            storages.value = json["response"];
        });
})

</script>

<template>
    <p class="pa-5 pb-0">ストレージを選択してください</p>

    <GridView @storage-clicked="storageClicked" :storages="storages" />
</template>

<style scoped>

</style>
