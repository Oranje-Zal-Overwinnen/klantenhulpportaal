<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Form from '../components/Form.vue';
import { userStore, type UserFormData } from '@/domains/users/store';

const route = useRoute();
const router = useRouter();

const userId = computed(() => Number(route.params.id));
const user = computed(() => userStore.getters.getById(userId.value).value);
const loaded = ref(false);

const loadUser = async () => {
    loaded.value = false;

    if (!user.value) {
        try {
            await userStore.actions.getAll();
        } catch {
            // De foutmelding wordt via de HTTP-service gezet.
        }
    }

    loaded.value = true;
};

watch(userId, loadUser, { immediate: true });

const handleSubmit = async (data: UserFormData) => {
    try {
        await userStore.actions.update(userId.value, data);
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
