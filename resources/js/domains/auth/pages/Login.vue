<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ErrorMessage from '@/components/ErrorMessage.vue';
import FormError from '@/components/FormError.vue';
import { authStore } from '@/domains/auth/store';

const route = useRoute();
const router = useRouter();

const form = ref({
    email: '',
    password: ''
});
const submitting = ref(false);
const needsVerification = ref(false);
const resending = ref(false);
const resendMessage = ref('');

const handleSubmit = async () => {
    submitting.value = true;
    needsVerification.value = false;
    resendMessage.value = '';

    try {
        const result = await authStore.actions.login(form.value);

        if (result === 'unverified') {
            needsVerification.value = true;
            return;
        }

        if (!result) return;

        const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/') ? route.query.redirect : null;

        await router.push(redirect ?? { name: 'tickets.overview' });
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        submitting.value = false;
    }
};

const handleResend = async () => {
    resending.value = true;
    resendMessage.value = '';

    try {
        resendMessage.value = await authStore.actions.resendVerification(form.value.email);
    } catch {
        // De foutmelding wordt via de HTTP-service gezet.
    } finally {
        resending.value = false;
    }
};
</script>

<template>
    <main>
        <h1>Inloggen</h1>

        <p v-if="route.query.registered === '1'">Registratie gelukt. Controleer je e-mail om je account te bevestigen.</p>

        <p v-if="route.query.verified === '1'">Je e-mailadres is bevestigd. Je kunt nu inloggen.</p>

        <p v-if="route.query.reset === '1'">Je wachtwoord is aangepast. Je kunt nu inloggen.</p>

        <ErrorMessage />

        <div v-if="needsVerification">
            <p>Vraag hieronder een nieuwe bevestigingsmail aan.</p>
            <button type="button" :disabled="resending" @click="handleResend">Bevestigingsmail opnieuw versturen</button>
            <p v-if="resendMessage">{{ resendMessage }}</p>
        </div>

        <form @submit.prevent="handleSubmit">
            <p>
                <label for="email">E-mailadres</label>
                <input id="email" v-model="form.email" type="email" autocomplete="email" required />
            </p>
            <FormError name="email" />

            <p>
                <label for="password">Wachtwoord</label>
                <input id="password" v-model="form.password" type="password" autocomplete="current-password" required />
            </p>
            <FormError name="password" />

            <button type="submit" :disabled="submitting">Inloggen</button>
        </form>

        <p>
            <RouterLink :to="{ name: 'auth.register' }">Nog geen account? Registreren</RouterLink>
            |
            <RouterLink :to="{ name: 'auth.forgot-password' }">Wachtwoord vergeten?</RouterLink>
        </p>
    </main>
</template>
