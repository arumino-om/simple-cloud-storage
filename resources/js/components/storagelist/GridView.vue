<script setup lang="ts">
const props = defineProps(['storages', 'thumbnails'])
const emit = defineEmits<{
    (e: 'storageClicked', value: string): void
}>();
</script>

<template>
    <v-container>
        <v-row no-gutters v-if="props.storages !== null">
            <v-col
                v-for="storage in storages"
                :key="storage.name"
                cols="6"
                sm="2"
                class="pa-1"
            >
                <v-card link @click="emit('storageClicked', storage)">
                    <v-card-text class="py-0 pt-2">{{storage.name}}</v-card-text>
                    <v-card-subtitle class="py-0 pb-2">{{storage.storage_tyoe}}</v-card-subtitle>
                </v-card>
            </v-col>
        </v-row>
        <v-row no-gutters v-else>
            <v-col
                cols="12"
                sm="12"
                class="pa-1"
            >
                <v-label>ファイルが存在しません</v-label>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>

</style>
