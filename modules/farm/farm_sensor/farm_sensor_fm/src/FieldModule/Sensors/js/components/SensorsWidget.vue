<template>
  <div @click="openPrecipitation">
    <farm-stack>
      <farm-inline><h2>View all</h2></farm-inline>
    </farm-stack>
  </div>
</template>

<script>
export default {
  methods: {
    openPrecipitation(e) {
      e.stopPropagation();
      this.$router.push({ path: '/precipitation' });
    },
  },
};
</script>

<style scoped>
</style>