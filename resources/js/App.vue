<script setup lang="ts">

</script>

<template>
    <v-app>
        <v-navigation-drawer :width="200">
            <v-list-item title="Simple Cloud Storage" subtitle="Client v1.0"></v-list-item>
            <v-divider></v-divider>
            <v-list-item link title="ファイル">ファイル</v-list-item>
            <v-list-item link title="List Item 2"></v-list-item>
            <v-list-item link title="List Item 3"></v-list-item>
        </v-navigation-drawer>

        <v-main>
            <router-view></router-view>
        </v-main>
    </v-app>

</template>

<style scoped>

</style>
