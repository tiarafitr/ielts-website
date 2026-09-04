<template>
  <div class="auth">
    <div class="auth__art">
      <span class="eyebrow">IELTS Speaking Course</span>
      <h2 style="font-size:clamp(1.5rem,2.8vw,2rem)">
        {{ isRegister ? 'Start your speaking journey' : 'Welcome back!' }}
      </h2>
      <p class="lead">
        {{ isRegister
          ? 'Create a free account and work through Parts 1, 2 and 3 with instant band feedback.'
          : 'Continue your IELTS speaking journey where you left off.' }}
      </p>
      <LearnerArt name="auth" width="100%" />
      <div class="row row--wrap" style="gap:8px">
        <span class="chip">Part 1 · Interview</span>
        <span class="chip">Part 2 · Long turn</span>
        <span class="chip">Part 3 · Discussion</span>
      </div>
    </div>

    <div class="auth__form">
      <h1 style="font-size:1.7rem;margin-bottom:6px">{{ isRegister ? 'Create your account' : 'Sign in' }}</h1>
      <p class="muted" style="margin-bottom:22px">
        {{ isRegister ? 'It takes less than a minute.' : 'Use the email you registered with.' }}
      </p>

      <div v-if="error" class="alert alert--error" style="margin-bottom:16px">{{ error }}</div>

      <div class="field" v-if="isRegister">
        <label for="name">Full name</label>
        <input id="name" v-model="form.name" type="text" autocomplete="name" placeholder="Your name" />
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" autocomplete="email" placeholder="you@example.com" />
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input
          id="password" v-model="form.password" type="password"
          :autocomplete="isRegister ? 'new-password' : 'current-password'"
          placeholder="••••••••" @keyup.enter="submit"
        />
        <span class="hint" v-if="isRegister">At least 8 characters.</span>
      </div>

      <div class="field" v-if="isRegister">
        <label for="password2">Confirm password</label>
        <input id="password2" v-model="form.password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••" @keyup.enter="submit" />
      </div>

      <button class="btn btn--primary btn--block" type="button" :disabled="busy" @click="submit">
        <span v-if="busy" class="spinner"></span>
        {{ busy ? 'Please wait…' : (isRegister ? 'Create account' : 'Continue learning') }}
      </button>

      <p class="muted" style="margin-top:18px;text-align:center">
        {{ isRegister ? 'Already learning with us?' : 'New here?' }}
        <button class="btn btn--quiet btn--sm" type="button" @click="toggle">
          {{ isRegister ? 'Sign in' : 'Create an account' }}
        </button>
      </p>
    </div>
  </div>
</template>

<script>
import { register as doRegister, login as doLogin } from '../store';
import { apiErrorMessage } from '../api';
import LearnerArt from '../components/LearnerArt.vue';

export default {
  name: 'AuthView',
  components: { LearnerArt },
  data() {
    return {
      mode: 'login',
      busy: false,
      error: '',
      form: { name: '', email: '', password: '', password_confirmation: '' },
    };
  },
  computed: {
    isRegister() { return this.mode === 'register'; },
  },
  methods: {
    toggle() {
      this.mode = this.isRegister ? 'login' : 'register';
      this.error = '';
    },
    async submit() {
      if (this.busy) return;
      this.error = '';
      this.busy = true;
      try {
        if (this.isRegister) {
          await doRegister({ ...this.form });
        } else {
          await doLogin({ email: this.form.email, password: this.form.password });
        }
      } catch (e) {
        this.error = apiErrorMessage(e);
      } finally {
        this.busy = false;
      }
    },
  },
};
</script>
