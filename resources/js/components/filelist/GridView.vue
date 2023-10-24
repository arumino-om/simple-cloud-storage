<script setup lang="ts">
const props = defineProps(['files', 'thumbnails'])
const emit = defineEmits<{
    (e: 'fileClicked', value: string): void
}>();
console.log(props.files);
</script>

<template>
    <v-container>
        <v-row no-gutters v-if="props.files !== null">
            <v-col
                v-for="file in files"
                :key="file.name"
                cols="6"
                md="4"
                lg="2"
                class="pa-1"
            >

                <v-card link @click="emit('fileClicked', file)">
                    <v-img
                        :src="file.thumbnail_uri"
                        class="align-end"
                        gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                        height="200px"
                        cover
                    >
                        <v-card-text class="py-0 pt-2 text-white">{{file.name}}</v-card-text>
                        <v-card-subtitle class="py-0 pb-2 text-white">{{file.filetype}}</v-card-subtitle>
                    </v-img>

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
