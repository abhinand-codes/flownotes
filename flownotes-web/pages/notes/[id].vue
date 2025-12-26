<script setup lang="ts">
const { api } = useAuth();
const route = useRoute();
const note = ref<any>(null);

onMounted(async () => {
  note.value = await api(`/notes/${route.params.id}`);
});

const save = async () => {
  await api(`/notes/${route.params.id}`, {
    method: 'PUT',
    body: {
      title: note.value.title,
      content: note.value.content,
    }
  });
};

const remove = async () => {
  await api(`/notes/${route.params.id}`, { method: 'DELETE' });
  await navigateTo('/');
};
</script>

<template>
  <div v-if="note">
    <input v-model="note.title" />
    <textarea v-model="note.content"></textarea>

    <button @click="save">Save</button>
    <button @click="remove">Delete</button>
  </div>
</template>
