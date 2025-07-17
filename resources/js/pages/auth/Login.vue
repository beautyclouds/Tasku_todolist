<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Mail, Lock } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';


defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <div class="flex min-h-screen font-['Poppins']">
    <!-- LEFT PANEL -->
    <div class="w-[30%] bg-cover bg-center relative" style="background-image: url('/img/bgregis.jpg')">
        <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1 flex flex-col gap-4">
            <Link href="/register" class="login-btn">Register</Link>
            <button class="register-btn">Login</button>
        </div>
        </div>

    <!-- RIGHT PANEL -->
    <div class="w-[70%] flex flex-col justify-center items-center px-8 bg-white">
      <img src="/img/logo-taskula.png" alt="Logo" class="w-35 mb-2" />

      <h2 class="text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-[#113f67] to-[#A5BBC9] mb-6 tracking-wide">
        LOGIN
      </h2>

      <Head title="Login" />

      <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="w-full max-w-sm space-y-4">
        <!-- Email -->
        <div class="relative">
          <Mail class="absolute left-3 top-3 text-[#113f67] w-5 h-5" />
          <input
            v-model="form.email"
            type="email"
            placeholder="Email"
            required
            autofocus
            autocomplete="email"
            class="pl-10 w-full border border-[#113f67] rounded-md py-2 px-3 focus:outline-none"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Password -->
        <div class="relative">
          <Lock class="absolute left-3 top-3 text-[#113f67] w-5 h-5" />
          <input
            v-model="form.password"
            type="password"
            placeholder="Password"
            required
            autocomplete="current-password"
            class="pl-10 w-full border border-[#113f67] rounded-md py-2 px-3 focus:outline-none"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Remember + Forgot -->
        <div class="flex justify-between items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.remember" />
            Remember me
          </label>
          <TextLink
            v-if="canResetPassword"
            :href="route('password.request')"
            class="text-[#113f67] hover:underline"
          >
            Forgot password?
          </TextLink>
        </div>

        <!-- Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-[#113f67] text-white font-semibold w-full py-2 rounded-md hover:bg-blue-900 transition duration-200"
        >
          <span v-if="form.processing" class="flex items-center justify-center gap-2">
            <LoaderCircle class="w-4 h-4 animate-spin" />
            Loading...
          </span>
          <span v-else>Login</span>
        </button>
      </form>

      <!-- Link to register -->
      <div class="mt-4 text-sm text-muted-foreground text-center">
        Don't have an account?
        <TextLink :href="route('register')" class="text-[#113f67] hover:underline">Sign up</TextLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-btn {
  padding: 12px 28px;
  font-size: 16px;
  font-weight: bold;
  background-color: transparent;
  color: white;
  text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
  border: none;
  border-top-left-radius: 25px;
  border-bottom-left-radius: 25px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.login-btn:hover {
  background-color: white;
  color: #113f67;
}

.register-btn {
  padding: 12px 28px;
  font-size: 16px;
  font-weight: bold;
  background-color: #ffffff;
  color: #113f67;
  border: none;
  border-top-left-radius: 25px;
  border-bottom-left-radius: 25px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: background-color 0.3s ease;
}

</style>