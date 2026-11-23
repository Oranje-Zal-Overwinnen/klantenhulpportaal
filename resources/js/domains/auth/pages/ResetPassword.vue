<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { authStore } from '@/domains/auth/store';

const route = useRoute();
const router = useRouter();

const token = typeof route.query.token === 'string' ? route.query.token : '';
const email = typeof route.query.email === 'string' ? route.query.email : '';

const form = ref({
    email,
    password: '',
    password_confirmation: ''
});
const submitting = ref(false);

const handleSubmit = async () => {
    submitting.value = true;

    try {
        await authStore.actions.resetPassword({
            token,
            ...form.value
        });

        await router.push({ name: 'auth.login', query: { reset: '1' } });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <main>
        <h1>Wachtwoord opnieuw instellen</h1>

        <p v-if="!token || !email">De resetlink is ongeldig of verlopen.</p>

        <template v-else>
            <ErrorMessage />

            <form @submit.prevent="handleSubmit">
                <p>
                    <label for="email">E-mailadres</label>
                    <input id="email" v-model="form.email" type="email" autocomplete="email" readonly />
                </p>
                <FormError name="email" />

                <p>
                    <label for="password">Nieuw wachtwoord</label>
                    <input id="password" v-model="form.password" type="password" autocomplete="new-password" minlength="8" required />
                </p>
                <FormError name="password" />

                <p>
                    <label for="password_confirmation">Wachtwoord bevestigen</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" minlength="8" required />
                </p>

                <button type="submit" :disabled="submitting">Wachtwoord opslaan</button>
            </form>
        </template>

        <p>
            <RouterLink :to="{ name: 'auth.login' }">Terug naar inloggen</RouterLink>
        </p>
    </main>
</template>
