<script setup lang="ts">
const { api } = useAuth();
const title = ref('');
const content = ref('');

const save = async () => {
  await api('/notes', {
    method: 'POST',
    body: { title: title.value, content: content.value }
  });
  await navigateTo('/');
};
</script>

<template>
  <form @submit.prevent="save">
    <input v-model="title" placeholder="Title" />
    <textarea v-model="content" placeholder="Write..."></textarea>
    <button>Create</button>
  </form>
</template>
