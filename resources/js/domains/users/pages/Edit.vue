<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { userStore, type UserFormData } from '@/domains/users/store';

const route = useRoute();
const router = useRouter();

const userId = Number(route.params.id);
const user = userStore.getters.getById(userId);
const loaded = ref(false);

onMounted(async () => {
    if (!user.value) {
        try {
            await userStore.actions.getAll();
        } catch {
            // De foutmelding wordt via de HTTP-service gezet.
        }
    }

    loaded.value = true;
});

const handleSubmit = async (data: UserFormData) => {
    try {
        await userStore.actions.update(userId, data);
        await router.push({ name: 'users.overview' });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    }
};
</script>

<template>
    <main>
        <h1>Gebruiker bewerken</h1>

        <Form v-if="user" :user="user" @submit="handleSubmit" />

        <p v-else-if="loaded">Gebruiker niet gevonden.</p>
    </main>
</template>
