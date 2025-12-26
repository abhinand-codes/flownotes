<script setup lang="ts">
const { api } = useAuth();
const notes = ref<any[]>([]);

onMounted(async () => {
  notes.value = await api('/notes');
});
</script>

<template>
  <div>
    <a href="/notes/new">New</a>
    <ul>
      <li v-for="n in notes" :key="n.id">
        <a :href="`/notes/${n.id}`">{{ n.title }}</a>
      </li>
    </ul>
  </div>
</template>
