<script setup lang="ts">
import { ref } from 'vue';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { authStore } from '@/domains/auth/store';

const email = ref('');
const success = ref('');
const submitting = ref(false);

const handleSubmit = async () => {
    submitting.value = true;
    success.value = '';

    try {
        success.value = await authStore.actions.forgotPassword(email.value);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <main>
        <h1>Wachtwoord vergeten</h1>

        <p>Vul je e-mailadres in. Je ontvangt een link om je wachtwoord opnieuw in te stellen.</p>

        <p v-if="success">{{ success }}</p>

        <ErrorMessage />

        <form @submit.prevent="handleSubmit">
            <p>
                <label for="email">E-mailadres</label>
                <input id="email" v-model="email" type="email" autocomplete="email" required />
            </p>
            <FormError name="email" />

            <button type="submit" :disabled="submitting">Resetlink versturen</button>
        </form>

        <p>
            <RouterLink :to="{ name: 'auth.login' }">Terug naar inloggen</RouterLink>
        </p>
    </main>
</template>
